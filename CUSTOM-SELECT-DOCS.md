# Custom Select Component - Laravel Livewire 3

Blade component select yang powerful dengan fitur lengkap menggunakan Livewire 3, Alpine.js, dan DaisyUI 5.

## Fitur

✅ Livewire 3 `wire:model` support
✅ `wire:model.live` dengan debounce
✅ Remote data dengan AJAX
✅ Remote search/searchable
✅ Keyboard navigation (Arrow Up/Down, Enter, Escape, Backspace)
✅ Loading animation (DaisyUI spinner)
✅ Clearable value
✅ Disabled state
✅ Design dengan DaisyUI 5
✅ Alpine.js untuk interactivity

## Instalasi

Component sudah siap digunakan di:

-   `app/View/Components/CustomSelect.php`
-   `resources/views/components/custom-select.blade.php`

## Penggunaan

### 1. Basic Usage (Static Options)

```blade
<x-custom-select
    wire:model="country_id"
    :options="[
        ['id' => 1, 'name' => 'Indonesia'],
        ['id' => 2, 'name' => 'Malaysia'],
        ['id' => 3, 'name' => 'Singapore'],
    ]"
    option_value="id"
    option_label="name"
    placeholder="Select country..."
/>
```

### 2. Remote Data (AJAX)

```blade
<x-custom-select
    wire:model="country_id"
    option_value="id"
    option_label="name"
    :remote="route('api.country.search')"
    placeholder="Select country..."
/>
```

### 3. With Initial Value

```blade
<x-custom-select
    wire:model="country_id"
    option_value="id"
    option_label="name"
    :remote="route('api.country.search')"
    :initial_value="['id' => '12', 'name' => 'United States']"
    placeholder="Select country..."
/>
```

### 4. Live Wire Model dengan Debounce

```blade
<x-custom-select
    wire:model.live="country_id"
    :remote="route('api.country.search')"
    debounce="500ms"
    clearable
/>
```

### 5. Dengan Custom Class

```blade
<x-custom-select
    wire:model="country_id"
    :remote="route('api.country.search')"
    class="h-15 select-primary"
    clearable
/>
```

### 6. Disabled State

```blade
<x-custom-select
    wire:model="country_id"
    :options="$countries"
    disabled
/>
```

### 7. Non-Searchable

```blade
<x-custom-select
    wire:model="status"
    :options="$statuses"
    :searchable="false"
/>
```

## Properties

| Property        | Type   | Default               | Description                       |
| --------------- | ------ | --------------------- | --------------------------------- |
| `wire:model`    | string | required              | Livewire model binding            |
| `option_value`  | string | 'id'                  | Key untuk value dari option       |
| `option_label`  | string | 'name'                | Key untuk label dari option       |
| `remote`        | string | null                  | URL endpoint untuk remote data    |
| `options`       | array  | []                    | Array options (untuk static data) |
| `initial_value` | array  | null                  | Initial selected value            |
| `placeholder`   | string | 'Select an option...' | Placeholder text                  |
| `clearable`     | bool   | false                 | Enable clear button               |
| `disabled`      | bool   | false                 | Disable select                    |
| `searchable`    | bool   | true                  | Enable search input               |
| `debounce`      | string | '300ms'               | Debounce time untuk search        |

## API Endpoint Example

Endpoint remote harus mengembalikan array object dengan format:

```json
{
    "data": [
        { "id": 1, "name": "Option 1" },
        { "id": 2, "name": "Option 2" }
    ]
}
```

Atau bisa juga langsung array:

```json
[
    { "id": 1, "name": "Option 1" },
    { "id": 2, "name": "Option 2" }
]
```

### Contoh Laravel Route & Controller

**routes/api.php**

```php
Route::get('/country/search', [CountryController::class, 'search'])->name('api.country.search');
```

**CountryController.php**

```php
public function search(Request $request)
{
    $query = $request->input('q', '');

    $countries = Country::query()
        ->when($query, function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })
        ->limit(50)
        ->get(['id', 'name']);

    return response()->json($countries);
}
```

## Keyboard Navigation

-   **Arrow Down**: Buka dropdown / Navigasi ke bawah
-   **Arrow Up**: Navigasi ke atas
-   **Enter**: Pilih option yang di-highlight / Buka dropdown
-   **Escape**: Tutup dropdown
-   **Backspace**: Clear selection (jika clearable=true)

## Contoh Livewire Component

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Country;

class UserForm extends Component
{
    public $country_id;
    public $initialCountry;

    public function mount($userId = null)
    {
        if ($userId) {
            $user = User::find($userId);
            $this->country_id = $user->country_id;

            // Set initial value
            if ($user->country) {
                $this->initialCountry = [
                    'id' => $user->country->id,
                    'name' => $user->country->name
                ];
            }
        }
    }

    public function updatedCountryId($value)
    {
        // Dipanggil ketika country_id berubah (jika pakai wire:model.live)
        $this->validate([
            'country_id' => 'required|exists:countries,id'
        ]);
    }

    public function save()
    {
        $this->validate([
            'country_id' => 'required|exists:countries,id'
        ]);

        // Save logic...
    }

    public function render()
    {
        return view('livewire.user-form');
    }
}
```

**View (livewire/user-form.blade.php)**

```blade
<div>
    <form wire:submit="save">
        <div class="form-control">
            <label class="label">
                <span class="label-text">Country</span>
            </label>

            <x-custom-select
                wire:model.live="country_id"
                :remote="route('api.country.search')"
                :initial_value="$initialCountry"
                placeholder="Select country..."
                clearable
                class="h-12"
            />

            @error('country_id')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary mt-4">
            Save
        </button>
    </form>
</div>
```

## Styling dengan DaisyUI

Component menggunakan class DaisyUI:

-   `select` - Base select styling
-   `select-bordered` - Border styling
-   `select-primary` - Primary color variant
-   `loading loading-spinner` - Loading animation
-   `btn` - Button styling

Anda bisa override dengan class custom atau DaisyUI variants.

## Tips & Tricks

### 1. Custom Option Value/Label Keys

```blade
<x-custom-select
    wire:model="user_id"
    option_value="uuid"
    option_label="full_name"
    :options="$users"
/>
```

### 2. Cascade Select (Dependent Dropdown)

```blade
<!-- Parent -->
<x-custom-select
    wire:model.live="province_id"
    :options="$provinces"
    placeholder="Select province..."
/>

<!-- Child (akan reload ketika province_id berubah) -->
<x-custom-select
    wire:model="city_id"
    :remote="route('api.cities', ['province_id' => $province_id])"
    :key="$province_id"
    placeholder="Select city..."
/>
```

### 3. Multiple Select di Form

```blade
<div class="grid grid-cols-2 gap-4">
    <x-custom-select
        wire:model="category_id"
        :options="$categories"
        placeholder="Category..."
    />

    <x-custom-select
        wire:model="status"
        :options="$statuses"
        placeholder="Status..."
    />
</div>
```

## Browser Support

-   Chrome/Edge (latest)
-   Firefox (latest)
-   Safari (latest)

## Dependencies

-   Laravel 10+
-   Livewire 3
-   Alpine.js 3
-   DaisyUI 5
-   TailwindCSS 3

## Troubleshooting

### Component tidak muncul

Pastikan Alpine.js dan Livewire sudah diload di layout:

```blade
@livewireStyles
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@livewireScripts
```

### Remote search tidak bekerja

Periksa:

1. Route API sudah benar
2. Endpoint mengembalikan JSON dengan format yang benar
3. CORS sudah dikonfigurasi (jika API di domain berbeda)

### Wire:model tidak sinkron

Gunakan `wire:model.live` untuk real-time sync, atau pastikan ada action yang trigger sync (submit form, etc).
