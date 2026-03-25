@extends('rydeen::shop.layouts.master')

@section('title', trans('rydeen-dealer::app.shop.orders.review-title'))

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">@lang('rydeen-dealer::app.shop.orders.review-title')</h1>

    @if (session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800 text-sm">{{ session('error') }}</div>
    @endif

    @if ($cart && $cart->items->count())
        <div class="bg-white rounded-lg shadow overflow-x-auto mb-6">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs text-gray-600 uppercase">
                    <tr>
                        <th class="px-4 py-3">@lang('rydeen-dealer::app.shop.orders.product')</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">@lang('rydeen-dealer::app.shop.catalog.qty')</th>
                        <th class="px-4 py-3">@lang('rydeen-dealer::app.shop.orders.unit-price')</th>
                        <th class="px-4 py-3">@lang('rydeen-dealer::app.shop.orders.subtotal')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cart->items as $item)
                        <tr class="border-b">
                            <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $item->sku }}</td>
                            <td class="px-4 py-3">{{ (int) $item->quantity }}</td>
                            <td class="px-4 py-3">${{ number_format($item->price, 2) }}</td>
                            <td class="px-4 py-3 font-medium">${{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right font-semibold">@lang('rydeen-dealer::app.shop.orders.subtotal')</td>
                        <td class="px-4 py-3 font-semibold">${{ number_format($cart->sub_total, 2) }}</td>
                    </tr>
                    @if ($cart->tax_total > 0)
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right">@lang('rydeen-dealer::app.shop.orders.tax')</td>
                            <td class="px-4 py-3">${{ number_format($cart->tax_total, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="text-lg">
                        <td colspan="4" class="px-4 py-3 text-right font-bold">@lang('rydeen-dealer::app.shop.orders.total')</td>
                        <td class="px-4 py-3 font-bold">${{ number_format($cart->grand_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Place Order Form --}}
        <form action="{{ route('dealer.order-review.place') }}" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf
            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                    @lang('rydeen-dealer::app.shop.orders.notes')
                </label>
                <textarea name="notes"
                          id="notes"
                          rows="3"
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                          placeholder="{{ trans('rydeen-dealer::app.shop.orders.notes-placeholder') }}"></textarea>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('dealer.catalog') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    @lang('rydeen-dealer::app.shop.orders.continue-shopping')
                </a>
                <button type="submit"
                        class="bg-green-600 text-white px-8 py-3 rounded text-sm font-semibold hover:bg-green-700">
                    @lang('rydeen-dealer::app.shop.orders.place-order')
                </button>
            </div>
        </form>
    @else
        <div class="text-center py-12 bg-white rounded-lg shadow">
            <p class="text-gray-500 mb-4">@lang('rydeen-dealer::app.shop.orders.cart-empty')</p>
            <a href="{{ route('dealer.catalog') }}"
               class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                @lang('rydeen-dealer::app.shop.dashboard.browse-catalog')
            </a>
        </div>
    @endif
@endsection
