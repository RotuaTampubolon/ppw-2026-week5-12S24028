<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kategori>
 */
class KategoriFactory extends Factory
{
    protected $model = Kategori::class;

    private static int $counter = 0;

    private static array $kategoris = [
        ['KAT-FIK', 'Fiksi'],
        ['KAT-NON', 'Non-Fiksi'],
        ['KAT-SAI', 'Sains & Teknologi'],
        ['KAT-SEJ', 'Sejarah'],
        ['KAT-SAS', 'Sastra & Budaya'],
        ['KAT-AGM', 'Agama & Spiritualitas'],
        ['KAT-EKO', 'Ekonomi & Bisnis'],
        ['KAT-HUK', 'Hukum & Politik'],
        ['KAT-KES', 'Kesehatan & Gizi'],
        ['KAT-PEN', 'Pendidikan'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $index = self::$counter % count(self::$kategoris);
        self::$counter++;
        return [
            'kode_kategori' => self::$kategoris[$index][0],
            'nama_kategori' => self::$kategoris[$index][1],
        ];
    }
}
