<section class="hall-seat-layout-editor">
    <header class="hall-seat-layout-editor__header">
        <div>
            <h2>جدول چیدمان، قیمت و راهروها</h2>
            <p>با کلیک روی عنوان ردیف یا ستون، همه صندلی‌های آن را انتخاب و تغییرات گروهی را اعمال کنید.</p>
        </div>
        <span>{{ $record->capacity }} صندلی فعال</span>
    </header>

    @if ($this->layoutSections->isEmpty())
        <div class="hall-seat-layout-editor__empty">
            هنوز صندلی‌ای برای این سالن ساخته نشده است. از بخش «صندلی‌ها» در پایین صفحه برای ساخت گروهی چیدمان استفاده کنید.
        </div>
    @else
        <form wire:submit="save">
            <div class="hall-seat-layout-editor__bulk-toolbar">
                <div class="hall-seat-layout-editor__selection-summary">
                    <strong>{{ collect($selectedSeats)->filter()->count() }} صندلی انتخاب شده</strong>
                    <button type="button" wire:click="clearSelection">پاک کردن انتخاب</button>
                </div>

                <label>
                    <span>قیمت گروهی (تومان)</span>
                    <div class="hall-seat-layout-editor__bulk-control">
                        <x-filament::input.wrapper>
                            <x-filament::input type="number" min="0" step="1" wire:model="bulkPrice" />
                        </x-filament::input.wrapper>
                        <x-filament::button type="button" size="sm" wire:click="applyBulkPrice">
                            اعمال قیمت
                        </x-filament::button>
                    </div>
                    @error('bulkPrice')
                        <small class="hall-seat-layout-editor__error">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <span>عملیات راهرو</span>
                    <div class="hall-seat-layout-editor__bulk-control">
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model="bulkAisleAction">
                                <option value="vertical_add">افزودن راهروی عمودی بعد از انتخاب</option>
                                <option value="vertical_remove">حذف راهروی عمودی انتخاب</option>
                                <option value="horizontal_add">افزودن راهروی افقی بعد از ردیف</option>
                                <option value="horizontal_remove">حذف راهروی افقی ردیف</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        <x-filament::button type="button" size="sm" wire:click="applyBulkAisle">
                            اعمال راهرو
                        </x-filament::button>
                    </div>
                </label>
            </div>

            <div class="hall-seat-layout-editor__screen">صحنه</div>

            @foreach ($this->layoutSections as $section => $layout)
                @php($sectionSeats = $layout['rows']->flatten())
                @php($isSectionSelected = $sectionSeats->isNotEmpty() && $sectionSeats->every(fn ($seat) => $selectedSeats[$seat->id] ?? false))

                <div class="hall-seat-layout-editor__section">
                    <div class="hall-seat-layout-editor__section-heading">
                        <h3>{{ $section }}</h3>
                        <button
                            type="button"
                            wire:click="toggleSeats(@json($sectionSeats->pluck('id')->values()->all()))"
                            @class(['is-selected' => $isSectionSelected])
                            aria-pressed="{{ $isSectionSelected ? 'true' : 'false' }}"
                        >
                            انتخاب کل بخش
                        </button>
                    </div>

                    <div class="hall-seat-layout-editor__table-wrap">
                        <table class="hall-seat-layout-editor__table">
                            <thead>
                                <tr>
                                    <th scope="col">ردیف / ستون</th>
                                    @foreach ($layout['columns'] as $column)
                                        @php($columnSeats = $layout['rows']->map(fn ($rowSeats) => $rowSeats->get($column))->filter())
                                        @php($isColumnSelected = $columnSeats->isNotEmpty() && $columnSeats->every(fn ($seat) => $selectedSeats[$seat->id] ?? false))
                                        <th scope="col">
                                            <button
                                                type="button"
                                                wire:click="toggleSeats(@json($columnSeats->pluck('id')->values()->all()))"
                                                @class(['is-selected' => $isColumnSelected])
                                                aria-pressed="{{ $isColumnSelected ? 'true' : 'false' }}"
                                            >
                                                ستون {{ $column }}
                                            </button>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($layout['rows'] as $rowLabel => $rowSeats)
                                    @php($isRowSelected = $rowSeats->isNotEmpty() && $rowSeats->every(fn ($seat) => $selectedSeats[$seat->id] ?? false))
                                    @php($hasRowAisle = $rowSeats->contains(fn ($seat) => $rowAisles[$seat->id] ?? false))
                                    <tr @class(['has-horizontal-aisle' => $hasRowAisle])>
                                        <th scope="row">
                                            <button
                                                type="button"
                                                wire:click="toggleSeats(@json($rowSeats->pluck('id')->values()->all()))"
                                                @class(['is-selected' => $isRowSelected])
                                                aria-pressed="{{ $isRowSelected ? 'true' : 'false' }}"
                                            >
                                                ردیف {{ $rowLabel }}
                                            </button>
                                        </th>

                                        @foreach ($layout['columns'] as $column)
                                            @php($seat = $rowSeats->get($column))
                                            @if ($seat)
                                                <td @class([
                                                    'is-selected' => $selectedSeats[$seat->id] ?? false,
                                                    'has-vertical-aisle' => $aisles[$seat->id] ?? false,
                                                    'is-inactive' => ! $seat->is_active,
                                                ])>
                                                    <label class="hall-seat-layout-editor__seat-selector" title="{{ $seat->code }}">
                                                        <x-filament::input.checkbox wire:model.live="selectedSeats.{{ $seat->id }}" />
                                                        <strong>{{ $seat->number }}</strong>
                                                    </label>
                                                    <x-filament::input.wrapper>
                                                        <x-filament::input
                                                            type="number"
                                                            min="0"
                                                            step="1"
                                                            placeholder="قیمت سانس"
                                                            aria-label="قیمت صندلی {{ $seat->code }}"
                                                            wire:model="prices.{{ $seat->id }}"
                                                        />
                                                    </x-filament::input.wrapper>
                                                    @error('prices.'.$seat->id)
                                                        <small class="hall-seat-layout-editor__error">{{ $message }}</small>
                                                    @enderror
                                                </td>
                                            @else
                                                <td class="is-empty">—</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="hall-seat-layout-editor__actions">
                <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="save">
                    ذخیره تغییرات دستی
                </x-filament::button>
            </div>
        </form>
    @endif
</section>
