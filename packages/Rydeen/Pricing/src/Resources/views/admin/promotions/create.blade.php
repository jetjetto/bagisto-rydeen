<x-admin::layouts>
    <x-slot:title>
        @if (isset($promotion))
            @lang('rydeen-pricing::app.edit')
        @else
            @lang('rydeen-pricing::app.create')
        @endif
    </x-slot>

    <x-admin::form
        :method="isset($promotion) ? 'PUT' : 'POST'"
        :action="isset($promotion)
            ? route('admin.rydeen.promotions.update', $promotion->id)
            : route('admin.rydeen.promotions.store')"
    >
        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @if (isset($promotion))
                    @lang('rydeen-pricing::app.edit')
                @else
                    @lang('rydeen-pricing::app.create')
                @endif
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.rydeen.promotions.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    Back
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    Save
                </button>
            </div>
        </div>

        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            {{-- Left column: main fields --}}
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        General
                    </p>

                    {{-- Name --}}
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('rydeen-pricing::app.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="name"
                            :value="old('name', $promotion->name ?? '')"
                            rules="required"
                            :label="trans('rydeen-pricing::app.name')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    {{-- Type --}}
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('rydeen-pricing::app.type')
                        </x-admin::form.control-group.label>

                        <select
                            name="type"
                            id="promo-type"
                            class="custom-select w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                            onchange="toggleTypeFields()"
                        >
                            <option value="percentage" {{ old('type', $promotion->type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                            <option value="threshold" {{ old('type', $promotion->type ?? '') === 'threshold' ? 'selected' : '' }}>Threshold</option>
                            <option value="timing" {{ old('type', $promotion->type ?? '') === 'timing' ? 'selected' : '' }}>Timing</option>
                            <option value="sku_level" {{ old('type', $promotion->type ?? '') === 'sku_level' ? 'selected' : '' }}>SKU Level</option>
                        </select>

                        <x-admin::form.control-group.error control-name="type" />
                    </x-admin::form.control-group>

                    {{-- Value --}}
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('rydeen-pricing::app.value')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="value"
                            :value="old('value', $promotion->value ?? '')"
                            rules="required|decimal"
                            :label="trans('rydeen-pricing::app.value')"
                        />

                        <x-admin::form.control-group.error control-name="value" />
                    </x-admin::form.control-group>

                    {{-- Min Qty (for threshold type) --}}
                    <div id="min-qty-field">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Minimum Quantity
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="min_qty"
                                :value="old('min_qty', $promotion->min_qty ?? '')"
                                :label="'Minimum Quantity'"
                            />

                            <x-admin::form.control-group.error control-name="min_qty" />
                        </x-admin::form.control-group>
                    </div>

                    {{-- Date range (for timing type) --}}
                    <div id="timing-fields">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Starts At
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="datetime"
                                name="starts_at"
                                :value="old('starts_at', isset($promotion) && $promotion->starts_at ? $promotion->starts_at->format('Y-m-d H:i:s') : '')"
                                :label="'Starts At'"
                            />

                            <x-admin::form.control-group.error control-name="starts_at" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Ends At
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="datetime"
                                name="ends_at"
                                :value="old('ends_at', isset($promotion) && $promotion->ends_at ? $promotion->ends_at->format('Y-m-d H:i:s') : '')"
                                :label="'Ends At'"
                            />

                            <x-admin::form.control-group.error control-name="ends_at" />
                        </x-admin::form.control-group>
                    </div>
                </div>
            </div>

            {{-- Right column: scope & status --}}
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <x-admin::accordion>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('rydeen-pricing::app.scope')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        {{-- Scope --}}
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('rydeen-pricing::app.scope')
                            </x-admin::form.control-group.label>

                            <select
                                name="scope"
                                class="custom-select w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                            >
                                <option value="all" {{ old('scope', $promotion->scope ?? '') === 'all' ? 'selected' : '' }}>All</option>
                                <option value="category" {{ old('scope', $promotion->scope ?? '') === 'category' ? 'selected' : '' }}>Category</option>
                                <option value="customer_group" {{ old('scope', $promotion->scope ?? '') === 'customer_group' ? 'selected' : '' }}>Customer Group</option>
                                <option value="sku" {{ old('scope', $promotion->scope ?? '') === 'sku' ? 'selected' : '' }}>SKU</option>
                            </select>

                            <x-admin::form.control-group.error control-name="scope" />
                        </x-admin::form.control-group>

                        {{-- Scope ID --}}
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Scope ID
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="scope_id"
                                :value="old('scope_id', $promotion->scope_id ?? '')"
                                :label="'Scope ID'"
                            />

                            <x-admin::form.control-group.error control-name="scope_id" />
                        </x-admin::form.control-group>

                        {{-- Active --}}
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('rydeen-pricing::app.active')
                            </x-admin::form.control-group.label>

                            <input
                                type="hidden"
                                name="active"
                                value="0"
                            />

                            <x-admin::form.control-group.control
                                type="checkbox"
                                name="active"
                                value="1"
                                :checked="old('active', $promotion->active ?? true)"
                                :label="trans('rydeen-pricing::app.active')"
                            />

                            <x-admin::form.control-group.error control-name="active" />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </x-admin::form>

    @pushOnce('scripts')
        <script>
            function toggleTypeFields() {
                const type = document.getElementById('promo-type').value;
                const minQtyField = document.getElementById('min-qty-field');
                const timingFields = document.getElementById('timing-fields');

                minQtyField.style.display = type === 'threshold' ? 'block' : 'none';
                timingFields.style.display = type === 'timing' ? 'block' : 'none';
            }

            document.addEventListener('DOMContentLoaded', toggleTypeFields);
        </script>
    @endPushOnce
</x-admin::layouts>
