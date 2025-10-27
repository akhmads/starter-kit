<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use App\Models\Order;

new class extends Component {
    use Toast;

    public $code;
    public $date;
    public $note;

    public function mount(): void
    {
        Gate::authorize('create order');
    }

    public function save(): void
    {
        $data = $this->validate([
            'code' => ['required', Rule::unique(Order::class)],
            'date' => 'required|date',
            'note' => 'nullable',
        ]);

        Order::create($data);

        $this->success('Success','Order successfully created.', redirectTo: route('order.index'));
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

        </div>
        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('order.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
