<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama', 100);
            $table->string('nis', 30)->unique();
            $table->string('email', 100)->unique();
            $table->string('no_telepon', 20)->nullable();
            $table->string('password');
            $table->string('kelas', 50)->nullable();
            $table->enum('role', ['siswa', 'admin'])->default('siswa');
            $table->string('google_id')->nullable()->unique();
            $table->string('avatar', 500)->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('barang', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nama_barang', 100);
            $table->string('kategori', 50);
            $table->string('warna', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('lokasi_ditemukan', 100);
            $table->date('tanggal_ditemukan');
            $table->string('foto')->nullable();
            $table->enum('status', ['tersedia', 'menunggu_klaim', 'diklaim', 'dikembalikan'])
                ->default('tersedia');
            $table->unsignedInteger('ditemukan_oleh')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('ditemukan_oleh')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('laporan_hilang', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('nama_barang', 100);
            $table->string('kategori', 50);
            $table->string('warna', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('lokasi_terakhir', 100)->nullable();
            $table->date('tanggal_hilang');
            $table->string('foto')->nullable();
            $table->enum('status', ['menunggu', 'diverifikasi', 'ditemukan', 'selesai'])
                ->default('menunggu');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('klaim', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('barang_id');
            $table->unsignedInteger('user_id');
            $table->text('ciri_barang');
            $table->string('bukti_kepemilikan')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('barang_id')->references('id')->on('barang')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('pengembalian', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('barang_id');
            $table->unsignedInteger('klaim_id');
            $table->unsignedInteger('admin_id');
            $table->dateTime('tanggal_dikembalikan')->useCurrent();
            $table->text('catatan')->nullable();
            $table->foreign('barang_id')->references('id')->on('barang')->cascadeOnDelete();
            $table->foreign('klaim_id')->references('id')->on('klaim')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->string('email', 100);
            $table->char('token_hash', 64)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('email', 'idx_reset_email');
            $table->index('expires_at', 'idx_reset_expires');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('matching_barang', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('laporan_hilang_id');
            $table->unsignedInteger('barang_ditemukan_id');
            $table->integer('score')->default(0);
            $table->string('label', 100)->nullable();
            $table->text('reasons')->nullable();
            $table->enum('status', ['ditemukan', 'dihubungi', 'diklaim', 'selesai', 'ditolak'])
                ->default('ditemukan');
            $table->unsignedInteger('dibuat_oleh')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['laporan_hilang_id', 'barang_ditemukan_id'], 'unique_matching');
            $table->index('laporan_hilang_id', 'idx_matching_laporan_hilang');
            $table->index('barang_ditemukan_id', 'idx_matching_barang');
            $table->index('dibuat_oleh', 'idx_matching_dibuat_oleh');
            $table->foreign('laporan_hilang_id')->references('id')->on('laporan_hilang')->cascadeOnDelete();
            $table->foreign('barang_ditemukan_id')->references('id')->on('barang')->cascadeOnDelete();
            $table->foreign('dibuat_oleh')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('aktivitas_user', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('target_user_id')->nullable();
            $table->unsignedInteger('laporan_hilang_id')->nullable();
            $table->unsignedInteger('barang_id')->nullable();
            $table->unsignedInteger('klaim_id')->nullable();
            $table->unsignedInteger('matching_id')->nullable();
            $table->string('aktivitas', 100);
            $table->text('deskripsi')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('user_id', 'idx_aktivitas_user_id');
            $table->index('target_user_id', 'idx_aktivitas_target_user_id');
            $table->index('laporan_hilang_id', 'idx_aktivitas_laporan_hilang_id');
            $table->index('barang_id', 'idx_aktivitas_barang_id');
            $table->index('klaim_id', 'idx_aktivitas_klaim_id');
            $table->index('matching_id', 'idx_aktivitas_matching_id');
            $table->index('created_at', 'idx_aktivitas_created_at');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('target_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('laporan_hilang_id')->references('id')->on('laporan_hilang')->nullOnDelete();
            $table->foreign('barang_id')->references('id')->on('barang')->nullOnDelete();
            $table->foreign('klaim_id')->references('id')->on('klaim')->nullOnDelete();
            $table->foreign('matching_id')->references('id')->on('matching_barang')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_user');
        Schema::dropIfExists('matching_barang');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('pengembalian');
        Schema::dropIfExists('klaim');
        Schema::dropIfExists('laporan_hilang');
        Schema::dropIfExists('barang');
        Schema::dropIfExists('users');
    }
};
