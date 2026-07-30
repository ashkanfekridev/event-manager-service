<?php

namespace App\Livewire;

use App\Models\Hall;
use App\Models\Seat;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HallSeatLayoutEditor extends Component
{
    public Hall $record;

    /** @var array<int, string|null> */
    public array $prices = [];

    /** @var array<int, bool> */
    public array $aisles = [];

    /** @var array<int, bool> */
    public array $rowAisles = [];

    /** @var array<int, bool> */
    public array $selectedSeats = [];

    public ?string $bulkPrice = null;

    public string $bulkAisleAction = 'vertical_add';

    public function mount(Hall $record): void
    {
        $this->record = $record;

        $this->authorizeAccess();
        $this->fillSeatSettings();
    }

    /**
     * @return Collection<string, array{columns: Collection<int, string>, rows: Collection<string, Collection<string, Seat>>}>
     */
    #[Computed]
    public function layoutSections(): Collection
    {
        return $this->record->seats()
            ->orderBy('section')
            ->orderBy('row_label')
            ->get()
            ->groupBy('section')
            ->map(fn (Collection $sectionSeats): array => [
                'columns' => $sectionSeats
                    ->pluck('number')
                    ->unique()
                    ->sort(SORT_NATURAL)
                    ->values(),
                'rows' => $sectionSeats
                    ->groupBy('row_label')
                    ->map(fn (Collection $rowSeats): Collection => $rowSeats
                        ->sortBy('number', SORT_NATURAL)
                        ->keyBy(fn (Seat $seat): string => $seat->number)),
            ]);
    }

    /** @param array<int, int|string> $seatIds */
    public function toggleSeats(array $seatIds): void
    {
        $this->authorizeAccess();

        $validated = Validator::make(
            ['seat_ids' => $seatIds],
            [
                'seat_ids' => ['array', 'max:1000'],
                'seat_ids.*' => ['integer', 'distinct'],
            ],
        )->validate();

        $this->toggleSeatIds(
            $this->record->seats()
                ->whereKey($validated['seat_ids'])
                ->pluck('id'),
        );
    }

    public function clearSelection(): void
    {
        $this->selectedSeats = [];
    }

    public function applyBulkPrice(): void
    {
        $this->authorizeAccess();

        $validated = $this->validate([
            'bulkPrice' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ], attributes: [
            'bulkPrice' => 'قیمت گروهی',
        ]);

        $selectedSeatIds = $this->selectedSeatIds();

        if ($selectedSeatIds->isEmpty()) {
            $this->sendSelectionRequiredNotification();

            return;
        }

        foreach ($selectedSeatIds as $seatId) {
            $this->prices[$seatId] = (string) $validated['bulkPrice'];
        }

        $this->save();
    }

    public function applyBulkAisle(): void
    {
        $this->authorizeAccess();

        $validated = $this->validate([
            'bulkAisleAction' => ['required', Rule::in([
                'vertical_add',
                'vertical_remove',
                'horizontal_add',
                'horizontal_remove',
            ])],
        ]);

        $selectedSeatIds = $this->selectedSeatIds();

        if ($selectedSeatIds->isEmpty()) {
            $this->sendSelectionRequiredNotification();

            return;
        }

        $action = $validated['bulkAisleAction'];

        if (Str::startsWith($action, 'vertical_')) {
            $isAdding = $action === 'vertical_add';

            foreach ($selectedSeatIds as $seatId) {
                $this->aisles[$seatId] = $isAdding;
            }
        } else {
            $completeRows = $this->completeSelectedRows();

            if ($completeRows->isEmpty()) {
                Notification::make()
                    ->warning()
                    ->title('برای راهروی افقی، یک ردیف کامل را از عنوان ردیف انتخاب کنید')
                    ->send();

                return;
            }

            $isAdding = $action === 'horizontal_add';

            foreach ($completeRows->flatten() as $seatId) {
                $this->rowAisles[$seatId] = $isAdding;
            }
        }

        $this->save();
    }

    public function save(): void
    {
        $this->authorizeAccess();

        $validated = $this->validate([
            'prices' => ['array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'aisles' => ['array'],
            'aisles.*' => ['boolean'],
            'rowAisles' => ['array'],
            'rowAisles.*' => ['boolean'],
        ], attributes: [
            'prices.*' => 'قیمت صندلی',
            'aisles.*' => 'وضعیت راهرو',
            'rowAisles.*' => 'وضعیت راهروی ردیف',
        ]);

        $timestamp = now();
        $seats = $this->record->seats()->get();

        Seat::query()->upsert(
            $seats->map(fn (Seat $seat): array => [
                ...$seat->getAttributes(),
                'default_price' => $validated['prices'][$seat->id] ?? null,
                'aisle_after' => $validated['aisles'][$seat->id] ?? false,
                'aisle_after_row' => $validated['rowAisles'][$seat->id] ?? false,
                'updated_at' => $timestamp,
            ])->all(),
            ['id'],
            ['default_price', 'aisle_after', 'aisle_after_row', 'updated_at'],
        );

        $this->fillSeatSettings();

        Notification::make()
            ->success()
            ->title('قیمت‌ها و راهروهای سالن ذخیره شد')
            ->send();
    }

    public function render(): View
    {
        return view('livewire.hall-seat-layout-editor');
    }

    private function fillSeatSettings(): void
    {
        $seats = $this->record->seats()->get(['id', 'default_price', 'aisle_after', 'aisle_after_row']);

        $this->prices = $seats
            ->mapWithKeys(fn (Seat $seat): array => [$seat->id => $seat->default_price])
            ->all();
        $this->aisles = $seats
            ->mapWithKeys(fn (Seat $seat): array => [$seat->id => $seat->aisle_after])
            ->all();
        $this->rowAisles = $seats
            ->mapWithKeys(fn (Seat $seat): array => [$seat->id => $seat->aisle_after_row])
            ->all();
    }

    /**
     * @param  Collection<int, int>  $seatIds
     */
    private function toggleSeatIds(Collection $seatIds): void
    {
        $shouldSelect = $seatIds->contains(
            fn (int $seatId): bool => ! ($this->selectedSeats[$seatId] ?? false),
        );

        foreach ($seatIds as $seatId) {
            $this->selectedSeats[$seatId] = $shouldSelect;
        }
    }

    /** @return Collection<int, int> */
    private function selectedSeatIds(): Collection
    {
        $selectedSeatIds = collect($this->selectedSeats)
            ->filter()
            ->keys();

        return $this->record->seats()
            ->whereKey($selectedSeatIds)
            ->pluck('id');
    }

    /** @return Collection<int, Collection<int, int>> */
    private function completeSelectedRows(): Collection
    {
        $selectedSeatIds = $this->selectedSeatIds()->flip();

        return $this->layoutSections
            ->flatMap(fn (array $layout): Collection => $layout['rows']->values())
            ->filter(fn (Collection $rowSeats): bool => $rowSeats->isNotEmpty() && $rowSeats
                ->every(fn (Seat $seat): bool => $selectedSeatIds->has($seat->id)))
            ->map(fn (Collection $rowSeats): Collection => $rowSeats->pluck('id')->values())
            ->values();
    }

    private function sendSelectionRequiredNotification(): void
    {
        Notification::make()
            ->warning()
            ->title('ابتدا یک یا چند صندلی، ردیف یا ستون را انتخاب کنید')
            ->send();
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        $panel = Filament::getCurrentPanel();

        abort_unless(
            ($user instanceof FilamentUser) && ($panel !== null) && $user->canAccessPanel($panel),
            403,
        );
    }
}
