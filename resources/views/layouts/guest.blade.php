<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="text-gray-900 antialiased">
        <style>
            :root {
                --page-bg: #f6f1ea;
                --ink: #0f172a;
                --muted: #4b5563;
                --primary: #0f766e;
            }

            .sk-guest {
                font-family: "IBM Plex Sans", sans-serif;
                color: var(--ink);
                background:
                    radial-gradient(circle at 15% 10%, rgba(16, 185, 129, 0.14), transparent 55%),
                    radial-gradient(circle at 80% 20%, rgba(249, 115, 22, 0.18), transparent 55%),
                    radial-gradient(circle at 40% 80%, rgba(14, 116, 144, 0.14), transparent 50%),
                    var(--page-bg);
            }

            .sk-card {
                background: #ffffff;
                border-radius: 24px;
                border: 1px solid rgba(15, 23, 42, 0.06);
                box-shadow: 0 22px 40px rgba(15, 23, 42, 0.08);
            }

            .sk-logo-text {
                font-family: "Times New Roman", Times, serif;
                font-style: italic;
                font-weight: 400;
                margin-right: 1px;
            }

            .sk-logo-bold {
                font-family: "Be Vietnam Pro", sans-serif;
                font-weight: 700;
            }

            .sk-card input:focus,
            .sk-card textarea:focus,
            .sk-card select:focus {
                outline: none;
                border-color: #10b981 !important;
                box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.35);
            }

            .sk-card input[type="checkbox"]:focus {
                box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.35);
            }
        </style>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 sk-guest">
            <div>
                <a href="/" class="text-8xl">
                    <span class="sk-logo-text">Studio</span><span class="sk-logo-bold">Kita.</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-5 sk-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
