@extends('rydeen::shop.layouts.master')

@section('title', trans('rydeen-auth::app.login-title'))

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">RYDEEN</h1>
            <p class="mt-2 text-gray-600">{{ trans('rydeen-auth::app.login-title') }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('dealer.login.send-code') }}">
            @csrf

            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ trans('rydeen-auth::app.enter-email') }}
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    placeholder="dealer@example.com"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition font-medium"
            >
                {{ trans('rydeen-auth::app.send-code') }}
            </button>
        </form>
    </div>
</div>
@endsection
