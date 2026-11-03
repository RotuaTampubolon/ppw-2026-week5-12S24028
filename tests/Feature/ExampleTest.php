<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Uji rute utama diarahkan ke katalog buku dan katalog buku dapat diakses.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/bukus');

        $katalogResponse = $this->get('/bukus');
        $katalogResponse->assertStatus(200);
    }
}
