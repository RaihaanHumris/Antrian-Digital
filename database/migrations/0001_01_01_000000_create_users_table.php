<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk tabel users, sessions, password reset, dan antrians.
     * Semua tabel esensial disiapkan agar aplikasi berjalan lancar tanpa error database.
     */
    public function up(): void
    {
        // 1. Tabel Users (Hanya Role Admin)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('peran')->default('admin');
            $table->string('nama_loket')->default('Loket Utama');
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Tabel Password Reset Tokens (Standar Laravel)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Tabel Sessions (Dibutuhkan karena SESSION_DRIVER=database di .env)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 4. Tabel Antrian Pasien
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nomor_urut');
            $table->string('kode_antrian', 20);
            $table->string('nama_pasien')->nullable();
            $table->string('jenis_poli', 50)->default('Poli Umum');
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'dilewati'])->default('menunggu');
            $table->date('tanggal_antrian')->index();

            // Relasi ke tabel users
            $table->foreignId('diinput_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dilayani_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('waktu_dipanggil')->nullable();
            $table->timestamps();

            // Mencegah ada nomor urut ganda di tanggal yang sama
            $table->unique(['tanggal_antrian', 'nomor_urut']);
        });
    }

    /**
     * Membatalkan migrasi (drop tabel).
     */
    public function down(): void
    {
        Schema::dropIfExists('antrians');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
