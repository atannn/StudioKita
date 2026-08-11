<?php

namespace Database\Seeders;

use App\Models\Photo;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudioPhotosSeeder extends Seeder
{
    public function run(): void
    {
        $logoSource = public_path('assets/logo/transparant/hitam.png');
        $facilitySource = public_path('assets/picture/download.jpg');

        if (!file_exists($logoSource) || !file_exists($facilitySource)) {
            $this->command?->warn('Placeholder file tidak ditemukan di public/assets.');
            return;
        }

        $tenants = Tenant::with('photos')->get();

        foreach ($tenants as $tenant) {
            $photos = $tenant->photos;

            if ($photos->where('is_primary', true)->isEmpty()) {
                $logoPath = $this->storePlaceholder($logoSource, $tenant->slug, 'logo');
                Photo::create([
                    'foto_path' => $logoPath,
                    'caption' => 'Logo Studio',
                    'is_primary' => true,
                    'uploaded_at' => now(),
                    'status' => 1,
                    'tenants_idTenant' => $tenant->idTenant,
                ]);
            }

            $galleryCount = $photos->where('is_primary', false)->count();
            $toAdd = max(0, 3 - $galleryCount);

            for ($i = 0; $i < $toAdd; $i++) {
                $facilityPath = $this->storePlaceholder($facilitySource, $tenant->slug, 'facility');
                Photo::create([
                    'foto_path' => $facilityPath,
                    'caption' => 'Foto ruangan & fasilitas',
                    'is_primary' => false,
                    'uploaded_at' => now(),
                    'status' => 1,
                    'tenants_idTenant' => $tenant->idTenant,
                ]);
            }
        }
    }

    private function storePlaceholder(string $source, string $slug, string $prefix): string
    {
        $ext = pathinfo($source, PATHINFO_EXTENSION) ?: 'jpg';
        $filename = $prefix.'-'.$slug.'-'.Str::uuid().'.'.$ext;
        $path = 'tenants/'.$filename;

        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }
}
