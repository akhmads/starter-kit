<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use App\Models\Address;

new class extends Component {
    use Toast;

    public $contact_id;
    public $address;

    public function mount(): void
    {

    }

    public function save(): void
    {
        $data = $this->validate([
            'contact_id' => 'required|exists:contacts,id',
            'address' => 'nullable',
        ]);

        Address::create($data);

        $this->success('Success','Address successfully created.', redirectTo: route('address.index'));
    }
}; ?>

<div>
    <x-header title="Create Address" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('address.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save">
        <x-card class="border border-base-300">
            <div class="space-y-4">
                <div class="form-control w-full">
                    <div class="label">
                        <span class="label-text">Contact</span>
                    </div>
                    <x-remote-select
                        wire:model="contact_id"
                        option_value="id"
                        option_label="label"
                        :remote="route('api.contacts.index')"
                        placeholder="Select Contact"
                        class="w-full"
                        clearable
                    />
                    @error('contact_id') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                <x-textarea label="Address" wire:model="address" class="field-sizing-content" />
            </div>
        </x-card>
        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('address.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
