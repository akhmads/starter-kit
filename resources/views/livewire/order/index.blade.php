<?php

use Illuminate\Support\Facades\Gate;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\Attributes\Session;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\Order;

new class extends Component {
    use Toast, WithPagination;

    #[Session(key: 'order_per_page')]
    public int $perPage = 10;

    #[Session(key: 'order_code')]
    public string $code = '';

    public int $filterCount = 0;
    public bool $drawer = false;
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public function mount(): void
    {
        Gate::authorize('view order');
        $this->updateFilterCount();
    }

    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'date', 'label' => 'Date', 'format' => ['date', 'd-M-Y']],
            ['key' => 'total', 'label' => 'Total', 'format' => ['currency', '2.,', '']],
            ['key' => 'note', 'label' => 'Note'],
            ['key' => 'created_at', 'label' => 'Created At', 'class' => 'lg:w-[160px]', 'format' => ['date', 'd-M-y, H:i']],
            ['key' => 'updated_at', 'label' => 'Updated At', 'class' => 'lg:w-[160px]', 'format' => ['date', 'd-M-y, H:i']],
        ];
    }

    public function orders(): LengthAwarePaginator
    {
        return Order::query()
        ->orderBy(...array_values($this->sortBy))
        ->filterLike('code', $this->code)
        ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'orders' => $this->orders(),
        ];
    }

    public function updated($property): void
    {
        if (! is_array($property) && $property != "") {
            $this->resetPage();
            $this->updateFilterCount();
        }
    }

    public function search(): void
    {
        $this->validate([
            'name' => 'nullable',
        ]);
    }

    public function clear(): void
    {
        $this->success('Filters cleared.');
        $this->reset();
        $this->resetPage();
        $this->updateFilterCount();
    }

    public function updateFilterCount(): void
    {
        $count = 0;
        if (!empty($this->name)) $count++;
        if (!empty($this->is_active)) $count++;
        $this->filterCount = $count;
    }

    public function delete(Order $order): void
    {
        Gate::authorize('delete order');
        $order->delete();
        $this->success('Order has been deleted.');
    }
}; ?>

<div>
    {{-- HEADER --}}
    <x-header title="Orders" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" icon="o-funnel" badge="{{ $filterCount }}" />
            @can('create order')
            <x-button label="Create" link="{{ route('order.create') }}" icon="o-plus" class="btn-primary" />
            @endcan
        </x-slot:actions>
    </x-header>

    {{-- TABLE --}}
    <x-card wire:loading.class="bg-slate-200/50 text-slate-400" class="border border-base-300">
        <x-table
            :headers="$headers"
            :rows="$orders"
            :sort-by="$sortBy"
            with-pagination
            show-empty-text
            per-page="perPage"
            :link="auth()->user()->can('update order') ? route('order.edit', ['order' => '[id]']) : null"
        >
            @scope('cell_is_active', $order)
            <x-active-badge :status="$order->is_active" />
            @endscope
            @scope('actions', $order)
            <div class="flex gap-0">
                @can('delete order')
                <x-button wire:click="delete({{ $order->id }})" spinner="delete({{ $order->id }})" wire:confirm="Are you sure you want to delete this row?" icon="o-trash" class="btn-ghost btn-sm" />
                @endcan
                @can('update order')
                <x-button link="{{ route('order.edit', $order->id) }}" icon="o-pencil-square" class="btn-ghost btn-sm" />
                @endcan
            </div>
            @endscope
        </x-table>
    </x-card>

    {{-- FILTER DRAWER --}}
    <x-search-drawer>
        <x-input label="Code" wire:model="code" />
        {{-- <x-select label="Active" wire:model="is_active" :options="\App\Enums\ActiveStatus::toSelect()" placeholder="-- All --" /> --}}
    </x-search-drawer>
</div>
