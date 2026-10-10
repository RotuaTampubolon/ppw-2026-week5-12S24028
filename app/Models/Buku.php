<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'bukus';

    /**
     * Mass assignment protection.
     */
    protected $fillable = [
        'isbn',
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'kategori_id',
        'stok',
        'sinopsis',
    ];

    /**
     * Type casting.
     */
    protected $casts = [
        'tahun_terbit' => 'integer',
        'stok'         => 'integer',
        'kategori_id'  => 'integer',
    ];

    /**
     * Many-to-One: Setiap buku milik satu kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
}
