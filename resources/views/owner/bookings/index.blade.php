<x-owner-layout title="Booking">
    <x-slot name="actions">
        <form method="GET" class="flex items-center gap-2">
            <select name="status"
                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">Semua Status</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                <option value="no_show" @selected(request('status') === 'no_show')>No Show</option>
            </select>
            <button class="px-3 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                Filter
            </button>
        </form>
    </x-slot>

    <div class="space-y-4">
        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded dark:bg-green-900/30 dark:text-gray-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-3 bg-red-100 text-red-800 rounded dark:bg-red-900/30 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 dark:border-gray-700 text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/40">
                                <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Tanggal Booking</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Customer</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Service</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Room</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Slot</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Payment</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Status</th>
                                <th class="border border-gray-200 dark:border-gray-700 p-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                @php
                                    $latestPayment = $booking->payments->first();
                                    $statusTone = match ($booking->status) {
                                        'confirmed' => 'bg-emerald-100 text-emerald-700',
                                        'completed' => 'bg-slate-200 text-slate-700',
                                        'cancelled', 'no_show' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                    $paymentTone = match ($latestPayment?->status) {
                                        'success' => 'bg-emerald-100 text-emerald-700',
                                        'cancelled', 'failed', 'expired' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                    $isCashPendingBooking = $latestPayment?->method === 'Cash'
                                        && $latestPayment?->status !== 'success';
                                    $canMarkNoShow = $booking->status === 'confirmed' && $isCashPendingBooking;
                                    $canCompleteCash = $booking->status === 'confirmed' && $isCashPendingBooking;
                                    $canConfirmPendingBooking = $booking->status === 'pending';
                                @endphp
                                <tr>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2 align-top">
                                        {{ \Carbon\Carbon::parse($booking->tanggal_booking)->format('d M Y H:i') }}
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2 align-top">
                                        <div class="font-medium">{{ $booking->user?->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $booking->user?->email ?? '-' }}</div>
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2 align-top">
                                        <div class="font-medium">{{ $booking->service?->nama_service ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ ucfirst((string) ($booking->service?->tipe_service ?? '-')) }}
                                        </div>
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2 align-top">
                                        {{ $booking->room?->nama_ruangan ?? '-' }}
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2 align-top">
                                        @if($booking->jadwal)
                                            <div>{{ $booking->jadwal->tanggal }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ substr((string) $booking->jadwal->waktu_mulai, 0, 5) }}-{{ substr((string) $booking->jadwal->waktu_selesai, 0, 5) }}
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2 align-top">
                                        @if($latestPayment)
                                            <div class="font-medium">
                                                {{ $latestPayment->method }}
                                                - Rp {{ number_format($latestPayment->amount ?? 0, 0, ',', '.') }}
                                            </div>
                                            <div class="mt-1">
                                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $paymentTone }}">
                                                    {{ ucfirst((string) $latestPayment->status) }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                State booking: {{ $booking->payment_state ?? 'unpaid' }}
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2 align-top">
                                        <div class="mb-2">
                                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $statusTone }}">
                                                {{ str_replace('_', ' ', ucfirst((string) $booking->status)) }}
                                            </span>
                                        </div>
                                        <div class="font-medium">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2 align-top">
                                        <div class="flex flex-col items-start gap-2">
                                            @if($canConfirmPendingBooking)
                                                <form method="POST" action="{{ route('owner.bookings.confirm', $booking->idbooking) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="text-green-600 underline" type="submit"
                                                            onclick="return confirm('{{ $isCashPendingBooking ? 'Konfirmasi reservasi cash ini?' : 'Konfirmasi booking ini?' }}')">
                                                        Konfirmasi
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('owner.bookings.cancel', $booking->idbooking) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="text-red-600 underline" type="submit"
                                                            onclick="return confirm('Batalkan booking ini? Slot akan dibuka kembali.')">
                                                        Cancel
                                                    </button>
                                                </form>
                                            @elseif($booking->status === 'confirmed')
                                                <form method="POST" action="{{ route('owner.bookings.complete', $booking->idbooking) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="text-indigo-600 underline" type="submit"
                                                            onclick="return confirm('{{ $canCompleteCash ? 'Tandai booking selesai dan anggap pembayaran cash sudah diterima?' : 'Tandai booking completed?' }}')">
                                                        Completed
                                                    </button>
                                                </form>

                                                @if($canMarkNoShow)
                                                    <form method="POST" action="{{ route('owner.bookings.mark-no-show', $booking->idbooking) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="text-amber-600 underline" type="submit"
                                                                onclick="return confirm('Tandai customer tidak hadir? Slot akan dibuka kembali.')">
                                                            No Show
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST" action="{{ route('owner.bookings.cancel', $booking->idbooking) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="text-red-600 underline" type="submit"
                                                            onclick="return confirm('Batalkan booking ini? Slot akan dibuka kembali.')">
                                                        Cancel
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-500">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="border border-gray-200 dark:border-gray-700 p-2 text-center" colspan="8">
                                        Belum ada booking.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    </div>
</x-owner-layout>
