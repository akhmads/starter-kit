<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use App\Models\Product;

new class extends Component {
    use Toast;

    public $code;
    public $name;
    public $description;
    public $price;
    public $is_active = false;

    public function mount(): void
    {
        Gate::authorize('create product');
    }

    public function save(): void
    {
        $data = $this->validate([
            'code' => ['required', Rule::unique(Product::class)],
            'name' => 'required',
            'description' => 'nullable',
            'price' => 'nullable',
            'is_active' => 'boolean',
        ]);

        Product::create($data);

        $this->success('Product successfully created.', redirectTo: route('product.index'));
    }
}; ?>

<div>
    <x-header title="Create Product" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('product.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save">
        <x-grid cols="2">
            <x-card class="border border-base-300">
                <div class="space-y-4">
                    <x-input label="Code" wire:model="code" class="input-border" />
                    <x-input label="Name" wire:model="name" />
                    <x-textarea label="Description" wire:model="description" />
                    <x-input label="Price" wire:model="price" x-mask:dynamic="$money($input, '.', '')" />
                    <x-toggle label="Active" wire:model="is_active" />
                </div>
            </x-card>
        </x-grid>
        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('product.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
