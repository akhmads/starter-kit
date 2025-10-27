<?php

use Spatie\LivewireFilepond\WithFilePond;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use App\Models\Product;

new class extends Component {
    use Toast, WithFilePond;

    public $code;
    public $name;
    public $description;
    public $price;
    public $is_active;
    public $image;

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
            'image' => 'required|mimetypes:image/jpg,image/jpeg,image/png|max:3000',
        ]);

        if ($this->image) {
            $url = $this->image->store('images', 'public');
            $data['image'] =  "/storage/".$url;
        }

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
        <x-grid cols="5">
            <div class="col-span-3">
                <x-card class="border border-base-300">
                    <div class="space-y-4">
                        <x-input label="Code" wire:model="code" class="input-border" />
                        <x-input label="Name" wire:model="name" />
                        <x-textarea label="Description" wire:model="description" class="field-sizing-content" />
                        <x-input label="Price" wire:model="price" x-mask:dynamic="$money($input, '.', '')" />
                        <x-select label="Is Active" :options="\App\Enums\ActiveStatus::toSelect()" wire:model="is_active" placeholder="-- Select --" />
                        {{-- <x-toggle label="Active" wire:model="is_active" /> --}}
                    </div>
                </x-card>
            </div>
            <div class="col-span-2 space-y-4">
                <x-card class="border border-base-300">
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-semibold mb-3">Image</div>
                            <x-filepond::upload wire:model="image" />
                            @error('image') <span class="text-xs text-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </x-card>
            </div>
        </x-grid>
        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('product.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
