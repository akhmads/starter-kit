<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use App\Models\Address;

new class extends Component {
    use Toast;

    public Address $address;

    public $contact_id;
    public $_address;

    public function mount(): void
    {
        $this->contact_id = $this->address->contact_id;
        $this->_address = $this->address->address;
    }

    public function save(): void
    {
        $data = $this->validate([
            'contact_id' => 'required|exists:contacts,id',
            '_address' => 'required|string',
        ]);

        $data['address'] = $data['_address'];
        unset($data['_address']);

        $this->address->update($data);

        $this->success('Success','Address successfully updated.', redirectTo: route('address.index'));
    }
}; ?>

<div>
    <x-header title="Update Address" separator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('address.index') }}" icon="o-arrow-uturn-left" />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save">
        <x-grid cols="5">
            <div class="col-span-3">
                <x-card class="border border-base-300">
                    <div class="space-y-4">
                        <x-remote-select
                            label="Contact"
                            wire:model="contact_id"
                            option_value="id"
                            option_label="label"
                            :remote="route('api.contacts.index')"
                            :initial_value="['id' => $address->contact_id, 'label' => '#'.$address->contact->id.' - '.$address->contact->name]"
                            placeholder="Select Contact"
                            class="w-full"
                            clearable
                        />
                        {{-- <x-offline-select
                            label="Contact"
                            wire:model.live="contact_id"
                            option_value="id"
                            option_label="name"
                            :options="\App\Models\Contact::all()->map(fn($c) => ['id' => $c->id, 'name' => '#'.$c->id.' - '.$c->name])->toArray()"
                            :initial_value="['id' => $address->contact_id, 'name' => '#'.$address->contact->id.' - '.$address->contact->name]"
                            placeholder="Select Contact"
                            class="w-full"
                            clearable
                        /> --}}
                        <x-textarea label="Address" wire:model="_address" class="field-sizing-content" />
                    </div>
                </x-card>
            </div>
        </x-grid>
        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('address.index') }}" />
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
