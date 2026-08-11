@if($q)
    <div class="text-sm text-[var(--muted)]">
        Menampilkan hasil untuk: <span class="font-semibold text-[var(--primary-dark)]">"{{ $q }}"</span>
    </div>
@endif
@if($kota)
    <div class="text-sm text-[var(--muted)]">
        Kota: <span class="font-semibold text-[var(--primary-dark)]">{{ $kota }}</span>
    </div>
@endif
