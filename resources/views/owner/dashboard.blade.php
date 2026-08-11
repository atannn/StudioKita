<x-owner-layout>
    @php
        $roomCount = $tenant?->rooms_count ?? 0;
        $serviceCount = $tenant?->services_count ?? 0;
        $facilityCount = $tenant?->facilities_count ?? 0;
        $verificationProgress = $verificationProgress ?? [
            'basic_verified' => false,
            'verified' => false,
            'payment_verified' => false,
            'completed' => 0,
            'total' => 3,
            'percentage' => 0,
        ];
        $basicVerified = (bool) ($verificationProgress['basic_verified'] ?? false);
        $verified = (bool) ($verificationProgress['verified'] ?? false);
        $paymentVerified = (bool) ($verificationProgress['payment_verified'] ?? false);
        $donut = (int) ($verificationProgress['percentage'] ?? 0);
        $verificationSegments = [
            ['complete' => $basicVerified, 'color' => '#6366f1', 'start' => 0, 'end' => 33.333],
            ['complete' => $verified, 'color' => '#22c55e', 'start' => 33.333, 'end' => 66.666],
            ['complete' => $paymentVerified, 'color' => '#fb923c', 'start' => 66.666, 'end' => 100],
        ];
        $verificationGradient = 'conic-gradient('.collect($verificationSegments)
            ->map(fn ($segment) => ($segment['complete'] ? $segment['color'] : '#e5e7eb').' '.$segment['start'].'% '.$segment['end'].'%')
            ->implode(', ').')';
        $metrics = $metrics ?? [
            'total' => 0,
            'active' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'revenue' => 0,
        ];
    @endphp

    <div class="flex flex-col gap-5 lg:flex-row">
        <div class="w-full lg:w-[64%] space-y-6">
            <div id="ownerAnalyticsCard">
                @include('owner.dashboard.partials.analytics')
            </div>

            <div class="overflow-hidden p-5 bg-white rounded-2xl">
                <div class="flex flex-col gap-4 w-full bg-gray-100 rounded-lg p-4">
                    <div class="flex flex-wrap gap-4 justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex w-12 h-12 items-center justify-center rounded-lg bg-white">
                                <div class="w-6 h-6 rounded-full bg-indigo-100"></div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-600">Total Ruangan</div>
                                <div class="text-base font-semibold text-gray-950">{{ $roomCount }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex w-12 h-12 items-center justify-center rounded-lg bg-white">
                                <div class="w-6 h-6 rounded-full bg-orange-100"></div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-600">Total Layanan</div>
                                <div class="text-base font-semibold text-gray-950">{{ $serviceCount }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex w-12 h-12 items-center justify-center rounded-lg bg-white">
                                <div class="w-6 h-6 rounded-full bg-emerald-100"></div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-600">Total Fasilitas</div>
                                <div class="text-base font-semibold text-gray-950">{{ $facilityCount }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="w-full lg:w-[36%] space-y-6">
            <div class="relative overflow-hidden bg-indigo-100 rounded-2xl p-5">
                <div class="absolute right-0 top-0 h-full w-28 bg-indigo-200/60"></div>
                <div class="absolute right-4 top-4 h-20 w-20 rounded-2xl border-2 border-dashed border-indigo-200 rotate-12"></div>
                <div class="relative flex flex-col gap-3">
                    <div class="text-xs font-semibold tracking-widest text-indigo-700">
                        PERBARUI INFO STUDIO
                    </div>
                    <div class="text-sm text-gray-600">
                        Lengkapi layanan dan fasilitas untuk meningkatkan kepercayaan pelanggan.
                    </div>
                    <a href="{{ route('owner.services.index') }}"
                       class="inline-flex w-max gap-2 items-center px-6 py-2.5 text-sm text-indigo-600 bg-white rounded-full shadow">
                        Kelola sekarang
                    </a>
                </div>
            </div>

            <div class="flex flex-col p-5 bg-white rounded-3xl min-h-[355px]">
                <div class="text-xl font-medium text-gray-800">Kesiapan Studio</div>
                <div class="flex flex-col flex-1 justify-center items-center mt-6">
                    <div class="relative flex items-center justify-center w-36 h-36 rounded-full"
                         style="background: {{ $verificationGradient }};">
                        <div class="absolute inset-4 bg-white rounded-full"></div>
                        <div class="relative text-3xl font-bold text-gray-800">{{ $donut }}%</div>
                    </div>
                    <div class="mt-4 text-xs font-medium text-gray-500">
                        {{ $verificationProgress['completed'] ?? 0 }} dari {{ $verificationProgress['total'] ?? 3 }} tahap selesai
                    </div>
                </div>
                <div class="flex gap-3 justify-between items-center mt-6 w-full text-xs sm:text-sm">
                    <div class="flex gap-2 items-center">
                        <div class="w-3.5 h-3.5 {{ $basicVerified ? 'bg-indigo-500' : 'bg-gray-300' }} rounded-md"></div>
                        <div class="text-gray-800 opacity-70">Data dasar</div>
                    </div>
                    <div class="flex gap-2 items-center">
                        <div class="w-3.5 h-3.5 {{ $verified ? 'bg-green-500' : 'bg-gray-300' }} rounded-md"></div>
                        <div class="text-gray-800 opacity-70">Verifikasi</div>
                    </div>
                    <div class="flex gap-2 items-center">
                        <div class="w-3.5 h-3.5 {{ $paymentVerified ? 'bg-orange-300' : 'bg-gray-300' }} rounded-md"></div>
                        <div class="text-gray-800 opacity-70">Pembayaran</div>
                    </div>
                </div>
            </div>

            <div id="ownerTopCard">
                @include('owner.dashboard.partials.top-card')
            </div>
        </div>
    </div>

    <script>
        (function () {
            const analyticsContainer = document.getElementById('ownerAnalyticsCard');
            const topContainer = document.getElementById('ownerTopCard');

            const renderCharts = (container) => {
                if (typeof window.initOwnerCharts === 'function') {
                    window.initOwnerCharts(container);
                }
            };

            const fetchPartial = async (url, container, withCharts) => {
                if (!container) return;
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (!response.ok) return;
                    const html = await response.text();
                    container.innerHTML = html;
                    if (withCharts) {
                        renderCharts(container);
                    }
                    bindAnalytics();
                    bindTopCard();
                } catch (error) {
                    console.error(error);
                }
            };

            const bindAnalytics = () => {
                if (!analyticsContainer) return;
                const form = analyticsContainer.querySelector('[data-analytics-form]');
                if (form && !form.dataset.bound) {
                    form.dataset.bound = '1';
                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        const url = new URL(form.action || window.location.href);
                        const params = new URLSearchParams(new FormData(form));
                        params.set('partial', 'analytics');
                        url.search = params.toString();
                        fetchPartial(url, analyticsContainer, true);
                    });
                }

                analyticsContainer.querySelectorAll('[data-range-link]').forEach((link) => {
                    if (link.dataset.bound) return;
                    link.dataset.bound = '1';
                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        const url = new URL(link.href, window.location.origin);
                        url.searchParams.set('partial', 'analytics');
                        fetchPartial(url, analyticsContainer, true);
                    });
                });
            };

            const bindTopCard = () => {
                if (!topContainer) return;
                const form = topContainer.querySelector('[data-top-form]');
                if (!form || form.dataset.bound) return;
                form.dataset.bound = '1';
                form.addEventListener('change', () => {
                    const url = new URL(form.action || window.location.href);
                    const params = new URLSearchParams(new FormData(form));
                    params.set('partial', 'top');
                    url.search = params.toString();
                    fetchPartial(url, topContainer, false);
                });
            };

            bindAnalytics();
            bindTopCard();
        })();
    </script>
</x-owner-layout>
