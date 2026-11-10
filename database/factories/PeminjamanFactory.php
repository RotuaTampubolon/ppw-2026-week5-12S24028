<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Peminjaman>
 */
class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    public function definition(): array
    {
        $tanggalPinjam = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'kode_pinjam' => 'PINJ-' . $tanggalPinjam->format('Ymd') . '-' . $this->faker->unique()->numerify('#####'),
            'buku_id' => Buku::factory(),
            'nim_peminjam' => strtoupper($this->faker->numerify('12S#####')),
            'nama_peminjam' => $this->faker->name(),
            'tanggal_pinjam' => $tanggalPinjam,
            'tanggal_jatuh_tempo' => (clone $tanggalPinjam)->modify('+7 days'),
            'tanggal_kembali' => null,
            'status' => 'dipinjam',
        ];
    }

    public function dikembalikan(): static
    {
        return $this->state(fn () => [
            'status' => 'dikembalikan',
            'tanggal_kembali' => now()->toDateString(),
        ]);
    }
}
