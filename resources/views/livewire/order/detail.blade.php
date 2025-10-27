<?php

use App\Helpers\Cast;
use Mary\Traits\Toast;
use App\Models\Product;
use Livewire\Volt\Component;
use App\Traits\ProductChoice;
use Illuminate\Support\Collection;

new class extends Component {
    use Toast, ProductChoice;

    public Collection $items;
    public string $product_id;
    public string $price;
    public string $qty;

    public string $mode = '';
    public string $selected = '';
    public bool $drawer = false;

    public function mount(Collection $items): void
    {
        $this->items = $items;
        $this->date = date('Y-m-d');
    }

    public function with(): array
    {
        $product_id = $this->items->pluck('product_id')->toArray();
        $products = Product::query()
            ->select('id', 'name')
            ->whereIn('id', $product_id)
            ->get()
            ->keyBy('id');

        return ['products' => $products];
    }

    public function clearForm(): void
    {
        $this->selected = '';
        $this->product_id = '';
        $this->price = '';
        $this->qty = '';
        $this->resetValidation();
    }

    public function addItem()
    {
        $this->clearForm();
        $this->mode = 'add';
        $this->drawer = true;
    }

    public function editItem(string $id)
    {
        $this->clearForm();

        $this->selected = $id;
        $target = $this->items->get($id);

        $this->product_id = $target->product_id;
        $this->price = $target->price;
        $this->qty = $target->qty;

        $this->mode = 'edit';
        $this->drawer = true;
    }

    public function saveItem(int $new = 0)
    {
        $data = $this->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:1',
        ]);

        if ($this->mode == 'add') {
            if ($this->items->pluck('product_id')->contains($this->product_id)) {
                $this->addError('product_id', 'Product already added.');
                return;
            }
        }

        if ($this->mode == 'add')
        {
            // $this->items->push((object)[
            //     'product_id' => $this->product_id,
            //     'price' => $this->price,
            //     'qty' => $this->qty,
            //     'subtotal' => $this->price * $this->qty,
            // ]);
            $this->items->put(uniqid(), (object)[
                'product_id' => $this->product_id,
                'price' => $this->price,
                'qty' => $this->qty,
                'subtotal' => $this->price * $this->qty,
            ]);
        }

        if ($this->mode == 'edit')
        {
            $this->items->transform(function ($data, $key) {
                if ($key == $this->selected) {
                    $data->product_id = $this->product_id;
                    $data->price = $this->price;
                    $data->qty = $this->qty;
                    $data->subtotal = $this->price * $this->qty;
                }
                return $data;
            });
        }

        $this->dispatch('items-updated', items: $this->items);

        if ($new == 1)
        {
            $this->clearForm();
        }
        else
        {
            $this->drawer = false;
            $this->success('Item has been created.');
        }
    }

    public function deleteItem(string $id)
    {
        $this->items->forget($id);
        $this->dispatch('items-updated', items: $this->items);
        $this->success('Item has been deleted.');
    }
}; ?>
<div>
    <x-card title="Items" separator>
        <x-slot:menu>
            <x-button label="Add Item" icon="o-plus" wire:click="addItem" spinner="addItem" class="bg-base-200" />
        </x-slot:menu>

        <div class="overflow-x-auto">
            <table class="table">
            <thead>
            <tr>
                <th>Product</td>
                <th class="text-right lg:w-40">Price</th>
                <th class="text-right lg:w-40">Qty</th>
                <th class="text-right lg:w-40">Subtotal</th>
                <th class="lg:w-16"></th>
            </tr>
            </thead>
            <tbody>

            @forelse ($items as $key => $item)
            <tr
                wire:loading.class="cursor-wait"
                class="divide-x divide-gray-200 dark:divide-gray-900 hover:bg-yellow-50 dark:hover:bg-gray-800 cursor-pointer"
            >
                <td wire:click="editItem('{{ $key }}')">{{ $products[$item->product_id]->name ?? 'Unknown' }}</td>
                <td wire:click="editItem('{{ $key }}')" class="text-right">{{ Cast::money($item->price) }}</td>
                <td wire:click="editItem('{{ $key }}')" class="text-right">{{ Cast::money($item->qty) }}</td>
                <td wire:click="editItem('{{ $key }}')" class="text-right">{{ Cast::money($item->subtotal) }}</td>
                <td>
                    <div class="flex items-center">
                        <x-button icon="o-x-mark" wire:click="deleteItem('{{ $key }}')" spinner="deleteItem('{{ $key }}')" wire:confirm="Are you sure ?" class="btn-xs btn-ghost text-xs -m-1 text-error" />
                    </div>
                </td>
            </tr>
            @empty
            <tr class="divide-x divide-gray-200 dark:divide-gray-900 hover:bg-yellow-50 dark:hover:bg-gray-800">
                <td colspan="10" class="text-center">No record found.</td>
            </tr>
            @endforelse

            <tr>
                <td class="text-right" colspan="3">Total</td>
                <td class="text-right">{{ Cast::money($items->sum('subtotal')) }}</td>
            </tr>
            </tbody>
            </table>
        </div>
    </x-card>

    {{-- DRAWER --}}
    <x-drawer wire:model="drawer" title="Update Item" right separator with-close-button class="lg:w-1/3">
        {{-- <x-form wire:submit="saveItem(0)"> --}}
            <div class="grid gap-5">
                <x-choices
                    label="Product"
                    wire:model="product_id"
                    :options="$productChoice"
                    search-function="searchProduct"
                    option-label="name"
                    single
                    searchable
                    clearable
                    placeholder="-- Select --"
                    {{-- :disabled="!$open" --}}
                />
                <x-input label="Price" wire:model="price" x-mask:dynamic="$money($input,'.','')" />
                <x-input label="Qty" wire:model="qty" x-mask:dynamic="$money($input,'.','')" />
            </div>
            <x-slot:actions>
                <x-button label="Save & create another" wire:click="saveItem(1)" spinner="saveItem(1)" />
                <x-button label="Save" icon="o-paper-airplane" wire:click="saveItem(0)" spinner="saveItem(0)" type="button" class="" class="btn-primary" />
            </x-slot:actions>
        {{-- </x-form> --}}
    </x-drawer>
</div>
