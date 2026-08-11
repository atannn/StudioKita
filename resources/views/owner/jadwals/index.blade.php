<x-owner-layout title="Manajemen Jadwal">
    <x-slot name="actions">
        <a href="{{ route('owner.jadwals.templates.index') }}"
           class="px-4 py-2 border border-indigo-200 text-indigo-700 bg-white rounded">
            Template Jadwal
        </a>
        <a href="{{ route('owner.jadwals.harian.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded">
            Atur Jadwal Harian
        </a>
    </x-slot>

    @php
        $calendarEvents = $jadwalsAll->map(function ($jadwal) {
            $serviceName = $jadwal->service?->nama_service
                ?? ($jadwal->source_type === 'override' ? 'Override Harian' : 'Service belum dipetakan');

            return [
                'id' => $jadwal->idJadwal,
                'title' => $serviceName.' | '.($jadwal->room?->nama_ruangan ?? 'Ruangan').' | '.substr($jadwal->waktu_mulai, 0, 5),
                'start' => $jadwal->tanggal.'T'.$jadwal->waktu_mulai,
                'end' => $jadwal->tanggal.'T'.$jadwal->waktu_selesai,
                'extendedProps' => [
                    'status' => $jadwal->status,
                    'room_id' => $jadwal->rooms_idrooms,
                    'room_name' => $jadwal->room?->nama_ruangan ?? '-',
                    'room_type' => $jadwal->room?->tipe_ruangan ?? '-',
                    'service_name' => $serviceName,
                    'source_type' => $jadwal->source_type,
                    'tanggal' => $jadwal->tanggal,
                    'waktu_mulai' => substr($jadwal->waktu_mulai, 0, 5),
                    'waktu_selesai' => substr($jadwal->waktu_selesai, 0, 5),
                ],
            ];
        })->values();
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('jadwal'))
            <div class="p-3 bg-red-100 text-red-800 rounded">
                {{ $errors->first('jadwal') }}
            </div>
        @endif

        <div class="space-y-3">
            <div class="text-base font-semibold text-gray-800">Kalender Jadwal</div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-end gap-4 justify-between">
                        <div>
                            <label class="text-xs font-semibold text-gray-600">Filter ruangan</label>
                            <select id="rekamanRoomFilter"
                                    class="mt-2 px-3 py-2 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm">
                                <option value="all">Semua ruangan</option>
                                @foreach ($roomsAll as $room)
                                    <option value="{{ $room->idrooms }}">{{ $room->nama_ruangan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-xs text-gray-500">
                            Klik jadwal untuk lihat detail
                        </div>
                    </div>

                    <div id="rekamanCalendar" class="mt-6"></div>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="text-base font-semibold text-gray-800">Ruangan Latihan</div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="w-full border border-gray-200 dark:border-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/40">
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Service</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Room</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Tanggal</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Waktu</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Status</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Sumber</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2 w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jadwalsLatihan as $jadwal)
                                @php
                                    $serviceLabel = $jadwal->service?->nama_service ?? ($jadwal->source_type === 'override' ? 'Override Harian' : 'Belum dipetakan');
                                    $sourceLabel = match ($jadwal->source_type) {
                                        'template' => 'Template',
                                        'override' => 'Harian',
                                        default => 'Legacy Manual',
                                    };
                                @endphp
                                <tr>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $serviceLabel }}</td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $jadwal->room?->nama_ruangan ?? '-' }}</td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $jadwal->tanggal }}</td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">
                                        {{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }}
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">
                                        @if ($jadwal->status === 'available')
                                            Tersedia
                                        @elseif ($jadwal->status === 'booked')
                                            Dibooking
                                        @else
                                            Diblokir
                                        @endif
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $sourceLabel }}</td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">
                                        @if ($jadwal->source_type === 'template' && $jadwal->schedule_template_id)
                                            <a class="text-blue-600 underline"
                                               href="{{ route('owner.jadwals.templates.edit', $jadwal->schedule_template_id) }}">
                                                Edit template
                                            </a>
                                        @elseif ($jadwal->source_type === 'override' && $jadwal->schedule_date_harian_override_id)
                                            <a class="text-blue-600 underline"
                                               href="{{ route('owner.jadwals.harian.edit', $jadwal->schedule_date_harian_override_id) }}">
                                                Edit harian
                                            </a>

                                            <form class="inline"
                                                  method="POST"
                                                  action="{{ route('owner.jadwals.harian.destroy', $jadwal->schedule_date_harian_override_id) }}"
                                                  onsubmit="return confirm('Hapus pengaturan harian ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 underline ml-3" type="submit">
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <a class="text-blue-600 underline"
                                               href="{{ route('owner.jadwals.edit', $jadwal->idJadwal) }}">
                                                Edit legacy
                                            </a>

                                            <form class="inline"
                                                  method="POST"
                                                  action="{{ route('owner.jadwals.destroy', $jadwal->idJadwal) }}"
                                                  onsubmit="return confirm('Hapus jadwal legacy ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 underline ml-3" type="submit">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="border p-2 text-center" colspan="7">
                                        Belum ada jadwal latihan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($jadwalsLatihan->hasPages())
                        <div class="mt-4">
                            {{ $jadwalsLatihan->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="text-base font-semibold text-gray-800">Ruangan Rekaman</div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="w-full border border-gray-200 dark:border-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/40">
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Service</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Room</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Tanggal</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Waktu</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Status</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2">Sumber</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2 w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jadwalsRekaman as $jadwal)
                                @php
                                    $serviceLabel = $jadwal->service?->nama_service ?? ($jadwal->source_type === 'override' ? 'Override Harian' : 'Belum dipetakan');
                                    $sourceLabel = match ($jadwal->source_type) {
                                        'template' => 'Template',
                                        'override' => 'Harian',
                                        default => 'Legacy Manual',
                                    };
                                @endphp
                                <tr>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $serviceLabel }}</td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $jadwal->room?->nama_ruangan ?? '-' }}</td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $jadwal->tanggal }}</td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">
                                        {{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }}
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">
                                        @if ($jadwal->status === 'available')
                                            Tersedia
                                        @elseif ($jadwal->status === 'booked')
                                            Dibooking
                                        @else
                                            Diblokir
                                        @endif
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">{{ $sourceLabel }}</td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2">
                                        @if ($jadwal->source_type === 'template' && $jadwal->schedule_template_id)
                                            <a class="text-blue-600 underline"
                                               href="{{ route('owner.jadwals.templates.edit', $jadwal->schedule_template_id) }}">
                                                Edit template
                                            </a>
                                        @elseif ($jadwal->source_type === 'override' && $jadwal->schedule_date_harian_override_id)
                                            <a class="text-blue-600 underline"
                                               href="{{ route('owner.jadwals.harian.edit', $jadwal->schedule_date_harian_override_id) }}">
                                                Edit harian
                                            </a>

                                            <form class="inline"
                                                  method="POST"
                                                  action="{{ route('owner.jadwals.harian.destroy', $jadwal->schedule_date_harian_override_id) }}"
                                                  onsubmit="return confirm('Hapus pengaturan harian ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 underline ml-3" type="submit">
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <a class="text-blue-600 underline"
                                               href="{{ route('owner.jadwals.edit', $jadwal->idJadwal) }}">
                                                Edit legacy
                                            </a>

                                            <form class="inline"
                                                  method="POST"
                                                  action="{{ route('owner.jadwals.destroy', $jadwal->idJadwal) }}"
                                                  onsubmit="return confirm('Hapus jadwal legacy ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 underline ml-3" type="submit">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="border p-2 text-center" colspan="7">
                                        Belum ada jadwal rekaman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($jadwalsRekaman->hasPages())
                        <div class="mt-4">
                            {{ $jadwalsRekaman->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="jadwalModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-lg">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs uppercase tracking-widest text-gray-500">Detail Jadwal</div>
                    <div id="modalTitle" class="text-lg font-semibold text-gray-900 mt-1"></div>
                </div>
                <button type="button" id="closeJadwalModal" class="text-gray-500 hover:text-gray-700">
                    x
                </button>
            </div>
            <div class="mt-4 space-y-2 text-sm text-gray-600">
                <div><span class="font-semibold text-gray-800">Service:</span> <span id="modalService"></span></div>
                <div><span class="font-semibold text-gray-800">Tanggal:</span> <span id="modalTanggal"></span></div>
                <div><span class="font-semibold text-gray-800">Waktu:</span> <span id="modalWaktu"></span></div>
                <div><span class="font-semibold text-gray-800">Ruangan:</span> <span id="modalRoom"></span></div>
                <div><span class="font-semibold text-gray-800">Tipe:</span> <span id="modalRoomType"></span></div>
                <div><span class="font-semibold text-gray-800">Status:</span> <span id="modalStatus"></span></div>
            </div>
            <div class="mt-5 text-right">
                <button type="button" id="closeJadwalModal2" class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <style>
        .fc .fc-toolbar {
            gap: 12px;
        }
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
        }
        .fc .fc-button {
            border-radius: 999px !important;
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            color: #374151 !important;
            text-transform: capitalize;
            padding: 0.45rem 0.9rem;
            box-shadow: none !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: #e0f2f1 !important;
            border-color: #5eead4 !important;
            color: #0f766e !important;
        }
        .fc .fc-button:hover {
            background: #f9fafb !important;
        }
        .fc .fc-daygrid-day-number {
            color: #6b7280;
            font-size: 0.85rem;
        }
        .fc .fc-col-header-cell-cushion {
            color: #111827;
            font-weight: 600;
            padding: 8px 0;
        }
        .fc .fc-daygrid-event {
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.75rem;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const calendarEl = document.getElementById('rekamanCalendar');
            const roomFilter = document.getElementById('rekamanRoomFilter');
            const modal = document.getElementById('jadwalModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalService = document.getElementById('modalService');
            const modalTanggal = document.getElementById('modalTanggal');
            const modalWaktu = document.getElementById('modalWaktu');
            const modalRoom = document.getElementById('modalRoom');
            const modalRoomType = document.getElementById('modalRoomType');
            const modalStatus = document.getElementById('modalStatus');
            const closeModalButtons = [
                document.getElementById('closeJadwalModal'),
                document.getElementById('closeJadwalModal2'),
            ];
            const rawEvents = @json($calendarEvents);

            const statusColors = {
                available: '#22c55e',
                booked: '#6366f1',
                blocked: '#f97316',
            };

            const renderEvents = (roomId = 'all') => {
                calendar.removeAllEvents();

                rawEvents
                    .filter((event) => roomId === 'all' || String(event.extendedProps.room_id) === String(roomId))
                    .forEach((event) => {
                        calendar.addEvent({
                            ...event,
                            backgroundColor: statusColors[event.extendedProps.status] || '#94a3b8',
                            borderColor: statusColors[event.extendedProps.status] || '#94a3b8',
                        });
                    });
            };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                nowIndicator: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay',
                },
                eventClick: function(info) {
                    const event = info.event;
                    const props = event.extendedProps || {};
                    modalTitle.textContent = event.title || 'Jadwal';
                    modalService.textContent = props.service_name || '-';
                    modalTanggal.textContent = props.tanggal || '-';
                    modalWaktu.textContent = `${props.waktu_mulai || ''} - ${props.waktu_selesai || ''}`;
                    modalRoom.textContent = props.room_name || '-';
                    modalRoomType.textContent = props.room_type || '-';
                    modalStatus.textContent = props.status || '-';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                },
            });

            calendar.render();
            renderEvents();

            roomFilter?.addEventListener('change', (event) => {
                renderEvents(event.target.value);
            });

            closeModalButtons.forEach((button) => {
                button?.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            });
        });
    </script>
</x-owner-layout>
