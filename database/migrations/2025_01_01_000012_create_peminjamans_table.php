<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel peminjamans untuk mencatat transaksi peminjaman buku.
     */
    public function up(): void
    {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pinjam', 20)->unique()->comment('Kode unik transaksi peminjaman');
            $table->foreignId('buku_id')
                  ->constrained('bukus')
                  ->cascadeOnDelete()
                  ->comment('FK ke tabel bukus');
            $table->string('nim_peminjam', 20)->comment('NIM mahasiswa peminjam');
            $table->string('nama_peminjam', 150)->comment('Nama lengkap peminjam');
            $table->date('tanggal_pinjam')->comment('Tanggal mulai pinjam');
            $table->date('tanggal_jatuh_tempo')->comment('Batas tanggal pengembalian');
            $table->date('tanggal_kembali')->nullable()->comment('Tanggal aktual pengembalian');
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam')->comment('Status peminjaman');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
