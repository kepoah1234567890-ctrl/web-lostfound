<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->insert([
            [
                'id' => 1,
                'nama' => 'Administrator Lost & Found',
                'nis' => 'ADM001',
                'email' => 'admin.lostfound@gmail.com',
                'password' => Hash::make('admin1234'),
                'kelas' => 'Staf Kesiswaan',
                'role' => 'admin',
                'created_at' => $now,
            ],
            [
                'id' => 2,
                'nama' => 'Budi Santoso',
                'nis' => '22231001',
                'email' => 'budi.santoso@gmail.com',
                'password' => Hash::make('user123'),
                'kelas' => 'XII RPL 1',
                'role' => 'siswa',
                'created_at' => $now,
            ],
            [
                'id' => 3,
                'nama' => 'Siti Rahmawati',
                'nis' => '22231002',
                'email' => 'siti.rahmawati@gmail.com',
                'password' => Hash::make('user123'),
                'kelas' => 'XI TKJ 2',
                'role' => 'siswa',
                'created_at' => $now,
            ],
            [
                'id' => 4,
                'nama' => 'Ahmad Fauzan',
                'nis' => '22231003',
                'email' => 'ahmad.fauzan@gmail.com',
                'password' => Hash::make('user123'),
                'kelas' => 'X DKV 1',
                'role' => 'siswa',
                'created_at' => $now,
            ],
            [
                'id' => 5,
                'nama' => 'Dewi Lestari',
                'nis' => '22231004',
                'email' => 'dewi.lestari@gmail.com',
                'password' => Hash::make('user123'),
                'kelas' => 'XII RPL 2',
                'role' => 'siswa',
                'created_at' => $now,
            ],
            [
                'id' => 6,
                'nama' => 'Rizky Pratama',
                'nis' => '22231005',
                'email' => 'rizky.pratama@gmail.com',
                'password' => Hash::make('user123'),
                'kelas' => 'XI TKJ 1',
                'role' => 'siswa',
                'created_at' => $now,
            ],
        ]);

        DB::table('barang')->insert([
            [
                'id' => 1,
                'nama_barang' => 'Tas Ransel Hitam Eiger',
                'kategori' => 'Tas',
                'warna' => 'Hitam',
                'deskripsi' => 'Tas ransel merk Eiger warna hitam dengan gantungan kunci robot.',
                'lokasi_ditemukan' => 'Lab Komputer RPL 2',
                'tanggal_ditemukan' => $now->toDateString(),
                'foto' => null,
                'status' => 'tersedia',
                'ditemukan_oleh' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'nama_barang' => 'Tumbler Biru Tupperware',
                'kategori' => 'Botol Minum',
                'warna' => 'Biru',
                'deskripsi' => 'Botol minum biru ukuran 750ml dengan stiker logo sekolah.',
                'lokasi_ditemukan' => 'Kantin Sekolah',
                'tanggal_ditemukan' => $now->toDateString(),
                'foto' => null,
                'status' => 'dikembalikan',
                'ditemukan_oleh' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'nama_barang' => 'Jaket Hoodie Abu-abu',
                'kategori' => 'Pakaian',
                'warna' => 'Abu-abu',
                'deskripsi' => 'Jaket hoodie tertinggal di bangku pinggir lapangan basket.',
                'lokasi_ditemukan' => 'Lapangan Basket',
                'tanggal_ditemukan' => $now->toDateString(),
                'foto' => null,
                'status' => 'dikembalikan',
                'ditemukan_oleh' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('laporan_hilang')->insert([
            [
                'id' => 1,
                'user_id' => 2,
                'nama_barang' => 'Dompet Coklat Kulit',
                'kategori' => 'Dompet',
                'warna' => 'Coklat',
                'deskripsi' => 'Dompet lipat kulit warna coklat berisi kartu pelajar.',
                'lokasi_terakhir' => 'Kantin Sekolah',
                'tanggal_hilang' => $now->toDateString(),
                'status' => 'menunggu',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'user_id' => 4,
                'nama_barang' => 'Kalkulator Casio',
                'kategori' => 'Elektronik',
                'warna' => 'Hitam',
                'deskripsi' => 'Kalkulator scientific warna hitam.',
                'lokasi_terakhir' => 'Lab Fisika',
                'tanggal_hilang' => $now->toDateString(),
                'status' => 'diverifikasi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('klaim')->insert([
            [
                'id' => 1,
                'barang_id' => 3,
                'user_id' => 4,
                'ciri_barang' => 'Jaket hoodie abu-abu dengan ciri jahitan khusus di saku.',
                'status' => 'disetujui',
                'catatan_admin' => 'Ciri-ciri cocok. Silakan ambil di ruang kesiswaan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('pengembalian')->insert([
            [
                'id' => 1,
                'barang_id' => 3,
                'klaim_id' => 1,
                'admin_id' => 1,
                'tanggal_dikembalikan' => $now,
                'catatan' => 'Barang diserahkan setelah verifikasi.',
            ],
        ]);
    }
}
