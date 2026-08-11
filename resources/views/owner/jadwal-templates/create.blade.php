<x-owner-layout title="Tambah Template Jadwal">
    @include('owner.jadwal-templates.partials.form', [
        'action' => route('owner.jadwals.templates.store'),
        'method' => 'POST',
        'template' => null,
        'submitLabel' => 'Simpan Template',
    ])
</x-owner-layout>
