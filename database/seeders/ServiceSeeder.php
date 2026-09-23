<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Fotokopi hitam putih',
                'description' => 'Fotokopi dokumen hitam putih untuk kebutuhan sekolah, kantor, dan keperluan umum. Harga contoh per lembar.',
                'category' => 'Fotokopi',
                'unit' => 'lembar',
                'price' => 300,
            ],
            [
                'name' => 'Fotokopi warna',
                'description' => 'Fotokopi warna untuk dokumen yang membutuhkan tampilan berwarna. Harga contoh per lembar.',
                'category' => 'Fotokopi',
                'unit' => 'lembar',
                'price' => 1000,
            ],
            [
                'name' => 'Print dokumen',
                'description' => 'Cetak dokumen dari file digital (PDF, Word, dan format umum lainnya). Harga contoh per lembar.',
                'category' => 'Print',
                'unit' => 'lembar',
                'price' => 500,
            ],
            [
                'name' => 'Print foto',
                'description' => 'Cetak foto berbagai ukuran dengan hasil yang tajam. Harga contoh per lembar.',
                'category' => 'Print',
                'unit' => 'lembar',
                'price' => 3000,
            ],
            [
                'name' => 'Scan dokumen',
                'description' => 'Pemindaian dokumen ke format digital. Harga contoh per lembar.',
                'category' => 'Scan',
                'unit' => 'lembar',
                'price' => 500,
            ],
            [
                'name' => 'Jilid',
                'description' => 'Penjilidan dokumen agar rapi dan tahan lama. Harga contoh per jilid.',
                'category' => 'Jilid',
                'unit' => 'isi',
                'price' => 5000,
            ],
            [
                'name' => 'Laminasi',
                'description' => 'Pelapisan dokumen dengan plastik laminasi. Harga contoh per lembar.',
                'category' => 'Jilid',
                'unit' => 'lembar',
                'price' => 3000,
            ],
        ];

        foreach ($services as $service) {
            Service::create([
                ...$service,
                'slug' => Str::slug($service['name']),
                'is_active' => true,
            ]);
        }
    }
}
