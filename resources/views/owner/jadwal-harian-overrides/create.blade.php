<x-owner-layout title="Atur Jadwal Harian">
    @include('owner.jadwal-harian-overrides.partials.form', [
        'action' => route('owner.jadwals.harian.store'),
        'method' => 'POST',
        'override' => null,
        'submitLabel' => 'Simpan Pengaturan Harian',
    ])
</x-owner-layout>
