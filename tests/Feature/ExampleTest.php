<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifikasi halaman utama (daftar buku) dapat diakses publik.
     */
    public function test_halaman_daftar_buku_dapat_diakses(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/bukus');

        $response->assertStatus(200);
        $response->assertSee('SIPUS-Del');
    }
}
