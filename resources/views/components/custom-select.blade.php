@props([
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'remote' => null,
    'options' => [],
    'initialValue' => null,
    'placeholder' => 'Select an option...',
    'clearable' => false,
    'disabled' => false,
    'searchable' => true,
    'debounce' => '300ms',
])

@php
    $wireModel = $attributes->wire('model')->value();
    $isLive = $attributes->wire('model')->hasModifier('live');
    $uniqueId = 'select-' . uniqid();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        loading: false,
        options: @js($options),
        selectedValue: @entangle($wireModel){{ $isLive ? '.live' : '' }},
        selectedLabel: @js($initialValue[$optionLabel] ?? ''),
        highlightedIndex: -1,
        remote: @js($remote),
        debounceTimer: null,
        clearable: @js($clearable),
        disabled: @js($disabled),
        searchable: @js($searchable),
        optionValue: @js($optionValue),
        optionLabel: @js($optionLabel),

        get filteredOptions() {
            if (this.remote) {
                return this.options;
            }
            if (!this.searchable || this.search === '') {
                return this.options;
            }
            return this.options.filter(option =>
                String(option[this.optionLabel] || '')
                    .toLowerCase()
                    .includes(this.search.toLowerCase())
            );
        },

        get displayValue() {
            if (this.selectedLabel) {
                return this.selectedLabel;
            }
            if (this.selectedValue) {
                const option = this.options.find(opt => opt[this.optionValue] == this.selectedValue);
                return option ? option[this.optionLabel] : '';
            }
            return '';
        },

        async fetchOptions(query = '') {
            if (!this.remote) return;

            this.loading = true;

            try {
                const url = new URL(this.remote);
                if (query) {
                    url.searchParams.set('q', query);
                }

                const response = await fetch(url.toString());
                const data = await response.json();

                // Support both direct array and nested data structure
                this.options = Array.isArray(data) ? data : (data.data || []);
            } catch (error) {
                console.error('Error fetching options:', error);
                this.options = [];
            } finally {
                this.loading = false;
            }
        },

        handleSearch() {
            if (!this.remote) return;

            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.fetchOptions(this.search);
            }, parseInt(@js($debounce)));
        },

        selectOption(option) {
            this.selectedValue = option[this.optionValue];
            this.selectedLabel = option[this.optionLabel];
            this.search = '';
            this.open = false;
            this.highlightedIndex = -1;
        },

        clearSelection() {
            if (this.disabled) return;
            this.selectedValue = null;
            this.selectedLabel = '';
            this.search = '';
            this.highlightedIndex = -1;
        },

        toggleDropdown() {
            if (this.disabled) return;

            this.open = !this.open;

            if (this.open) {
                this.search = '';
                if (this.remote && this.options.length === 0) {
                    this.fetchOptions();
                }
                this.$nextTick(() => {
                    if (this.searchable) {
                        this.$refs.searchInput?.focus();
                    }
                });
            }
        },

        handleKeydown(event) {
            if (this.disabled) return;

            const filtered = this.filteredOptions;

            switch(event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    if (!this.open) {
                        this.open = true;
                        if (this.remote && this.options.length === 0) {
                            this.fetchOptions();
                        }
                    } else {
                        this.highlightedIndex = (this.highlightedIndex + 1) % filtered.length;
                        this.scrollToHighlighted();
                    }
                    break;

                case 'ArrowUp':
                    event.preventDefault();
                    if (this.open) {
                        this.highlightedIndex = this.highlightedIndex <= 0
                            ? filtered.length - 1
                            : this.highlightedIndex - 1;
                        this.scrollToHighlighted();
                    }
                    break;

                case 'Enter':
                    event.preventDefault();
                    if (this.open && this.highlightedIndex >= 0 && filtered[this.highlightedIndex]) {
                        this.selectOption(filtered[this.highlightedIndex]);
                    } else if (!this.open) {
                        this.toggleDropdown();
                    }
                    break;

                case 'Escape':
                    event.preventDefault();
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                    break;

                case 'Backspace':
                    if (!this.open && !this.search && this.clearable) {
                        event.preventDefault();
                        this.clearSelection();
                    }
                    break;
            }
        },

        scrollToHighlighted() {
            this.$nextTick(() => {
                const highlighted = this.$refs.dropdown?.querySelector('[data-highlighted=true]');
                if (highlighted) {
                    highlighted.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            });
        },

        init() {
            // Load initial options if not remote
            if (!this.remote && this.options.length === 0) {
                this.options = @js($options);
            }

            // Watch for external value changes
            this.$watch('selectedValue', (newValue) => {
                if (newValue && !this.selectedLabel) {
                    const option = this.options.find(opt => opt[this.optionValue] == newValue);
                    if (option) {
                        this.selectedLabel = option[this.optionLabel];
                    }
                } else if (!newValue) {
                    this.selectedLabel = '';
                }
            });
        }
    }"
    x-init="init()"
    @click.away="open = false"
    @keydown="handleKeydown($event)"
    class="relative w-full"
    wire:ignore
>
    <!-- Hidden input for form submission -->
    <input type="hidden" x-model="selectedValue" {{ $attributes->whereStartsWith('wire:model') }}>

    <!-- Select Button -->
    <button
        type="button"
        @click="toggleDropdown()"
        :disabled="disabled"
        :class="{ 'cursor-not-allowed opacity-60': disabled }"
        {{ $attributes->except(['wire:model', 'wire:model.live'])->class([
            'select select-bordered w-full flex items-center justify-between',
            'focus:outline-none focus:border-primary'
        ]) }}
    >
        <span
            class="flex-1 text-left truncate"
            :class="{ 'text-base-content/40': !displayValue }"
            x-text="displayValue || @js($placeholder)"
        ></span>

        <div class="flex items-center gap-2 ml-2">
            <!-- Loading Spinner -->
            <span
                x-show="loading"
                x-cloak
                class="loading loading-spinner loading-xs"
            ></span>

            <!-- Clear Button -->
            <button
                x-show="clearable && displayValue && !disabled"
                x-cloak
                type="button"
                @click.stop="clearSelection()"
                class="hover:text-error transition-colors"
                title="Clear selection"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Dropdown Arrow -->
            <svg
                class="w-5 h-5 transition-transform duration-200"
                :class="{ 'rotate-180': open }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg"
        x-ref="dropdown"
    >
        <!-- Search Input -->
        <div
            x-show="searchable"
            class="p-2 border-b border-base-300"
        >
            <input
                x-ref="searchInput"
                type="text"
                x-model="search"
                @input="handleSearch()"
                class="input input-sm input-bordered w-full"
                placeholder="Search..."
                @click.stop
                @keydown.stop
            >
        </div>

        <!-- Options List -->
        <div class="max-h-60 overflow-y-auto">
            <!-- Loading State -->
            <div
                x-show="loading && options.length === 0"
                class="p-4 text-center text-base-content/60"
            >
                <span class="loading loading-spinner loading-md"></span>
                <p class="mt-2 text-sm">Loading options...</p>
            </div>

            <!-- Options -->
            <template x-if="!loading || options.length > 0">
                <div>
                    <template x-for="(option, index) in filteredOptions" :key="option[optionValue]">
                        <button
                            type="button"
                            @click="selectOption(option)"
                            @mouseenter="highlightedIndex = index"
                            :data-highlighted="highlightedIndex === index"
                            class="w-full px-4 py-2 text-left hover:bg-base-200 transition-colors flex items-center justify-between"
                            :class="{
                                'bg-base-200': highlightedIndex === index,
                                'bg-primary/10 text-primary': selectedValue == option[optionValue]
                            }"
                        >
                            <span x-text="option[optionLabel]"></span>
                            <svg
                                x-show="selectedValue == option[optionValue]"
                                class="w-5 h-5 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    </template>

                    <!-- No Results -->
                    <div
                        x-show="filteredOptions.length === 0 && !loading"
                        class="p-4 text-center text-base-content/60 text-sm"
                    >
                        No options found
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
