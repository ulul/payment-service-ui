<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
                <a href="{{ url('/') }}" class="text-sm font-semibold tracking-tight">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <nav class="flex items-center gap-3 text-sm">
                    @if (session()->has('api_token'))
                        <a class="text-slate-600 hover:text-slate-900" href="{{ route('dashboard') }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-md bg-slate-900 px-3 py-1.5 font-medium text-white hover:bg-slate-800">
                                Logout
                            </button>
                        </form>
                    @else
                        <a class="text-slate-600 hover:text-slate-900" href="{{ route('login') }}">Login</a>
                        <a class="rounded-md bg-slate-900 px-3 py-1.5 font-medium text-white hover:bg-slate-800" href="{{ route('register') }}">
                            Register
                        </a>
                    @endif
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-10">
            @if (session('success'))
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </body>
    @yield('scripts')
</html>


