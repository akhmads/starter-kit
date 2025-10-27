<?php

use Illuminate\Support\Facades\Gate;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\Attributes\Session;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\Product;

new class extends Component {
    use Toast, WithPagination;

    #[Session(key: 'product_per_page')]
    public int $perPage = 10;

    #[Session(key: 'product_name')]
    public string $name = '';

    #[Session(key: 'product_active')]
    public string $is_active = '';

    public int $filterCount = 0;
    public bool $drawer = false;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function mount(): void
    {
        Gate::authorize('view product');
        $this->updateFilterCount();
    }

    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'price', 'label' => 'Price', 'format' => ['currency', '2.,', '']],
            ['key' => 'is_active', 'label' => 'Active', 'class' => 'lg:w-[120px]'],
            ['key' => 'created_at', 'label' => 'Created At', 'class' => 'lg:w-[160px]', 'format' => ['date', 'd-M-y, H:i']],
            ['key' => 'updated_at', 'label' => 'Updated At', 'class' => 'lg:w-[160px]', 'format' => ['date', 'd-M-y, H:i']],
        ];
    }

    public function products(): LengthAwarePaginator
    {
        return Product::query()
        ->orderBy(...array_values($this->sortBy))
        ->filterLike('name', $this->name)
        ->active($this->is_active)
        ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'products' => $this->products(),
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

    public function delete(Product $product): void
    {
        Gate::authorize('delete product');
        $product->delete();
        $this->success('Product has been deleted.');
    }

    public function export()
    {
        Gate::authorize('export product');

        $product = Product::orderBy('id','asc');
        $writer = SimpleExcelWriter::streamDownload('Product.xlsx');
        foreach ( $product->lazy() as $product ) {
            $writer->addRow([
                'id' => $product->id ?? '',
                'name' => $product->name ?? '',
                'is_active' => $product->is_active ?? '',
            ]);
        }
        return response()->streamDownload(function() use ($writer){
            $writer->close();
        }, 'Product.xlsx');
    }

    public function fake(): void
    {
        $count = 50;
        $products = [];

        for ($i = 0; $i < $count; $i++) {
            $products[] = [
                'code' => fake()->unique()->bothify('SKU-####'),
                'name' => fake()->unique()->words(rand(2, 4), true),
                'description' => fake()->paragraphs(rand(2, 4), true),
                'price' => fake()->randomFloat(2, 10, 999),
                // 'stock' => fake()->numberBetween(0, 100),
                'is_active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('products')->insert($products);
    }
}; ?>

<div>
    {{-- HEADER --}}
    <x-header title="Product" separator progress-indicator>
        <x-slot:actions>
            @can('export product')
            <x-button label="Export" wire:click="export" spinner="export" icon="o-arrow-down-tray" />
            @endcan
            @can('import product')
            <x-button label="Import" link="{{ route('product.import') }}" icon="o-arrow-up-tray" />
            @endcan
            <x-button label="Filters" @click="$wire.drawer = true" icon="o-funnel" badge="{{ $filterCount }}" />
            @env('local')
            <x-button label="Generate Fake Data" wire:click="fake" spinner="fake" class="btn-primary" />
            @endenv
            @can('create product')
            <x-button label="Create" link="{{ route('product.create') }}" icon="o-plus" class="btn-primary" />
            @endcan
        </x-slot:actions>
    </x-header>

    {{-- TABLE --}}
    <x-card wire:loading.class="bg-slate-200/50 text-slate-400" class="border border-base-300">
        <x-table
            :headers="$headers"
            :rows="$products"
            :sort-by="$sortBy"
            with-pagination
            show-empty-text
            per-page="perPage"
            :link="auth()->user()->can('update product') ? route('product.edit', ['product' => '[id]']) : null"
        >
            @scope('cell_is_active', $product)
            <x-active-badge :status="$product->is_active" />
            @endscope
            @scope('actions', $product)
            <div class="flex gap-0">
                @can('delete product')
                <x-button wire:click="delete({{ $product->id }})" spinner="delete({{ $product->id }})" wire:confirm="Are you sure you want to delete this row?" icon="o-trash" class="btn-ghost btn-sm" />
                @endcan
                @can('update product')
                <x-button link="{{ route('product.edit', $product->id) }}" icon="o-pencil-square" class="btn-ghost btn-sm" />
                @endcan
            </div>
            @endscope
        </x-table>
    </x-card>

    {{-- FILTER DRAWER --}}
    <x-search-drawer>
        <x-input label="Name" wire:model="name" />
        <x-select label="Active" wire:model="is_active" :options="\App\Enums\ActiveStatus::toSelect()" placeholder="-- All --" />
    </x-search-drawer>
</div>
