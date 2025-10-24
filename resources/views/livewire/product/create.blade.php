<?php

use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use App\Models\Product;

new class extends Component {
    use Toast;

    public $code = '';
    public $name = '';
    public bool $is_active = false;

    public function mount(): void
    {
        Gate::authorize('create product');
    }

    public function save(): void
    {
        $data = $this->validate([
            'code' => 'required|unique:product,code',
            'name' => 'required',
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
        <x-card>
            <div class="space-y-4">
                <x-input label="Code" wire:model="code" />
                <x-input label="Name" wire:model="name" />
                <x-toggle label="Active" wire:model="is_active" />
            </div>
        </x-card>
        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('product.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
