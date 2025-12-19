@php
    $title = 'Register';
@endphp

@extends('layouts.app', ['title' => $title])

@section('content')
    <div class="grid gap-8">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-lg font-semibold">Register</h1>
            @if ($errors->has('api'))
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first('api') }}
                </div>
            @endif
            <form class="mt-6 space-y-4" method="POST" action="{{ route('register') }}">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="name">Name</label>
                    <input
                        class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200"
                        id="name"
                        name="name"
                        type="text"
                        autocomplete="name"
                        required
                        placeholder="Jane Doe"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
                    <input
                        class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200"
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        placeholder="you@example.com"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="password">Password</label>
                    <input
                        class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="••••••••"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="password_confirmation">Confirm password</label>
                    <input
                        class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200"
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="••••••••"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-60"
                    >
                        Submit
                    </button>
                    <p class="text-sm text-slate-600">
                        Already have an account?
                        <a class="font-medium text-slate-900 underline underline-offset-4" href="{{ route('login') }}">Login</a>
                    </p>
                </div>
            </form>
        </section>
    </div>
@endsection


