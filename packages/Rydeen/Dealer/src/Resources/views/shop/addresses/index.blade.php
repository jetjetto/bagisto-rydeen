@extends('rydeen::shop.layouts.master')

@section('title', trans('rydeen-dealer::app.shop.addresses.title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">@lang('rydeen-dealer::app.shop.addresses.title')</h1>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Add Address Form --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@lang('rydeen-dealer::app.shop.addresses.add-address')</h2>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('dealer.addresses.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Label --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.label') <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="label"
                           value="{{ old('label') }}"
                           placeholder="e.g. Main Warehouse, Showroom"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 @error('label') border-red-400 @enderror">
                    @error('label')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Company Name --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.company-name')
                    </label>
                    <input type="text"
                           name="company_name"
                           value="{{ old('company_name') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 @error('company_name') border-red-400 @enderror">
                    @error('company_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- First Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.first-name') <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="first_name"
                           value="{{ old('first_name') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 @error('first_name') border-red-400 @enderror">
                    @error('first_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Last Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.last-name') <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="last_name"
                           value="{{ old('last_name') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 @error('last_name') border-red-400 @enderror">
                    @error('last_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Address Line 1 --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.address1') <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="address1"
                           value="{{ old('address1') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 @error('address1') border-red-400 @enderror">
                    @error('address1')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Address Line 2 --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.address2')
                    </label>
                    <input type="text"
                           name="address2"
                           value="{{ old('address2') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400">
                </div>

                {{-- City --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.city') <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="city"
                           value="{{ old('city') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 @error('city') border-red-400 @enderror">
                    @error('city')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- State --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.state') <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="state"
                           value="{{ old('state') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 @error('state') border-red-400 @enderror">
                    @error('state')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Postcode --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.postcode') <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="postcode"
                           value="{{ old('postcode') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 @error('postcode') border-red-400 @enderror">
                    @error('postcode')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        @lang('rydeen-dealer::app.shop.addresses.phone')
                    </label>
                    <input type="text"
                           name="phone"
                           value="{{ old('phone') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 @error('phone') border-red-400 @enderror">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-5">
                <button type="submit"
                        class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold px-5 py-2 rounded text-sm transition-colors">
                    @lang('rydeen-dealer::app.shop.addresses.save-address')
                </button>
            </div>
        </form>
    </div>

    {{-- Address List --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@lang('rydeen-dealer::app.shop.addresses.saved-addresses')</h2>

        @if ($addresses->isEmpty())
            <p class="text-sm text-gray-500">@lang('rydeen-dealer::app.shop.addresses.no-addresses')</p>
        @else
            <div class="space-y-4">
                @foreach ($addresses as $address)
                    <div class="border border-gray-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-semibold text-gray-900">{{ $address->label }}</span>
                                @if ($address->is_approved)
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded font-medium bg-green-100 text-green-800">
                                        @lang('rydeen-dealer::app.shop.addresses.approved')
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded font-medium bg-yellow-100 text-yellow-800">
                                        @lang('rydeen-dealer::app.shop.addresses.pending-approval')
                                    </span>
                                @endif
                            </div>

                            @if ($address->company_name)
                                <p class="text-sm text-gray-600">{{ $address->company_name }}</p>
                            @endif

                            <p class="text-sm text-gray-700">{{ $address->first_name }} {{ $address->last_name }}</p>
                            <p class="text-sm text-gray-600">{{ $address->formatted_address }}</p>

                            @if ($address->phone)
                                <p class="text-sm text-gray-500 mt-1">{{ $address->phone }}</p>
                            @endif
                        </div>

                        <div class="flex-shrink-0">
                            <form action="{{ route('dealer.addresses.destroy', $address->id) }}" method="POST"
                                  onsubmit="return confirm('{{ trans('rydeen-dealer::app.shop.addresses.confirm-delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    @lang('rydeen-dealer::app.shop.addresses.delete')
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
