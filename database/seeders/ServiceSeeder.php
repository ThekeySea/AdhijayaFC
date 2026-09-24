<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use App\Models\ServiceOptionGroup;
use App\Models\ServicePriceTier;
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
        ServiceOption::query()->delete();
        ServiceOptionGroup::query()->delete();
        ServicePriceTier::query()->delete();
        Service::query()->delete();
        ServiceCategory::query()->delete();

        $categories = $this->categories();
        $categoryIds = [];

        foreach ($categories as $index => $category) {
            $model = ServiceCategory::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'] ?? null,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
            $categoryIds[$category['slug']] = $model->id;
        }

        foreach ($this->jasaServices() as $row) {
            $this->createService($row, $categoryIds[$row['category']], Service::TYPE_JASA);
        }

        foreach ($this->jualServices() as $row) {
            $this->createService($row, null, Service::TYPE_JUAL);
        }
    }

    /**
     * @return list<array{name: string, slug: string, description?: string}>
     */
    private function categories(): array
    {
        return [
            ['name' => 'Digital Print', 'slug' => 'digital-print', 'description' => 'Print ukuran besar A0–A5 dan scan copy.'],
            ['name' => 'Cetak Buku', 'slug' => 'cetak-buku', 'description' => 'Nota, blocknote, agenda, buku, dan karcis.'],
            ['name' => 'Undangan & Event', 'slug' => 'undangan-event', 'description' => 'Undangan dan plakat untuk acara.'],
            ['name' => 'Alat Tulis Stationery', 'slug' => 'alat-tulis-stationery', 'description' => 'Stempel, kartu, kop surat, dan kebutuhan kantor.'],
            ['name' => 'Media Promosi & UV', 'slug' => 'media-promosi-uv', 'description' => 'Banner, brosur, plotter, dan media promosi.'],
            ['name' => 'Lain Lain', 'slug' => 'lain-lain', 'description' => 'Pin, stiker, kalender, paper bag, dan lainnya.'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function jasaServices(): array
    {
        return [
            // Digital Print — no min_quantity (by design)
            ['category' => 'digital-print', 'name' => 'Print A0', 'description' => 'Print ukuran A0 untuk poster, peta, dan desain besar. Pilih bahan kertas, sisi cetak, serta finishing laminating atau jilid.', 'unit' => 'lembar', 'price' => 25000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->printGroups(3.0)],
            ['category' => 'digital-print', 'name' => 'Print A1', 'description' => 'Print ukuran A1 untuk display dan presentasi. Tentukan jenis kertas, satu atau dua sisi, dan laminating bila perlu.', 'unit' => 'lembar', 'price' => 18000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->printGroups(2.5)],
            ['category' => 'digital-print', 'name' => 'Print A2', 'description' => 'Print ukuran A2 untuk kebutuhan studio dan kantor. Lengkapi pilihan bahan, sisi cetak, dan finishing.', 'unit' => 'lembar', 'price' => 12000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->printGroups(2.0)],
            ['category' => 'digital-print', 'name' => 'Print A3', 'description' => 'Print ukuran A3 untuk proposal dan materi rapat. Pilih kertas, sisi cetak, serta laminating atau jilid.', 'unit' => 'lembar', 'price' => 7000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->printGroups(1.5)],
            ['category' => 'digital-print', 'name' => 'Print A4', 'description' => 'Print dokumen A4 hitam putih atau warna sesuai file. Bebasmu memilih bahan kertas, sisi cetak, dan finishing.', 'unit' => 'lembar', 'price' => 500, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->printGroups(1.0)],
            ['category' => 'digital-print', 'name' => 'Print A5', 'description' => 'Print ukuran A5 untuk kartu, leaflet kecil, dan catatan. Sertakan file desain lalu pilih opsi cetak.', 'unit' => 'lembar', 'price' => 400, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->printGroups(0.8)],
            ['category' => 'digital-print', 'name' => 'Scan Copy', 'description' => 'Pemindaian dokumen ke file digital sekaligus salinan cetak bila perlu. Pilih ukuran kertas hasil scan.', 'unit' => 'lembar', 'price' => 700, 'file_requirement' => Service::FILE_NONE, 'option_groups' => [
                [
                    'name' => 'Produk & Bahan',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'HVS 70 gsm', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'HVS 80 gsm', 'price' => 100, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Art Paper 120 gsm', 'price' => 400, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Ukuran',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'A5', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A4', 'price' => 300, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A3', 'price' => 900, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A2', 'price' => 1800, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A1', 'price' => 3500, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A0', 'price' => 6000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
            ]],

            // Cetak Buku
            ['category' => 'cetak-buku', 'name' => 'Nota NCR', 'description' => 'Cetak nota rangkap NCR untuk kasir dan administrasi. Pilih jenis kertas NCR, ukuran, dan jumlah lembar per buku.', 'unit' => 'buku', 'price' => 45000, 'min_quantity' => 5, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => [
                [
                    'name' => 'Produk & Bahan',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'NCR putih 2 rangkap', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'NCR putih-kuning 3 rangkap', 'price' => 8000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'NCR putih-kuning-merah 4 rangkap', 'price' => 15000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Ukuran',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'F8 (16,5 x 10 cm)', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A5', 'price' => 3000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A4', 'price' => 7000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Jumlah lembar per buku',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => '50 lembar', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => '100 lembar', 'price' => 15000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => '150 lembar', 'price' => 30000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
            ]],
            ['category' => 'cetak-buku', 'name' => 'Buku Blocknote', 'description' => 'Buku blocknote custom untuk meeting dan promosi. Atur ukuran, jenis isi, jumlah halaman, dan laminating sampul.', 'unit' => 'pcs', 'price' => 25000, 'min_quantity' => 10, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => [
                [
                    'name' => 'Ukuran',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'A6', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A5', 'price' => 5000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Isi',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Kosong', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Garis', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Dot grid', 'price' => 1000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Jumlah halaman',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => '32 halaman', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => '64 halaman', 'price' => 8000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => '96 halaman', 'price' => 15000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Laminating sampul',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Tidak pakai', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Laminating doff', 'price' => 3000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Laminating gloss', 'price' => 3000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
            ]],
            ['category' => 'cetak-buku', 'name' => 'Buku Agenda', 'description' => 'Cetak buku agenda dengan desain sampul sendiri. Pilih jenis jilid, isi, jumlah halaman, dan ukuran.', 'unit' => 'pcs', 'price' => 55000, 'min_quantity' => 10, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => [
                [
                    'name' => 'Jenis jilid',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Spiral', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Perfect bound', 'price' => 10000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Hardcover', 'price' => 30000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Isi',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Kosong', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Planner / jadwal', 'price' => 8000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Jumlah halaman',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => '64 halaman', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => '96 halaman', 'price' => 10000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => '128 halaman', 'price' => 20000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Ukuran',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'A5', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A4', 'price' => 15000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
            ]],
            ['category' => 'cetak-buku', 'name' => 'Cetak Buku A5', 'description' => 'Jasa cetak buku ukuran A5 jumlah kecil hingga menengah. Pilih model buku: spiral, softcover, atau hardcover.', 'unit' => 'buku', 'price' => 18000, 'min_quantity' => 10, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->bookModelGroups('A5')],
            ['category' => 'cetak-buku', 'name' => 'Cetak Buku A3', 'description' => 'Jasa cetak buku ukuran A3 untuk portofolio dan katalog. Pilih bahan sampul dan jenis jilid sesuai kebutuhan.', 'unit' => 'buku', 'price' => 65000, 'min_quantity' => 5, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->bookModelGroups('A3')],
            ['category' => 'cetak-buku', 'name' => 'Cetak Buku A4', 'description' => 'Jasa cetak buku ukuran A4 untuk laporan dan skripsi. Lengkapi file lalu pilih model buku yang diinginkan.', 'unit' => 'buku', 'price' => 42000, 'min_quantity' => 5, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->bookModelGroups('A4')],
            ['category' => 'cetak-buku', 'name' => 'Buku Karcis Tiket', 'description' => 'Cetak karcis tiket event dengan opsi nomor seri dan perforasi. Pilih ukuran karcis sesuai event.', 'unit' => 'buku', 'price' => 55000, 'min_quantity' => 5, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => [
                [
                    'name' => 'Ukuran karcis',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'F8 (16,5 x 10 cm)', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A6', 'price' => -2000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'A5', 'price' => 5000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Nomor seri',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Tanpa nomor seri', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Dengan nomor seri', 'price' => 5000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Perforasi',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Tanpa perforasi', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Dengan perforasi', 'price' => 3000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
            ]],
            ['category' => 'cetak-buku', 'name' => 'Buku Yasin', 'description' => 'Cetak buku yasin untuk tahlil dan doa bersama. Pilih kertas, jenis jilid, printing, dan free marker.', 'unit' => 'buku', 'price' => 10000, 'min_quantity' => 10, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => [
                [
                    'name' => 'Produk & Bahan',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'HVS 80 gsm', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Book paper 70 gsm', 'price' => 500, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Art paper 150 gsm', 'price' => 1500, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Jenis jilid',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Softcover', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Hardcover', 'price' => 4000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Free marker',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Tanpa free marker', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Free marker', 'price' => 1000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
                [
                    'name' => 'Printing',
                    'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Hitam putih', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                        ['name' => 'Full color', 'price' => 3000, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ],
                ],
            ], 'price_tiers' => [
                ['min_qty' => 1, 'max_qty' => 100, 'unit_price' => 10000],
                ['min_qty' => 101, 'max_qty' => 250, 'unit_price' => 7500],
                ['min_qty' => 251, 'max_qty' => 500, 'unit_price' => 6000],
                ['min_qty' => 501, 'max_qty' => null, 'unit_price' => 5000],
            ]],

            // Undangan & Event
            ['category' => 'undangan-event', 'name' => 'Plakat', 'description' => 'Plakat penghargaan dan kenang-kenangan custom. Pilih bahan, ukuran, base, dan box bila perlu.', 'unit' => 'pcs', 'price' => 150000, 'min_quantity' => 5, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Akrilik', 'price' => 0],
                    ['name' => 'Kayu', 'price' => -20000],
                    ['name' => 'Kaca', 'price' => 30000],
                ]],
                ['name' => 'Ukuran', 'required' => true, 'options' => [
                    ['name' => '15 x 20 cm', 'price' => 0],
                    ['name' => '20 x 25 cm', 'price' => 25000],
                    ['name' => '25 x 30 cm', 'price' => 50000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Tanpa box', 'price' => 0],
                    ['name' => 'Box beludru', 'price' => 35000],
                    ['name' => 'Base custom', 'price' => 20000],
                ]],
            ])],
            ['category' => 'undangan-event', 'name' => 'Undangan', 'description' => 'Cetak undangan pernikahan, ulang tahun, dan acara lainnya. Pilih bahan, lipatan, amplop, dan finishing.', 'unit' => 'pcs', 'price' => 8000, 'min_quantity' => 50, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Biro 220 gsm', 'price' => 0],
                    ['name' => 'Art carton 260 gsm', 'price' => 2000],
                    ['name' => 'Tissue 200 gsm', 'price' => 3000],
                ]],
                ['name' => 'Jenis lipatan', 'required' => true, 'options' => [
                    ['name' => '1 lipat (folding)', 'price' => 0],
                    ['name' => '2 lipat', 'price' => 500],
                    ['name' => '3 lipat', 'price' => 1000],
                ]],
                ['name' => 'Amplop', 'required' => false, 'options' => [
                    ['name' => 'Tanpa amplop', 'price' => 0],
                    ['name' => 'Amplop undangan', 'price' => 1500],
                ]],
                ['name' => 'Laminating & Jilid', 'required' => false, 'options' => [
                    ['name' => 'Tidak pakai', 'price' => 0],
                    ['name' => 'Laminating full', 'price' => 1500],
                    ['name' => 'Laminating depan', 'price' => 800],
                ]],
            ])],

            // Alat Tulis Stationery
            ['category' => 'alat-tulis-stationery', 'name' => 'Stempel', 'description' => 'Stempel nama, instansi, atau toko dengan desain custom. Pilih jenis stempel, ukuran die, dan warna tinta.', 'unit' => 'pcs', 'price' => 75000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Trodat printy', 'price' => 0],
                    ['name' => 'Stempel kayu', 'price' => -20000],
                    ['name' => 'Stempel karet', 'price' => -10000],
                ]],
                ['name' => 'Ukuran die', 'required' => true, 'options' => [
                    ['name' => 'Kecil', 'price' => 0],
                    ['name' => 'Sedang', 'price' => 5000],
                    ['name' => 'Besar', 'price' => 10000],
                ]],
                ['name' => 'Warna tinta', 'required' => true, 'options' => [
                    ['name' => 'Biru', 'price' => 0],
                    ['name' => 'Merah', 'price' => 0],
                    ['name' => 'Hitam', 'price' => 0],
                ]],
            ])],
            ['category' => 'alat-tulis-stationery', 'name' => 'Id Card', 'description' => 'Kartu identitas dengan cetak full color dan laminating tipis. Pilih bahan, laminating, dan aksesori.', 'unit' => 'pcs', 'price' => 18000, 'min_quantity' => 10, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'PVC 0,76 mm', 'price' => 0],
                    ['name' => 'PVC 0,30 mm tipis', 'price' => -3000],
                    ['name' => 'Art carton 260 gsm', 'price' => -5000],
                ]],
                ['name' => 'Laminating & Jilid', 'required' => true, 'options' => [
                    ['name' => 'Laminating doff', 'price' => 0],
                    ['name' => 'Laminating gloss', 'price' => 0],
                    ['name' => 'Tanpa laminating', 'price' => -2000],
                ]],
                ['name' => 'Aksesori', 'required' => false, 'options' => [
                    ['name' => 'Tanpa aksesori', 'price' => 0],
                    ['name' => 'Slot lanyard', 'price' => 1000],
                    ['name' => 'Clip / peniti', 'price' => 2000],
                ]],
            ])],
            ['category' => 'alat-tulis-stationery', 'name' => 'Kartu Nama', 'description' => 'Kartu nama profesional untuk bisnis dan relasi. Pilih bahan, finishing, dan bentuk kartu.', 'unit' => 'set', 'price' => 120000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Art carton 260 gsm', 'price' => 0],
                    ['name' => 'Art carton 300 gsm', 'price' => 15000],
                    ['name' => 'PVC', 'price' => 25000],
                ]],
                ['name' => 'Laminating & Jilid', 'required' => false, 'options' => [
                    ['name' => 'Tanpa laminating', 'price' => 0],
                    ['name' => 'Laminating doff', 'price' => 10000],
                    ['name' => 'Laminating gloss', 'price' => 10000],
                ]],
                ['name' => 'Bentuk', 'required' => false, 'options' => [
                    ['name' => 'Standar', 'price' => 0],
                    ['name' => 'Rounded corner', 'price' => 5000],
                ]],
            ])],
            ['category' => 'alat-tulis-stationery', 'name' => 'Pulpen Promosi', 'description' => 'Pulpen promosi custom untuk acara dan branding. Pilih warna, kemasan, dan area sablon.', 'unit' => 'pcs', 'price' => 9000, 'min_quantity' => 50, 'file_requirement' => Service::FILE_OPTIONAL, 'option_groups' => $this->genericGroups([
                ['name' => 'Warna pulpen', 'required' => true, 'options' => [
                    ['name' => 'Hitam', 'price' => 0],
                    ['name' => 'Biru', 'price' => 0],
                    ['name' => 'Merah', 'price' => 0],
                    ['name' => 'Putih', 'price' => 500],
                ]],
                ['name' => 'Kemasan', 'required' => false, 'options' => [
                    ['name' => 'Satuan', 'price' => 0],
                    ['name' => 'Box per 50', 'price' => 15000],
                ]],
                ['name' => 'Area sablon', 'required' => false, 'options' => [
                    ['name' => '1 titik', 'price' => 0],
                    ['name' => 'Full body', 'price' => 1500],
                ]],
            ])],
            ['category' => 'alat-tulis-stationery', 'name' => 'Kop Surat', 'description' => 'Cetak kop surat resmi untuk kantor dan usaha. Pilih ukuran kertas dan hasil cetak.', 'unit' => 'lembar', 'price' => 1500, 'min_quantity' => 100, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'HVS 70 gsm', 'price' => 0],
                    ['name' => 'HVS 80 gsm', 'price' => 200],
                    ['name' => 'Concord 100 gsm', 'price' => 500],
                ]],
                ['name' => 'Ukuran', 'required' => true, 'options' => [
                    ['name' => 'F4', 'price' => 0],
                    ['name' => 'A4', 'price' => 0],
                ]],
                ['name' => 'Cetak berapa sisi', 'required' => false, 'options' => [
                    ['name' => 'Satu sisi', 'price' => 0],
                    ['name' => 'Dua sisi', 'price' => 300],
                ]],
            ])],
            ['category' => 'alat-tulis-stationery', 'name' => 'Amplop', 'description' => 'Cetak amplop custom atau ukuran standar. Pilih jenis amplop dan sisi cetak.', 'unit' => 'pcs', 'price' => 3500, 'min_quantity' => 100, 'file_requirement' => Service::FILE_OPTIONAL, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Amplop jerami C5', 'price' => 0],
                    ['name' => 'Amplop DL', 'price' => -500],
                    ['name' => 'Amplop kabinet', 'price' => 1000],
                ]],
                ['name' => 'Cetak berapa sisi', 'required' => false, 'options' => [
                    ['name' => 'Satu sisi', 'price' => 0],
                    ['name' => 'Dua sisi', 'price' => 500],
                ]],
            ])],
            ['category' => 'alat-tulis-stationery', 'name' => 'Tali Lanyard', 'description' => 'Tali lanyard print sablon untuk event dan kantor. Pilih lebar, klip, dan sisi cetak.', 'unit' => 'pcs', 'price' => 22000, 'min_quantity' => 20, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Polyster 2 cm', 'price' => 0],
                    ['name' => 'Polyster 2,5 cm', 'price' => 3000],
                ]],
                ['name' => 'Aksesori', 'required' => false, 'options' => [
                    ['name' => 'Klip standar', 'price' => 0],
                    ['name' => 'Snap buckle', 'price' => 2000],
                    ['name' => 'J-trigger', 'price' => 1500],
                ]],
                ['name' => 'Cetak berapa sisi', 'required' => false, 'options' => [
                    ['name' => 'Satu sisi', 'price' => 0],
                    ['name' => 'Dua sisi', 'price' => 4000],
                ]],
            ])],
            ['category' => 'alat-tulis-stationery', 'name' => 'Map', 'description' => 'Map folder custom untuk dokumen dan presentasi. Pilih jenis map, bahan, dan jumlah kantong.', 'unit' => 'pcs', 'price' => 8500, 'min_quantity' => 20, 'file_requirement' => Service::FILE_OPTIONAL, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Map kantong F4', 'price' => 0],
                    ['name' => 'Fastbind', 'price' => 3000],
                    ['name' => 'Map presentasi', 'price' => 5000],
                ]],
                ['name' => 'Jumlah kantong', 'required' => false, 'options' => [
                    ['name' => '1 kantong', 'price' => 0],
                    ['name' => '2 kantong', 'price' => 1500],
                ]],
            ])],
            ['category' => 'alat-tulis-stationery', 'name' => 'Kartu Custom', 'description' => 'Kartu custom untuk member, akses, atau kebutuhan khusus. Pilih bahan, laminating, dan slot.', 'unit' => 'pcs', 'price' => 15000, 'min_quantity' => 10, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'PVC', 'price' => 0],
                    ['name' => 'Art carton 260 gsm', 'price' => -4000],
                ]],
                ['name' => 'Laminating & Jilid', 'required' => false, 'options' => [
                    ['name' => 'Laminating doff', 'price' => 0],
                    ['name' => 'Laminating gloss', 'price' => 0],
                ]],
                ['name' => 'Aksesori', 'required' => false, 'options' => [
                    ['name' => 'Tanpa slot', 'price' => 0],
                    ['name' => 'Slot magnet', 'price' => 3000],
                    ['name' => 'Slot QR', 'price' => 1000],
                ]],
            ])],

            // Media Promosi & UV
            ['category' => 'media-promosi-uv', 'name' => 'XY Banner', 'description' => 'Print XY banner indoor/outdoor dengan hasil tajam. Pilih media, pemakaian, dan finishing.', 'unit' => 'meter', 'price' => 35000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Flexi', 'price' => 0],
                    ['name' => 'Albatros', 'price' => 15000],
                    ['name' => 'Backdrop / satin', 'price' => 25000],
                ]],
                ['name' => 'Pemakaian', 'required' => true, 'options' => [
                    ['name' => 'Indoor', 'price' => 0],
                    ['name' => 'Outdoor', 'price' => 5000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Mata ayam + rompeng', 'price' => 5000],
                    ['name' => 'Tanpa finishing', 'price' => 0],
                ]],
            ])],
            ['category' => 'media-promosi-uv', 'name' => 'Roll Banner', 'description' => 'Roll banner stand portable untuk promo dan event. Pilih lebar stand, tinggi, dan tas.', 'unit' => 'pcs', 'price' => 185000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => '80 cm x 200 cm', 'price' => 0],
                    ['name' => '85 cm x 200 cm', 'price' => 15000],
                    ['name' => '100 cm x 200 cm', 'price' => 35000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Tanpa tas', 'price' => 0],
                    ['name' => 'Include tas', 'price' => 25000],
                ]],
            ])],
            ['category' => 'media-promosi-uv', 'name' => 'Brosur Flyer', 'description' => 'Cetak brosur dan flyer promosi berbagai ukuran. Pilih ukuran, kertas, sisi cetak, dan laminating.', 'unit' => 'lembar', 'price' => 1200, 'min_quantity' => 50, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Ukuran', 'required' => true, 'options' => [
                    ['name' => 'A6', 'price' => 0],
                    ['name' => 'A5', 'price' => 400],
                    ['name' => 'A4', 'price' => 800],
                ]],
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'HVS 80 gsm', 'price' => 0],
                    ['name' => 'Art paper 120 gsm', 'price' => 300],
                    ['name' => 'Art paper 150 gsm', 'price' => 500],
                ]],
                ['name' => 'Cetak berapa sisi', 'required' => false, 'options' => [
                    ['name' => 'Satu sisi', 'price' => 0],
                    ['name' => 'Dua sisi', 'price' => 400],
                ]],
                ['name' => 'Laminating & Jilid', 'required' => false, 'options' => [
                    ['name' => 'Tidak pakai', 'price' => 0],
                    ['name' => 'Laminating full', 'price' => 1500],
                ]],
            ])],
            ['category' => 'media-promosi-uv', 'name' => 'Tripod Banner', 'description' => 'Tripod banner untuk display promosi di acara. Pilih ukuran dan apakah include print media.', 'unit' => 'set', 'price' => 320000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => '80 x 180 cm', 'price' => 0],
                    ['name' => '60 x 160 cm', 'price' => -40000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Tripod saja', 'price' => 0],
                    ['name' => 'Include print media', 'price' => 120000],
                ]],
            ])],
            ['category' => 'media-promosi-uv', 'name' => 'Banner 4 & 5 Meter', 'description' => 'Cetak banner ukuran 4 atau 5 meter untuk spanduk dan backdrop. Pilih panjang dan finishing.', 'unit' => 'meter', 'price' => 45000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Ukuran', 'required' => true, 'options' => [
                    ['name' => '4 meter', 'price' => 0],
                    ['name' => '5 meter', 'price' => 10000],
                ]],
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Flexi outdoor', 'price' => 0],
                    ['name' => 'Albatros', 'price' => 15000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Mata ayam', 'price' => 5000],
                    ['name' => 'Tanpa finishing', 'price' => 0],
                ]],
            ])],
            ['category' => 'media-promosi-uv', 'name' => 'Print Plotter', 'description' => 'Print plotter untuk gambar teknik, desain, dan poster besar. Pilih kertas, sisi, dan laminating.', 'unit' => 'meter', 'price' => 40000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'HVS blueprint', 'price' => 0],
                    ['name' => 'Photo paper', 'price' => 20000],
                    ['name' => 'Matte photo', 'price' => 15000],
                ]],
                ['name' => 'Cetak berapa sisi', 'required' => false, 'options' => [
                    ['name' => 'Satu sisi', 'price' => 0],
                    ['name' => 'Dua sisi', 'price' => 15000],
                ]],
                ['name' => 'Laminating & Jilid', 'required' => false, 'options' => [
                    ['name' => 'Tidak pakai', 'price' => 0],
                    ['name' => 'Laminating full', 'price' => 25000],
                ]],
            ])],
            ['category' => 'media-promosi-uv', 'name' => 'Print Kertas Metera', 'description' => 'Print di kertas metera untuk kebutuhan legal dan administrasi. Pilih ukuran dokumen.', 'unit' => 'lembar', 'price' => 3500, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Ukuran', 'required' => true, 'options' => [
                    ['name' => 'A4', 'price' => 0],
                    ['name' => 'F4', 'price' => 0],
                ]],
            ])],

            // Lain Lain
            ['category' => 'lain-lain', 'name' => 'Pin', 'description' => 'Pin badge custom untuk komunitas dan event. Pilih ukuran, bentuk, dan isi paket.', 'unit' => 'pcs', 'price' => 8500, 'min_quantity' => 50, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Ukuran', 'required' => true, 'options' => [
                    ['name' => '44 mm', 'price' => 0],
                    ['name' => '58 mm', 'price' => 2000],
                ]],
                ['name' => 'Bentuk', 'required' => true, 'options' => [
                    ['name' => 'Bulat', 'price' => 0],
                    ['name' => 'Kotak', 'price' => 1000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Peniti', 'price' => 0],
                    ['name' => 'Kunci gantung', 'price' => 1500],
                ]],
            ])],
            ['category' => 'lain-lain', 'name' => 'Gantungan Kunci', 'description' => 'Gantungan kunci custom akrilik atau karet. Pilih bahan, bentuk, dan finishing.', 'unit' => 'pcs', 'price' => 15000, 'min_quantity' => 50, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Akrilik', 'price' => 0],
                    ['name' => 'Karet', 'price' => 3000],
                    ['name' => 'Metal', 'price' => 8000],
                ]],
                ['name' => 'Bentuk', 'required' => false, 'options' => [
                    ['name' => 'Custom cut', 'price' => 0],
                    ['name' => 'Standar bulat', 'price' => -2000],
                ]],
                ['name' => 'Laminating & Jilid', 'required' => false, 'options' => [
                    ['name' => 'Laminating doff', 'price' => 0],
                    ['name' => 'Laminating gloss', 'price' => 0],
                ]],
            ])],
            ['category' => 'lain-lain', 'name' => 'Stiker', 'description' => 'Cetak stiker custom berbagai bentuk dan ukuran. Pilih bahan, finishing, dan bentuk potong.', 'unit' => 'lembar', 'price' => 8000, 'min_quantity' => 20, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Vinyl', 'price' => 0],
                    ['name' => 'Sticker HVS', 'price' => -2000],
                    ['name' => 'Transparent', 'price' => 2000],
                    ['name' => 'Hologram', 'price' => 5000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Laminating doff', 'price' => 2000],
                    ['name' => 'Laminating gloss', 'price' => 2000],
                    ['name' => 'Tanpa laminating', 'price' => 0],
                ]],
                ['name' => 'Bentuk', 'required' => false, 'options' => [
                    ['name' => 'Potong kontur', 'price' => 3000],
                    ['name' => 'Round corner', 'price' => 1000],
                    ['name' => 'Per lembar (tanpa potong)', 'price' => 0],
                ]],
            ])],
            ['category' => 'lain-lain', 'name' => 'Kipas Promosi', 'description' => 'Kipas promosi custom untuk acara dan doorprize. Pilih bentuk dan pegangan.', 'unit' => 'pcs', 'price' => 12000, 'min_quantity' => 50, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Bentuk', 'required' => true, 'options' => [
                    ['name' => 'Bulat', 'price' => 0],
                    ['name' => 'Kartu', 'price' => -1000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Pegangan plastik', 'price' => 0],
                    ['name' => 'Pegangan kayu', 'price' => 2000],
                ]],
            ])],
            ['category' => 'lain-lain', 'name' => 'Kalender', 'description' => 'Cetak kalender dinding dan meja custom. Pilih jenis, ukuran, dan jumlah lembar.', 'unit' => 'pcs', 'price' => 45000, 'min_quantity' => 10, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Jenis', 'required' => true, 'options' => [
                    ['name' => 'Dinding', 'price' => 0],
                    ['name' => 'Meja', 'price' => -20000],
                ]],
                ['name' => 'Ukuran', 'required' => true, 'options' => [
                    ['name' => 'A3', 'price' => 0],
                    ['name' => 'A4', 'price' => -5000],
                ]],
                ['name' => 'Jumlah lembar', 'required' => false, 'options' => [
                    ['name' => '13 lembar', 'price' => 0],
                    ['name' => '6 lembar', 'price' => -8000],
                ]],
            ])],
            ['category' => 'lain-lain', 'name' => 'Paper Bag', 'description' => 'Paper bag custom untuk packaging dan gift. Pilih bahan, ukuran, dan tali.', 'unit' => 'pcs', 'price' => 6500, 'min_quantity' => 50, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Kraft coklat', 'price' => 0],
                    ['name' => 'Samson', 'price' => 1000],
                    ['name' => 'Art carton 260 gsm', 'price' => 3000],
                ]],
                ['name' => 'Ukuran', 'required' => false, 'options' => [
                    ['name' => 'Small', 'price' => 0],
                    ['name' => 'Medium', 'price' => 1500],
                    ['name' => 'Large', 'price' => 3000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Tali kraft', 'price' => 0],
                    ['name' => 'Tali rope', 'price' => 1000],
                    ['name' => 'Tali pita', 'price' => 2000],
                ]],
            ])],
            ['category' => 'lain-lain', 'name' => 'Gantungan Pintu', 'description' => 'Gantungan pintu custom untuk hotel, kamar, dan promo. Pilih bahan, ukuran, dan aksesori.', 'unit' => 'pcs', 'price' => 25000, 'min_quantity' => 10, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Produk & Bahan', 'required' => true, 'options' => [
                    ['name' => 'Akrilik 3 mm', 'price' => 0],
                    ['name' => 'Kayu 5 mm', 'price' => 5000],
                    ['name' => 'PVC', 'price' => -3000],
                ]],
                ['name' => 'Ukuran', 'required' => false, 'options' => [
                    ['name' => 'Standar 20 x 8 cm', 'price' => 0],
                    ['name' => 'Besar 25 x 10 cm', 'price' => 5000],
                ]],
                ['name' => 'Aksesori', 'required' => false, 'options' => [
                    ['name' => 'Tanpa double tape', 'price' => 0],
                    ['name' => 'Include double tape', 'price' => 500],
                ]],
            ])],
            ['category' => 'lain-lain', 'name' => 'Cetak Foto Kanvas', 'description' => 'Cetak foto di kanvas untuk dekorasi dan hadiah. Pilih ukuran, rangka, dan finishing.', 'unit' => 'pcs', 'price' => 125000, 'file_requirement' => Service::FILE_REQUIRED, 'option_groups' => $this->genericGroups([
                ['name' => 'Ukuran', 'required' => true, 'options' => [
                    ['name' => 'A3', 'price' => 0],
                    ['name' => 'A2', 'price' => 40000],
                    ['name' => 'Custom (maks 1 m²)', 'price' => 75000],
                ]],
                ['name' => 'Finishing', 'required' => false, 'options' => [
                    ['name' => 'Include rangka kayu', 'price' => 0],
                    ['name' => 'Tanpa rangka', 'price' => -20000],
                    ['name' => 'Laminating doff', 'price' => 10000],
                ]],
            ])],
        ];
    }

    /**
     * Retail items shown in the ATK home section (type = jual).
     *
     * @return list<array<string, mixed>>
     */
    private function jualServices(): array
    {
        return [
            ['name' => 'Pulpen', 'description' => 'Pulpen standar untuk kebutuhan tulis harian.', 'unit' => 'pcs', 'price' => 3000, 'file_requirement' => Service::FILE_NONE],
            ['name' => 'Buku tulis', 'description' => 'Buku tulis bergaris ukuran standar.', 'unit' => 'pcs', 'price' => 8000, 'file_requirement' => Service::FILE_NONE],
            ['name' => 'Map folder', 'description' => 'Map folder untuk menyusun dokumen dan tugas.', 'unit' => 'pcs', 'price' => 5000, 'file_requirement' => Service::FILE_NONE],
            ['name' => 'Kertas A4 (rim)', 'description' => 'Satu rim kertas A4 70gsm untuk print dan fotokopi.', 'unit' => 'rim', 'price' => 48000, 'file_requirement' => Service::FILE_NONE],
            ['name' => 'Spidol', 'description' => 'Spidol permanen untuk label dan presentasi.', 'unit' => 'pcs', 'price' => 7000, 'file_requirement' => Service::FILE_NONE],
        ];
    }

    /**
     * Option groups for Print A0–A5.
     *
     * @return list<array<string, mixed>>
     */
    private function printGroups(float $factor): array
    {
        $money = static fn (float $value): int => max(0, (int) round($value));

        return [
            [
                'name' => 'Produk & Bahan',
                'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                'is_required' => true,
                'options' => [
                    ['name' => 'HVS 70 gsm', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'HVS 80 gsm', 'price' => $money(80 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Art Paper 120 gsm', 'price' => $money(400 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Art Paper 150 gsm', 'price' => $money(600 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Art Carton 260 gsm', 'price' => $money(1500 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Matte paper', 'price' => $money(350 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Photo paper', 'price' => $money(500 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Blueprint', 'price' => $money(300 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                ],
            ],
            [
                'name' => 'Cetak Berapa Sisi',
                'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                'is_required' => true,
                'options' => [
                    ['name' => 'Satu sisi', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Dua sisi', 'price' => $money(150 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                ],
            ],
            [
                'name' => 'Laminating & Jilid',
                'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                'is_required' => false,
                'options' => [
                    ['name' => 'Tidak pakai', 'price' => 0, 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Laminating full doff', 'price' => $money(3000 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Laminating full gloss', 'price' => $money(3000 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Laminating full clear', 'price' => $money(3000 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Laminating depan', 'price' => $money(1600 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Laminating belakang', 'price' => $money(1600 * $factor), 'pricing' => ServiceOption::PRICING_PER_UNIT],
                    ['name' => 'Jilid spiral', 'price' => 5000, 'pricing' => ServiceOption::PRICING_PER_ORDER],
                ],
            ],
        ];
    }

    /**
     * Single "Produk & Bahan" model choices for Cetak Buku A5/A4/A3.
     *
     * @return list<array<string, mixed>>
     */
    private function bookModelGroups(string $size): array
    {
        $models = match ($size) {
            'A5' => [
                ['name' => 'Jilid spiral HVS 80 gsm', 'price' => 0],
                ['name' => 'Jilid spiral HVS 100 gsm', 'price' => 3000],
                ['name' => 'Softcover art paper 120 gsm', 'price' => 8000],
                ['name' => 'Softcover art paper 150 gsm', 'price' => 12000],
                ['name' => 'Hardcover art carton 260 gsm', 'price' => 25000],
            ],
            'A4' => [
                ['name' => 'Jilid spiral HVS 80 gsm', 'price' => 0],
                ['name' => 'Jilid spiral HVS 100 gsm', 'price' => 5000],
                ['name' => 'Softcover art paper 120 gsm', 'price' => 15000],
                ['name' => 'Softcover art paper 150 gsm', 'price' => 22000],
                ['name' => 'Hardcover art carton 260 gsm', 'price' => 45000],
            ],
            default => [
                ['name' => 'Jilid spiral HVS 80 gsm', 'price' => 0],
                ['name' => 'Softcover art paper 120 gsm', 'price' => 20000],
                ['name' => 'Softcover art paper 150 gsm', 'price' => 30000],
                ['name' => 'Hardcover art carton 260 gsm', 'price' => 60000],
            ],
        };

        return [
            [
                'name' => 'Produk & Bahan',
                'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                'is_required' => true,
                'options' => array_map(static fn (array $model) => [
                    'name' => $model['name'],
                    'price' => $model['price'],
                    'pricing' => ServiceOption::PRICING_PER_UNIT,
                ], $models),
            ],
        ];
    }

    /**
     * Normalize simplified group definitions into full group payloads.
     *
     * @param  list<array{name: string, required: bool, options: list<array{name: string, price: int|float}>}>  $groups
     * @return list<array<string, mixed>>
     */
    private function genericGroups(array $groups): array
    {
        return array_map(static function (array $group) {
            $isLaminating = str_contains($group['name'], 'Laminating') || str_contains($group['name'], 'Finishing') || str_contains($group['name'], 'Cetak berapa sisi');

            return [
                'name' => $group['name'],
                'selection_type' => ServiceOptionGroup::SELECTION_SINGLE,
                'is_required' => (bool) $group['required'],
                'options' => array_map(static fn (array $option) => [
                    'name' => $option['name'],
                    'price' => $option['price'],
                    'pricing' => $isLaminating && str_contains($option['name'], 'spiral')
                        ? ServiceOption::PRICING_PER_ORDER
                        : ServiceOption::PRICING_PER_UNIT,
                ], $group['options']),
            ];
        }, $groups);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function createService(array $row, ?int $categoryId, string $type): void
    {
        $groups = $row['option_groups'] ?? [];
        $tiers = $row['price_tiers'] ?? null;
        $fileRequirement = $row['file_requirement'] ?? Service::FILE_NONE;
        $categorySlug = (string) ($row['category'] ?? 'lain-lain');
        unset($row['option_groups'], $row['price_tiers'], $row['file_requirement'], $row['category']);

        $service = Service::create([
            ...$row,
            'slug' => Str::slug($row['name']),
            'category_id' => $categoryId,
            'type' => $type,
            'is_active' => true,
            'file_requirement' => $fileRequirement,
            'image_url' => $type === Service::TYPE_JUAL
                ? 'images/services/atk.svg'
                : 'images/services/'.$categorySlug.'.svg',
        ]);

        foreach ($groups as $groupIndex => $group) {
            $groupModel = ServiceOptionGroup::create([
                'service_id' => $service->id,
                'name' => $group['name'],
                'selection_type' => $group['selection_type'] ?? ServiceOptionGroup::SELECTION_SINGLE,
                'is_required' => (bool) ($group['is_required'] ?? false),
                'sort_order' => $groupIndex,
                'is_active' => true,
            ]);

            foreach ($group['options'] ?? [] as $optionIndex => $option) {
                ServiceOption::create([
                    'service_id' => $service->id,
                    'group_id' => $groupModel->id,
                    'name' => $option['name'],
                    'price' => $option['price'],
                    'pricing' => $option['pricing'] ?? ServiceOption::PRICING_PER_UNIT,
                    'is_active' => true,
                    'sort_order' => $optionIndex,
                ]);
            }
        }

        if ($type === Service::TYPE_JASA) {
            $tiers ??= $this->tiersFor((float) $service->price);

            foreach ($tiers as $index => $tier) {
                ServicePriceTier::create([
                    'service_id' => $service->id,
                    'min_qty' => $tier['min_qty'],
                    'max_qty' => $tier['max_qty'],
                    'unit_price' => $tier['unit_price'],
                    'sort_order' => $index,
                ]);
            }
        }
    }

    /**
     * Default ladder: 1–10, 11–50, 51–100, 101+ with decreasing unit price.
     *
     * @return list<array{min_qty: int, max_qty: int|null, unit_price: int}>
     */
    private function tiersFor(float $base): array
    {
        $base = max(1, (int) round($base));

        return [
            ['min_qty' => 1, 'max_qty' => 10, 'unit_price' => $base],
            ['min_qty' => 11, 'max_qty' => 50, 'unit_price' => (int) round($base * 0.8)],
            ['min_qty' => 51, 'max_qty' => 100, 'unit_price' => (int) round($base * 0.7)],
            ['min_qty' => 101, 'max_qty' => null, 'unit_price' => (int) round($base * 0.6)],
        ];
    }
}
