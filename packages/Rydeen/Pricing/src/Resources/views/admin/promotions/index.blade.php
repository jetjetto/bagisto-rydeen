<x-admin::layouts>
    <x-slot:title>
        @lang('rydeen-pricing::app.promotions')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('rydeen-pricing::app.promotions')
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.rydeen.promotions.create') }}"
                class="primary-button"
            >
                @lang('rydeen-pricing::app.create')
            </a>
        </div>
    </div>

    <div class="mt-7 overflow-x-auto rounded-xl bg-white shadow-sm dark:bg-gray-900">
        <table class="w-full text-sm text-left">
            <thead class="border-b bg-gray-50 text-xs uppercase text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <tr>
                    <th class="px-6 py-4">@lang('rydeen-pricing::app.name')</th>
                    <th class="px-6 py-4">@lang('rydeen-pricing::app.type')</th>
                    <th class="px-6 py-4">@lang('rydeen-pricing::app.value')</th>
                    <th class="px-6 py-4">@lang('rydeen-pricing::app.scope')</th>
                    <th class="px-6 py-4">@lang('rydeen-pricing::app.active')</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($promotions as $promotion)
                    <tr class="border-b dark:border-gray-800">
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">
                            {{ $promotion->name }}
                        </td>

                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            {{ ucfirst(str_replace('_', ' ', $promotion->type)) }}
                        </td>

                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            {{ $promotion->value }}
                        </td>

                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            {{ ucfirst(str_replace('_', ' ', $promotion->scope)) }}
                            @if ($promotion->scope_id)
                                (#{{ $promotion->scope_id }})
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if ($promotion->active)
                                <span class="inline-block rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-block rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    href="{{ route('admin.rydeen.promotions.edit', $promotion->id) }}"
                                    class="cursor-pointer rounded-md p-1.5 text-gray-600 transition-all hover:bg-violet-100 hover:text-violet-600 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    <span class="icon-edit text-2xl"></span>
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.rydeen.promotions.destroy', $promotion->id) }}"
                                    onsubmit="return confirm('Are you sure you want to delete this promotion?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="cursor-pointer rounded-md p-1.5 text-gray-600 transition-all hover:bg-red-100 hover:text-red-600 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        <span class="icon-delete text-2xl"></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="6"
                            class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"
                        >
                            No promotions found. Create your first promotion to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin::layouts>
