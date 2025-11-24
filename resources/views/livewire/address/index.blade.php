<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\Attributes\Session;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\Address;

new class extends Component {
    use Toast, WithPagination;

    #[Session(key: 'address_per_page')]
    public int $perPage = 10;

    #[Session(key: 'address_name')]
    public string $name = '';

    public int $filterCount = 0;
    public bool $drawer = false;
    public array $sortBy = ['column' => 'contact_name', 'direction' => 'asc'];

    public function mount(): void
    {
        $this->updateFilterCount();
    }

    public function headers(): array
    {
        return [
            ['key' => 'contact_name', 'label' => 'Name'],
            ['key' => 'address', 'label' => 'Address'],
            ['key' => 'created_at', 'label' => 'Created At', 'class' => 'lg:w-[160px]', 'format' => ['date', 'd-M-y, H:i']],
            ['key' => 'updated_at', 'label' => 'Updated At', 'class' => 'lg:w-[160px]', 'format' => ['date', 'd-M-y, H:i']],
        ];
    }

    public function addresses(): LengthAwarePaginator
    {
        return Address::query()
        ->withAggregate('contact', 'name')
        ->orderBy(...array_values($this->sortBy))
        ->when($this->name, fn($query) => $query->whereHas('contact', fn($q) => $q->where('name', 'like', '%'.$this->name.'%')))
        ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'addresses' => $this->addresses(),
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
        $this->reset(['name']);
        $this->resetPage();
        $this->updateFilterCount();
        $this->drawer = false;
    }

    public function updateFilterCount(): void
    {
        $count = 0;
        if (!empty($this->name)) $count++;
        $this->filterCount = $count;
    }

    public function delete(Address $address): void
    {
        $address->delete();
        $this->success('Address successfully deleted.');
    }
}; ?>

<div>
    {{-- HEADER --}}
    <x-header title="Address" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" icon="o-funnel" badge="{{ $filterCount }}" />
            <x-button label="Create" link="{{ route('address.create') }}" icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    {{-- TABLE --}}
    <x-card wire:loading.class="bg-slate-200/50 text-slate-400" class="border border-base-300">
        <x-table
            :headers="$headers"
            :rows="$addresses"
            :sort-by="$sortBy"
            with-pagination
            show-empty-text
            per-page="perPage"
            :link="route('address.edit', ['address' => '[id]'])"
        >
            @scope('actions', $address)
            <div class="flex gap-0">
                <x-button wire:click="delete({{ $address->id }})" spinner="delete({{ $address->id }})" wire:confirm="Are you sure you want to delete this row?" icon="o-trash" class="btn-ghost btn-sm" />
                <x-button link="{{ route('address.edit', $address->id) }}" icon="o-pencil-square" class="btn-ghost btn-sm" />
            </div>
            @endscope
        </x-table>
    </x-card>

    {{-- FILTER DRAWER --}}
    <x-search-drawer>
        <x-input label="Name" wire:model="name" />
    </x-search-drawer>
</div>
