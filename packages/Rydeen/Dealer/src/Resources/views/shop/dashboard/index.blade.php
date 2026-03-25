@extends('rydeen::shop.layouts.master')

@section('title', trans('rydeen-dealer::app.shop.dashboard.title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            @lang('rydeen-dealer::app.shop.dashboard.welcome', ['name' => $customer->first_name])
        </h1>
        <p class="text-gray-600 mt-1">@lang('rydeen-dealer::app.shop.dashboard.subtitle')</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Total Orders YTD --}}
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                @lang('rydeen-dealer::app.shop.dashboard.total-orders-ytd')
            </p>
            <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ number_format($stats['total_orders_ytd']) }}
            </p>
        </div>

        {{-- This Month Total --}}
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                @lang('rydeen-dealer::app.shop.dashboard.this-month')
            </p>
            <p class="mt-2 text-3xl font-bold text-gray-900">
                ${{ number_format($stats['this_month_total'], 2) }}
            </p>
        </div>

        {{-- Pending Orders --}}
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                @lang('rydeen-dealer::app.shop.dashboard.pending-orders')
            </p>
            <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ number_format($stats['pending_orders_count']) }}
            </p>
        </div>

        {{-- Forecast Level --}}
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                @lang('rydeen-dealer::app.shop.dashboard.forecast-level')
            </p>
            <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ $stats['forecast_level'] }}
            </p>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('dealer.catalog') }}"
           class="block bg-white rounded-lg shadow p-4 hover:shadow-md transition text-center">
            <p class="font-semibold text-gray-800">@lang('rydeen-dealer::app.shop.dashboard.browse-catalog')</p>
        </a>

        <a href="{{ route('dealer.orders') }}"
           class="block bg-white rounded-lg shadow p-4 hover:shadow-md transition text-center">
            <p class="font-semibold text-gray-800">@lang('rydeen-dealer::app.shop.dashboard.view-orders')</p>
        </a>

        <a href="{{ route('dealer.resources') }}"
           class="block bg-white rounded-lg shadow p-4 hover:shadow-md transition text-center">
            <p class="font-semibold text-gray-800">@lang('rydeen-dealer::app.shop.dashboard.resources')</p>
        </a>
    </div>
@endsection
