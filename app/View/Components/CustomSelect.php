<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomSelect extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $optionValue = 'id',
        public string $optionLabel = 'name',
        public ?string $remote = null,
        public array $options = [],
        public ?array $initialValue = null,
        public string $placeholder = 'Select an option...',
        public bool $clearable = false,
        public bool $disabled = false,
        public bool $searchable = true,
        public string $debounce = '300ms',
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.custom-select');
    }
}
