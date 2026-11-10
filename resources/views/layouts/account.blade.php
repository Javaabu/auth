{{-- The package's minimal layout for the profile and security pages. Point javaabu-auth.layouts.account at your own. --}}
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
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 py-8">
        <header class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ $urls['home'] ?? url('/') }}" class="text-lg font-semibold">{{ config('app.name') }}</a>

            @include('auth::partials.account-nav')
        </header>

        <main class="flex flex-col gap-6">
            @yield('content')
        </main>
    </div>

    @manifestScripts
</body>
</html>
