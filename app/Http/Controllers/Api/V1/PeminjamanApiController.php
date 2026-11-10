<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'status' => 'sometimes|in:dipinjam,dikembalikan',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $loans = Peminjaman::with('buku')
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('nim'), fn ($q, $nim) => $q->where('nim_peminjam', strtoupper($nim)))
            ->latest()->paginate($request->integer('per_page', 15));

        return PeminjamanResource::collection($loans);
    }

    public function show(int $id)
    {
        $loan = Peminjaman::with('buku')->find($id);
        if (! $loan) {
            return $this->problemResponse('Not Found', 'Peminjaman yang diminta tidak ditemukan.', 404, 'peminjaman-not-found');
        }
        return new PeminjamanResource($loan);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'buku_id' => 'required|integer|exists:bukus,id',
            'nim_peminjam' => 'required|string|max:20',
            'nama_peminjam' => 'required|string|max:150',
            'durasi_hari' => 'sometimes|integer|min:1|max:30',
        ]);

        $loan = DB::transaction(function () use ($data) {
            $book = Buku::whereKey($data['buku_id'])->lockForUpdate()->firstOrFail();
            if ($book->stok < 1) {
                abort(422, 'Stok buku sedang habis');
            }
            $nim = strtoupper($data['nim_peminjam']);
            if (Peminjaman::where('buku_id', $book->id)->where('nim_peminjam', $nim)->where('status', 'dipinjam')->exists()) {
                abort(409, 'NIM tersebut masih memiliki peminjaman aktif untuk buku ini.');
            }
            $today = now()->startOfDay();
            $book->decrement('stok');
            return Peminjaman::create([
                'kode_pinjam' => 'PINJ-' . now()->format('Ymd') . '-' . strtoupper(str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT)),
                'buku_id' => $book->id,
                'nim_peminjam' => $nim,
                'nama_peminjam' => $data['nama_peminjam'],
                'tanggal_pinjam' => $today,
                'tanggal_jatuh_tempo' => $today->copy()->addDays($data['durasi_hari'] ?? 7),
                'status' => 'dipinjam',
            ]);
        }, 3);

        $loan->load('buku');
        return $this->createdResponse(new PeminjamanResource($loan), url("/api/v1/peminjamans/{$loan->id}"), 'Peminjaman berhasil dicatat.');
    }

    public function kembalikan(int $id): JsonResponse
    {
        $loan = DB::transaction(function () use ($id) {
            $loan = Peminjaman::whereKey($id)->lockForUpdate()->first();
            if (! $loan) {
                abort(404, 'Peminjaman yang diminta tidak ditemukan.');
            }
            if ($loan->status === 'dikembalikan') {
                abort(409, 'Peminjaman ini sudah dikembalikan.');
            }
            $book = Buku::whereKey($loan->buku_id)->lockForUpdate()->firstOrFail();
            $loan->update(['status' => 'dikembalikan', 'tanggal_kembali' => now()->toDateString()]);
            $book->increment('stok');
            return $loan;
        }, 3);

        return $this->successResponse(new PeminjamanResource($loan->load('buku')), 'Buku berhasil dikembalikan.');
    }
}
