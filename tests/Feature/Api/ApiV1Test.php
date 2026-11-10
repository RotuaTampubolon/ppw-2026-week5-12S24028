<?php

namespace Tests\Feature\Api;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_and_problem_responses(): void
    {
        $category = Kategori::factory()->create();
        $book = Buku::factory()->for($category, 'kategori')->create(['stok' => 2]);

        $this->getJson('/api/v1/bukus')->assertOk()->assertJsonPath('data.0.id', $book->id);
        $this->getJson('/api/v1/bukus/99999')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json');
    }

    public function test_book_mutations_require_auth_and_put_patch_differ(): void
    {
        $category = Kategori::factory()->create();
        $book = Buku::factory()->for($category, 'kategori')->create(['stok' => 2, 'sinopsis' => 'lama']);
        $this->postJson('/api/v1/bukus', [])->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());
        $this->patchJson("/api/v1/bukus/{$book->id}", ['stok' => 5])
            ->assertOk();
        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 5, 'sinopsis' => 'lama']);

        $this->putJson("/api/v1/bukus/{$book->id}", [
            'isbn' => $book->isbn,
            'judul' => $book->judul,
            'penulis' => $book->penulis,
            'penerbit' => $book->penerbit,
            'tahun_terbit' => $book->tahun_terbit,
            'kategori_id' => $category->id,
            'stok' => 4,
        ])->assertOk();
        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 4, 'sinopsis' => null]);
    }

    public function test_borrow_return_and_dynamic_links(): void
    {
        $category = Kategori::factory()->create();
        $book = Buku::factory()->for($category, 'kategori')->create(['stok' => 1]);
        Sanctum::actingAs(User::factory()->create());

        $loan = $this->postJson('/api/v1/peminjamans', [
            'buku_id' => $book->id,
            'nim_peminjam' => '12s12345',
            'nama_peminjam' => 'Mahasiswa',
        ])->assertCreated()->json('data');

        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 0]);
        $this->assertArrayHasKey('kembalikan_buku', $loan['_links']);
        $this->postJson('/api/v1/peminjamans', [
            'buku_id' => $book->id,
            'nim_peminjam' => '12s12345',
            'nama_peminjam' => 'Mahasiswa',
        ])->assertStatus(422);

        $this->patchJson("/api/v1/peminjamans/{$loan['id']}/kembalikan")->assertOk();
        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 1]);
        $this->patchJson("/api/v1/peminjamans/{$loan['id']}/kembalikan")->assertConflict();
    }

    public function test_external_consumer_handles_success_and_timeout(): void
    {
        $book = Buku::factory()->create();
        Http::fake([
            'openlibrary.org/*' => Http::response([
                "ISBN:{$book->isbn}" => ['title' => 'External', 'number_of_pages' => 10],
            ]),
        ]);
        $this->getJson("/api/v1/bukus/{$book->id}/referensi-eksternal")
            ->assertOk()->assertJsonPath('data.judul', 'External');

        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));
        $this->getJson("/api/v1/bukus/{$book->id}/referensi-eksternal")->assertStatus(504);
    }
}
