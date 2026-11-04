{{-- The package's minimal layout for the guest pages. Point javaabu-auth.layouts.auth at your own. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['dv', 'ar'], true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' · ' : '' }}{{ config('app.name') }}</title>
    @manifestStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center gap-6 px-4 py-12">
        <p class="text-center text-lg font-semibold">
            <a href="{{ url('/') }}">{{ config('app.name') }}</a>
        </p>

        @yield('content')
    </main>

    @manifestScripts
</body>
</html>
