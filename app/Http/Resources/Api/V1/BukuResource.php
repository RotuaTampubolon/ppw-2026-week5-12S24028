<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $links = [
            'self' => ['href' => url("/api/v1/bukus/{$this->id}"), 'method' => 'GET'],
            'referensi_eksternal' => [
                'href' => url("/api/v1/bukus/{$this->id}/referensi-eksternal"),
                'method' => 'GET',
            ],
        ];

        if ($this->stok > 0) {
            $links['pinjam_buku'] = ['href' => url('/api/v1/peminjamans'), 'method' => 'POST'];
        }

        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'judul' => $this->judul,
            'penulis' => $this->penulis,
            'penerbit' => $this->penerbit,
            'tahun_terbit' => $this->tahun_terbit,
            'kategori' => $this->whenLoaded('kategori'),
            'kategori_id' => $this->kategori_id,
            'stok' => $this->stok,
            'sinopsis' => $this->sinopsis,
            '_links' => $links,
        ];
    }
}
