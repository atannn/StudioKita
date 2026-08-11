<div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($studios as $s)
        @php
            $photoPath = $s->primaryPhoto?->foto_path;
            $imgUrl = $photoPath ? asset('storage/'.$photoPath) : null;
            $minPrice = $s->services
                ?->map(fn ($service) => min((float) ($service->weekday_price ?? 0), (float) ($service->weekend_price ?? 0)))
                ?->min();
            $priceLabel = $minPrice !== null ? 'Rp ' . number_format($minPrice, 0, ',', '.') : 'Rp -';
            $location = collect([$s->kecamatan, $s->kota, $s->provinsi])->filter()->implode(', ');
        @endphp

        <a href="{{ route('studios.show', $s->slug) }}"
           class="bg-white rounded-3xl border border-[var(--line)] shadow-sm hover:shadow-xl transition hover:-translate-y-1 overflow-hidden">
            <div class="relative h-48 bg-[#f3f4f6] overflow-hidden">
                @if($imgUrl)
                    <img src="{{ $imgUrl }}" class="w-full h-full object-cover" alt="{{ $s->nama }}">
                @else
                    <div class="w-full h-full flex items-center justify-center text-sm text-[var(--muted)]">
                        Foto belum tersedia
                    </div>
                @endif
                <span class="absolute top-4 left-4 sk-pill">Studio aktif</span>
            </div>

            <div class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="sk-title text-lg font-semibold">{{ $s->nama }}</div>
                        <div class="text-sm text-[var(--muted)] mt-1">{{ $location ?: ($s->alamat ?? '-') }}</div>
                    </div>
                    <div class="text-xs font-semibold text-[var(--primary-dark)] bg-[rgba(15,118,110,0.12)] px-2 py-1 rounded-full">
                        Detail
                    </div>
                </div>
                <div class="text-sm text-[var(--muted)] mt-3">
                    {{ \Illuminate\Support\Str::limit($s->deskripsi ?? 'Studio ini siap digunakan untuk latihan maupun rekaman.', 90) }}
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-[var(--muted)]">Mulai dari</div>
                        <div class="text-base font-semibold text-[var(--primary-dark)]">{{ $priceLabel }}</div>
                        
                    </div>
                    <span class="sk-pill">Lihat detail</span>
                </div>
            </div>
        </a>
    @empty
        <div class="col-span-3 bg-white p-6 rounded-2xl shadow-sm text-[var(--muted)]">
            Belum ada studio yang tersedia.
        </div>
    @endforelse
</div>
