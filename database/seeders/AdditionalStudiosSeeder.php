<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Room;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdditionalStudiosSeeder extends Seeder
{
    public function run(): void
    {
        $lorem = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Vivamus suscipit tortor eget felis porttitor volutpat.";

        $tenantsData = [
            [
                'nama' => 'Amplify Surabaya',
                'slug' => 'amplify-surabaya',
                'nama_pemilik' => 'Raka Santoso',
                'email' => 'raka@amplify-surabaya.test',
                'no_telp' => '081234500101',
                'alamat' => 'Jl. Raya Darmo 101',
                'kecamatan' => 'Tegalsari',
                'kota' => 'Surabaya',
                'provinsi' => 'Jawa Timur',
            ],
            [
                'nama' => 'Harmony Lab Surabaya',
                'slug' => 'harmony-lab-surabaya',
                'nama_pemilik' => 'Dewi Kartika',
                'email' => 'dewi@harmonylab.test',
                'no_telp' => '081234500102',
                'alamat' => 'Jl. Manyar Kertoarjo 22',
                'kecamatan' => 'Gubeng',
                'kota' => 'Surabaya',
                'provinsi' => 'Jawa Timur',
            ],
            [
                'nama' => 'Ruang Nada Jakarta',
                'slug' => 'ruang-nada-jakarta',
                'nama_pemilik' => 'Fajar Pratama',
                'email' => 'fajar@ruangnada.test',
                'no_telp' => '081234500201',
                'alamat' => 'Jl. Tebet Raya 15',
                'kecamatan' => 'Tebet',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
            ],
            [
                'nama' => 'City Groove Studio',
                'slug' => 'city-groove-jakarta',
                'nama_pemilik' => 'Alya Putri',
                'email' => 'alya@citygroove.test',
                'no_telp' => '081234500202',
                'alamat' => 'Jl. Kemang Utara 5',
                'kecamatan' => 'Mampang Prapatan',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
            ],
            [
                'nama' => 'Metro Rhythm House',
                'slug' => 'metro-rhythm-jakarta',
                'nama_pemilik' => 'Ardiansyah',
                'email' => 'ardi@metrorhythm.test',
                'no_telp' => '081234500203',
                'alamat' => 'Jl. Gajah Mada 88',
                'kecamatan' => 'Gambir',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
            ],
            [
                'nama' => 'Braga Soundroom',
                'slug' => 'braga-soundroom',
                'nama_pemilik' => 'Nisa Rahman',
                'email' => 'nisa@bragasoundroom.test',
                'no_telp' => '081234500301',
                'alamat' => 'Jl. Braga 45',
                'kecamatan' => 'Sumur Bandung',
                'kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
            ],
            [
                'nama' => 'Lembang Tone Studio',
                'slug' => 'lembang-tone',
                'nama_pemilik' => 'Rizki Mahendra',
                'email' => 'rizki@lembangt.test',
                'no_telp' => '081234500302',
                'alamat' => 'Jl. Setiabudi 120',
                'kecamatan' => 'Coblong',
                'kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
            ],
            [
                'nama' => 'Medan Sound District',
                'slug' => 'medan-sound-district',
                'nama_pemilik' => 'Sari Simanjuntak',
                'email' => 'sari@medansound.test',
                'no_telp' => '081234500401',
                'alamat' => 'Jl. Gatot Subroto 55',
                'kecamatan' => 'Medan Petisah',
                'kota' => 'Medan',
                'provinsi' => 'Sumatera Utara',
            ],
            [
                'nama' => 'Semarang Groove Hub',
                'slug' => 'semarang-groove-hub',
                'nama_pemilik' => 'Bima Nugroho',
                'email' => 'bima@semaranggroove.test',
                'no_telp' => '081234500501',
                'alamat' => 'Jl. Pandanaran 10',
                'kecamatan' => 'Semarang Tengah',
                'kota' => 'Semarang',
                'provinsi' => 'Jawa Tengah',
            ],
        ];

        $baseRooms = [
            [
                'nama_ruangan' => 'Ruang Latihan A',
                'deskripsi' => 'buat latihan band',
                'kapasitas' => 8,
                'tipe_ruangan' => 'latihan',
            ],
            [
                'nama_ruangan' => 'Ruang Latihan B',
                'deskripsi' => 'buat latihan band',
                'kapasitas' => 8,
                'tipe_ruangan' => 'latihan',
            ],
            [
                'nama_ruangan' => 'Ruang Rekaman A',
                'deskripsi' => 'rekording',
                'kapasitas' => 6,
                'tipe_ruangan' => 'rekaman',
            ],
            [
                'nama_ruangan' => 'Ruang Rekaman B',
                'deskripsi' => 'rekording',
                'kapasitas' => 6,
                'tipe_ruangan' => 'rekaman',
            ],
        ];

        $serviceTemplates = Service::query()
            ->select('nama_service', 'tipe_service', 'durasi_menit', 'weekday_price', 'weekend_price', 'deskripsi', 'status')
            ->orderBy('idservice')
            ->get()
            ->map(fn ($service) => $service->toArray());

        if ($serviceTemplates->isEmpty()) {
            $serviceTemplates = collect([
                [
                    'nama_service' => 'Rekording Lagu',
                    'tipe_service' => 'rekaman',
                    'durasi_menit' => 240,
                    'weekday_price' => 5000000,
                    'weekend_price' => 5500000,
                    'deskripsi' => 'rekording full lagu dengan alat musik',
                    'status' => 1,
                ],
                [
                    'nama_service' => 'Rekording jinggle',
                    'tipe_service' => 'rekaman',
                    'durasi_menit' => 240,
                    'weekday_price' => 3000000,
                    'weekend_price' => 3300000,
                    'deskripsi' => 'rekording jinggle alat musik',
                    'status' => 1,
                ],
                [
                    'nama_service' => 'Latihan',
                    'tipe_service' => 'latihan',
                    'durasi_menit' => 120,
                    'weekday_price' => 90000,
                    'weekend_price' => 100000,
                    'deskripsi' => 'buat latihan band',
                    'status' => 1,
                ],
            ]);
        }

        $facilityTemplates = Facility::query()
            ->select('nama_fasilitas', 'deskripsi', 'status')
            ->orderBy('idfasiltas')
            ->get()
            ->map(fn ($facility) => $facility->toArray());

        if ($facilityTemplates->isEmpty()) {
            $facilityTemplates = collect([
                ['nama_fasilitas' => 'Gitar', 'deskripsi' => "Squier Affinity Telecaster '22", 'status' => 1],
                ['nama_fasilitas' => 'Bass', 'deskripsi' => "Cort Curbow 5 '07 Made in Korea", 'status' => 1],
                ['nama_fasilitas' => 'Keyboard', 'deskripsi' => 'Yamaha CK 61', 'status' => 1],
                ['nama_fasilitas' => 'Drum', 'deskripsi' => 'TAMA WBS42BNMS-NMF Starclassic Walnut/Birch', 'status' => 1],
                ['nama_fasilitas' => 'Microphone', 'deskripsi' => 'Mic Wireless Shure BLX', 'status' => 1],
                ['nama_fasilitas' => 'DJ Controller', 'deskripsi' => 'AlphaTheta XDJ-AZ', 'status' => 1],
            ]);
        }

        $multipliers = [0.9, 1.05, 1.1, 0.95, 1.08, 1.12, 1.0, 1.15, 1.2];

        foreach ($tenantsData as $index => $data) {
            if (Tenant::where('slug', $data['slug'])->exists()) {
                $this->command?->warn("Skip {$data['slug']} (slug sudah ada).");
                continue;
            }

            if (Tenant::where('email', $data['email'])->exists()) {
                $this->command?->warn("Skip {$data['email']} (email tenant sudah ada).");
                continue;
            }

            if (User::where('email', $data['email'])->exists()) {
                $this->command?->warn("Skip {$data['email']} (email user sudah ada).");
                continue;
            }

            DB::transaction(function () use ($data, $lorem, $baseRooms, $serviceTemplates, $facilityTemplates, $multipliers, $index) {
                $tenant = Tenant::create([
                    'nama' => $data['nama'],
                    'slug' => $data['slug'],
                    'deskripsi' => $lorem,
                    'nama_pemilik' => $data['nama_pemilik'],
                    'email' => $data['email'],
                    'no_telp' => $data['no_telp'],
                    'alamat' => $data['alamat'],
                    'kecamatan' => $data['kecamatan'],
                    'kota' => $data['kota'],
                    'provinsi' => $data['provinsi'],
                    'status' => 'active',
                ]);

                $user = User::create([
                    'name' => $data['nama_pemilik'],
                    'email' => $data['email'],
                    'password' => Hash::make('password123'),
                    'role' => 'owner',
                    'status' => true,
                    'tenants_idTenant' => $tenant->idTenant,
                ]);

                $user->no_telp = $data['no_telp'];
                $user->save();

                foreach ($baseRooms as $room) {
                    Room::create(array_merge($room, [
                        'tenants_idTenant' => $tenant->idTenant,
                        'status' => 1,
                    ]));
                }

                $multiplier = $multipliers[$index] ?? 1;
                foreach ($serviceTemplates as $service) {
                    $baseWeekdayPrice = (float) ($service['weekday_price'] ?? 0);
                    $baseWeekendPrice = (float) ($service['weekend_price'] ?? $baseWeekdayPrice);

                    $weekdayPrice = round($baseWeekdayPrice * $multiplier, 2);
                    $weekendPrice = round($baseWeekendPrice * $multiplier, 2);

                    Service::create([
                        'nama_service' => $service['nama_service'],
                        'tipe_service' => $service['tipe_service'],
                        'durasi_menit' => $service['durasi_menit'],
                        'weekday_price' => $weekdayPrice,
                        'weekend_price' => $weekendPrice,
                        'deskripsi' => $service['deskripsi'] ?? null,
                        'status' => $service['status'] ?? 1,
                        'tenants_idTenant' => $tenant->idTenant,
                    ]);
                }

                foreach ($facilityTemplates as $facility) {
                    Facility::create([
                        'nama_fasilitas' => $facility['nama_fasilitas'],
                        'deskripsi' => $facility['deskripsi'] ?? null,
                        'status' => $facility['status'] ?? 1,
                        'tenants_idTenant' => $tenant->idTenant,
                    ]);
                }
            });
        }
    }
}
