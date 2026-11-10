<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class BukuApiController extends Controller
{
    use ApiResponse;

    private function rules(bool $partial = false, ?int $ignoreId = null): array
    {
        $required = $partial ? 'sometimes|required' : 'required';
        $unique = 'unique:bukus,isbn' . ($ignoreId ? ',' . $ignoreId : '');

        return [
            'isbn' => "{$required}|string|max:20|{$unique}",
            'judul' => "{$required}|string|min:5|max:255",
            'penulis' => "{$required}|string|max:150",
            'penerbit' => "{$required}|string|max:150",
            'tahun_terbit' => "{$required}|integer|min:1900|max:" . date('Y'),
            'kategori_id' => "{$required}|integer|exists:kategoris,id",
            'stok' => "{$required}|integer|min:0",
            'sinopsis' => 'sometimes|nullable|string',
        ];
    }

    public function index(Request $request)
    {
        $request->validate([
            'kategori_id' => 'sometimes|integer|exists:kategoris,id',
            'tersedia' => 'sometimes|in:1',
            'sort' => 'sometimes|in:judul,stok,tahun_terbit',
            'dir' => 'sometimes|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Buku::with('kategori')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($inner) => $inner
                    ->where('judul', 'like', "%{$search}%")
                    ->orWhere('penulis', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%"));
            })
            ->when($request->filled('kategori_id'), fn ($q) => $q->where('kategori_id', $request->integer('kategori_id')))
            ->when($request->input('tersedia') === '1', fn ($q) => $q->where('stok', '>', 0));

        $sort = in_array($request->input('sort'), ['judul', 'stok', 'tahun_terbit'], true)
            ? $request->input('sort') : 'judul';
        $dir = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        $books = $query->orderBy($sort, $dir)->paginate($request->integer('per_page', 15));

        return BukuResource::collection($books);
    }

    public function show(int $id)
    {
        $book = Buku::with('kategori')->find($id);
        if (! $book) {
            return $this->problemResponse('Not Found', 'Buku yang diminta tidak ditemukan.', 404, 'buku-not-found');
        }

        return new BukuResource($book);
    }

    public function store(Request $request): JsonResponse
    {
        $book = Buku::create($request->validate($this->rules()));
        $book->load('kategori');

        return $this->createdResponse(
            new BukuResource($book),
            url("/api/v1/bukus/{$book->id}"),
            'Buku berhasil ditambahkan.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $book = Buku::find($id);
        if (! $book) {
            return $this->problemResponse('Not Found', 'Buku yang diminta tidak ditemukan.', 404, 'buku-not-found');
        }

        $isPut = $request->isMethod('put');
        if (! $isPut && $request->isMethod('patch') && $request->json()->count() === 0) {
            return $this->problemResponse('Bad Request', 'PATCH membutuhkan setidaknya satu atribut.', 400, 'empty-patch');
        }

        $data = $request->validate($this->rules(! $isPut, $book->id));
        if ($isPut) {
            $data['sinopsis'] = $data['sinopsis'] ?? null;
        }
        $book->update($data);
        $book->load('kategori');

        return $this->successResponse(new BukuResource($book), 'Buku berhasil diperbarui.');
    }

    public function destroy(int $id): JsonResponse
    {
        $book = Buku::find($id);
        if (! $book) {
            return $this->problemResponse('Not Found', 'Buku yang diminta tidak ditemukan.', 404, 'buku-not-found');
        }
        if ($book->peminjamans()->where('status', 'dipinjam')->exists()) {
            return $this->problemResponse('Conflict', 'Buku masih memiliki peminjaman aktif.', 409, 'active-loan-conflict');
        }

        $book->delete();
        return $this->noContentResponse();
    }

    public function referensiEksternal(int $id): JsonResponse
    {
        $book = Buku::find($id);
        if (! $book) {
            return $this->problemResponse('Not Found', 'Buku yang diminta tidak ditemukan.', 404, 'buku-not-found');
        }

        try {
            $response = Http::timeout(8)->get('https://openlibrary.org/api/books', [
                'bibkeys' => "ISBN:{$book->isbn}",
                'format' => 'json',
                'jscmd' => 'data',
            ]);
        } catch (ConnectionException) {
            return $this->problemResponse('Gateway Timeout', 'Layanan OpenLibrary tidak merespons tepat waktu.', 504, 'external-timeout');
        }
        if (! $response->successful()) {
            return $this->problemResponse('Bad Gateway', 'Layanan OpenLibrary gagal memproses permintaan.', 502, 'external-service-failed');
        }

        $external = $response->json("ISBN:{$book->isbn}");
        if (! is_array($external) || $external === []) {
            return $this->problemResponse('Not Found', 'Data buku tidak ditemukan di OpenLibrary.', 404, 'external-book-not-found');
        }

        return $this->successResponse([
            'judul' => $external['title'] ?? null,
            'jumlah_halaman' => $external['number_of_pages'] ?? null,
            'penerbit' => $external['publishers'][0]['name'] ?? null,
            'pratinjau' => $external['url'] ?? null,
            'cover' => $external['cover']['large'] ?? $external['cover']['medium'] ?? null,
        ], 'Referensi eksternal berhasil diambil.');
    }
}
