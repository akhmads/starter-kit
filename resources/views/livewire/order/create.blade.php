<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Mary\Traits\Toast;
use App\Models\Order;

new class extends Component {
    use Toast;

    public $code;
    public $date;
    public $note;
    public Collection $items;

    public function mount(): void
    {
        Gate::authorize('create order');
        $this->items = collect([]);
    }

    public function save(): void
    {
        $data = $this->validate([
            'code' => ['required', Rule::unique(Order::class)],
            'date' => 'required|date',
            'note' => 'nullable',
        ]);

        if (empty($this->items) || count($this->items) == 0) {
            $this->addError('items', 'At least one item is required.');
            return;
        }

        $data['total'] = $this->items->sum('subtotal');
        $order = Order::create($data);

        $this->items->each(function ($item, $key) use ($order) {
            $order->details()->create([
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'qty' => $item['qty'],
                'subtotal' => $item['subtotal'],
            ]);
        });

        $this->success('Success','Order successfully created.', redirectTo: route('order.index'));
    }

    #[On('items-updated')]
    public function itemsUpdated($items)
    {
        $this->items = collect($items);
    }
}; ?>

<div>
    <x-header title="Create Order" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('order.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save">
        <div class="space-y-4">
            <x-card class="border border-base-300">
                <x-grid cols="2" class="space-y-4">
                    <x-input label="Code" wire:model="code" />
                    <x-input label="Date" wire:model="date" type="date" />
                    <div class="col-span-full">
                        <x-textarea label="Note" wire:model="note" class="field-sizing-content" />
                    </div>
                    {{-- <x-input label="Price" wire:model="price" x-mask:dynamic="$money($input, '.', '')" />
                    <x-select label="Is Active" :options="\App\Enums\ActiveStatus::toSelect()" wire:model="is_active" placeholder="-- Select --" /> --}}
                </x-grid>
            </x-card>

            <div class="overflow-x-auto">
                @error('items')
                <div class="flex justify-center">
                    <span class="text-red-500 text-sm p-1">{{ $message }}</span>
                </div>
                @enderror
                <livewire:order.detail :items="$items" />
            </div>
        </div>
        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('order.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
