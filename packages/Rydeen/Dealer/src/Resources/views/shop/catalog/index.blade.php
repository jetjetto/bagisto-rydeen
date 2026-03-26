@extends('rydeen::shop.layouts.master')

@section('title', trans('rydeen-dealer::app.shop.catalog.title'))

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900">@lang('rydeen-dealer::app.shop.catalog.title')</h1>

        {{-- Search --}}
        <form action="{{ route('dealer.catalog') }}" method="GET" class="flex gap-2">
            @if (request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="{{ trans('rydeen-dealer::app.shop.catalog.search-placeholder') }}"
                   class="border border-gray-300 rounded px-3 py-2 text-sm w-64">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                @lang('rydeen-dealer::app.shop.catalog.search')
            </button>
        </form>
    </div>

    <div class="flex gap-6">
        {{-- Category Sidebar --}}
        <aside class="hidden lg:block w-56 flex-shrink-0">
            <h2 class="text-sm font-semibold text-gray-700 uppercase mb-3">@lang('rydeen-dealer::app.shop.catalog.categories')</h2>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dealer.catalog') }}"
                       class="block text-sm px-2 py-1 rounded {{ ! request('category') ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                        @lang('rydeen-dealer::app.shop.catalog.all-products')
                    </a>
                </li>
                @if ($categories)
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('dealer.catalog', ['category' => $category->id]) }}"
                               class="block text-sm px-2 py-1 rounded {{ request('category') == $category->id ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
        </aside>

        {{-- Product Grid --}}
        <div class="flex-1">
            @if ($products->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    @lang('rydeen-dealer::app.shop.catalog.no-products')
                </div>
            @else
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($products as $product)
                        <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
                            <a href="{{ route('dealer.catalog.product', $product->url_key ?? $product->id) }}">
                                @php
                                    $imageUrl = $product->images->first()?->url ?? $product->base_image_url ?? null;
                                @endphp
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-48 object-contain bg-gray-50">
                                @else
                                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
                                        @lang('rydeen-dealer::app.shop.catalog.no-image')
                                    </div>
                                @endif
                            </a>

                            <div class="p-4">
                                <a href="{{ route('dealer.catalog.product', $product->url_key ?? $product->id) }}"
                                   class="block text-sm font-semibold text-gray-800 hover:text-blue-600 truncate">
                                    {{ $product->name }}
                                </a>
                                <p class="text-xs text-gray-500 mt-1">SKU: {{ $product->sku }}</p>

                                {{-- Price --}}
                                @if (isset($prices[$product->id]))
                                    <p class="mt-2 text-lg font-bold text-green-700">
                                        ${{ number_format($prices[$product->id]['price'], 2) }}
                                    </p>
                                    @if ($prices[$product->id]['promo_name'])
                                        <span class="inline-block mt-1 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs rounded font-medium">
                                            {{ $prices[$product->id]['promo_name'] }}
                                        </span>
                                    @endif
                                @else
                                    <p class="mt-2 text-sm text-gray-400 italic">
                                        @lang('rydeen-dealer::app.shop.catalog.price-unavailable')
                                    </p>
                                @endif

                                {{-- Add to Cart --}}
                                <button type="button"
                                        onclick="addToCart({{ $product->id }}, 1, this)"
                                        class="mt-3 w-full bg-blue-600 text-white text-sm py-2 rounded hover:bg-blue-700">
                                    @lang('rydeen-dealer::app.shop.catalog.add-to-cart')
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function addToCart(productId, quantity, btn) {
        btn.disabled = true;
        btn.textContent = '{{ trans('rydeen-dealer::app.shop.catalog.adding') }}';

        fetch('{{ route('shop.api.checkout.cart.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ product_id: productId, quantity: quantity }),
        })
        .then(r => r.json())
        .then(data => {
            btn.textContent = '{{ trans('rydeen-dealer::app.shop.catalog.added') }}';
            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = '{{ trans('rydeen-dealer::app.shop.catalog.add-to-cart') }}';
            }, 1500);
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = '{{ trans('rydeen-dealer::app.shop.catalog.add-to-cart') }}';
        });
    }
</script>
@endpush
