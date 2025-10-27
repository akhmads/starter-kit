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

    public Order $order;

    public $code;
    public $date;
    public $note;
    public Collection $items;

    public function mount(): void
    {
        Gate::authorize('update order');
        $this->fill($this->order);
        $this->items = collect([]);

        foreach ($this->order->details as $item){
            $this->items->put(uniqid(), (object)[
                'product_id' => $item->product_id,
                'price' => $item->price,
                'qty' => $item->qty,
                'subtotal' => $item->subtotal,
            ]);
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'code' => ['required', Rule::unique(Order::class)->ignore($this->order)],
            'date' => 'required|date',
            'note' => 'nullable',
        ]);

        // Validasi items secara terpisah
        if (empty($this->items) || count($this->items) == 0) {
            $this->addError('items', 'At least one item is required.');
            return;
        }

        $this->order->details()->delete();
        $this->items->each(function ($item, $key) {
            $this->order->details()->create([
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'qty' => $item['qty'],
                'subtotal' => $item['subtotal'],
            ]);
        });

        $data['total'] = $this->items->sum('subtotal');
        $this->order->update($data);

        $this->success('Success','Order successfully updated.', redirectTo: route('order.index'));
    }

    #[On('items-updated')]
    public function itemsUpdated($items)
    {
        $this->items = collect($items);
    }
}; ?>

<div>
    <x-header title="Update Order" separator>
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
