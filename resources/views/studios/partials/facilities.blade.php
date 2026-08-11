<div class="sk-label">Fasilitas</div>
<h3 class="sk-title text-xl font-semibold mt-1">Fasilitas & alat.</h3>
<div class="mt-3 overflow-x-auto">
    <table class="sk-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($facilities as $f)
                <tr>
                    <td>{{ $f->nama_fasilitas }}</td>
                    <td class="text-[var(--muted)]">{{ $f->deskripsi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-[var(--muted)]">Belum ada fasilitas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $facilities->links() }}
</div>
