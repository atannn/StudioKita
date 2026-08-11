<x-owner-layout title="Edit Template Jadwal">
    @include('owner.jadwal-templates.partials.form', [
        'action' => route('owner.jadwals.templates.update', $template->id),
        'method' => 'PUT',
        'template' => $template,
        'submitLabel' => 'Update Template',
    ])
</x-owner-layout>
