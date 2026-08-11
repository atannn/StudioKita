<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            @auth
                @if(Auth::user()->role === 'owner')
                    Dashboard Pemilik Studio
                @else
                    Dashboard Customer
                @endif
            @endauth
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @auth
                @if(Auth::user()->role === 'owner')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-gray-900 dark:text-gray-100"><b>Nama Studio:</b> {{ $tenant?->nama ?? '-' }}</p>
                        <p class="text-gray-900 dark:text-gray-100"><b>Pemilik:</b> {{ $tenant?->nama_pemilik ?? '-' }}</p>
                    </div>
                @else
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded dark:bg-green-900/30 dark:text-green-200">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('info'))
                        <div class="mb-4 p-4 bg-blue-100 text-blue-800 rounded dark:bg-blue-900/30 dark:text-blue-200">
                            {{ session('info') }}
                        </div>
                    @endif

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Selamat Datang, {{ Auth::user()->name }}!</h3>
                            <p class="mb-4">Anda dapat melakukan booking studio dengan memilih studio yang tersedia.</p>
                            
                            <div class="mt-6">
                                <a href="{{ route('studios.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                    Pilih Studio untuk Booking
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</x-app-layout>
