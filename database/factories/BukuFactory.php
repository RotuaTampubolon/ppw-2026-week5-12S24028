<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Buku>
 */
class BukuFactory extends Factory
{
    protected $model = Buku::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isbn = $this->generateIsbn();

        return [
            'isbn'         => $isbn,
            'judul'        => $this->faker->sentence(rand(3, 7), true),
            'penulis'      => $this->faker->name(),
            'penerbit'     => $this->faker->company() . ' Press',
            'tahun_terbit' => $this->faker->numberBetween(1990, 2025),
            'kategori_id'  => Kategori::inRandomOrder()->first()?->id ?? Kategori::factory(),
            'stok'         => $this->faker->numberBetween(0, 100),
            'sinopsis'     => $this->faker->paragraph(rand(2, 4)),
        ];
    }

    /**
     * Generate a realistic ISBN-13 format.
     */
    private function generateIsbn(): string
    {
        $prefix = '978';
        $group = rand(0, 9);
        $publisher = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $title = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $base = $prefix . $group . $publisher . $title;

        // Calculate check digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$base[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $check = (10 - ($sum % 10)) % 10;

        return $base . $check;
    }
}
