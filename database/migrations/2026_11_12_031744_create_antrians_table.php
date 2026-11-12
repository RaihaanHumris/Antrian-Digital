<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nomor_urut');
            $table->string('kode_antrian', 20);
            $table->string('nama_pasien')->nullable();
            $table->string('jenis_poli', 50)->default('Poli Umum');
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'dilewati'])->default('menunggu');
            $table->date('tanggal_antrian')->index();

            $table->timestamp('waktu_dipanggil')->nullable();
            $table->timestamps();

            // Mencegah ada nomor urut ganda di tanggal yang sama
            $table->unique(['tanggal_antrian', 'nomor_urut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};