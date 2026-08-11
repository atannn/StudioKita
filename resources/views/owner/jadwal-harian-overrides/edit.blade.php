<x-owner-layout title="Edit Jadwal Harian">
    @include('owner.jadwal-harian-overrides.partials.form', [
        'action' => route('owner.jadwals.harian.update', $override->id),
        'method' => 'PUT',
        'override' => $override,
        'submitLabel' => 'Update Pengaturan Harian',
    ])
</x-owner-layout>
