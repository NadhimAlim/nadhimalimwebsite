<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Project;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        // Layanan Jasa
        Service::create([
            'title' => 'Landing Page / Company Profile',
            'icon' => 'bi-laptop',
            'description' => 'Desain website profesional, responsif, dan elegan untuk meningkatkan kredibilitas bisnis Anda.',
            'starting_price' => 1500000,
        ]);

        Service::create([
            'title' => 'Toko Online / E-Commerce',
            'icon' => 'bi-cart-check',
            'description' => 'Sistem penjualan online lengkap dengan manajemen produk, keranjang belanja, dan integrasi payment gateway.',
            'starting_price' => 3500000,
        ]);

        Service::create([
            'title' => 'Custom Web Application',
            'icon' => 'bi-code-slash',
            'description' => 'Pengembangan aplikasi berbasis web berbasis Laravel disesuaikan dengan kebutuhan alur bisnis Anda.',
            'starting_price' => 5000000,
        ]);

        // Portofolio Project
        Project::create([
            'title' => 'Sistem Informasi Manajemen Sekolah',
            'category' => 'Web Application',
            'description' => 'Aplikasi pengelolaan data siswa, guru, dan nilai berbasis Laravel.',
            'image' => 'school.jpg',
            'link' => 'https://example.com',
        ]);

        Project::create([
            'title' => 'E-Commerce UMKM Kuliner',
            'category' => 'Toko Online',
            'description' => 'Platform pemesanan makanan online terintegrasi pembayaran digital.',
            'image' => 'ecommerce.jpg',
            'link' => 'https://example.com',
        ]);
    }
}