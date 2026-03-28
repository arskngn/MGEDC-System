@props([
    'name',
    'id',
    'value' => '',
    'required' => false,
    'placeholder' => null,
    'errorName' => null,
])

@php
    $errorName = $errorName ?? $name;
    $googleKey = config('services.google.maps_api_key') ?? '';
@endphp

<div class="relative" data-address-autocomplete-wrapper>
    <input
        type="text"
        id="{{ $id }}"
        name="{{ $name }}"
        @unless($attributes->has('x-model'))
            value="{{ $value }}"
        @endunless
        data-address-autocomplete
        data-google-key="{{ $googleKey }}"
        @if($required) required @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        maxlength="2000"
        autocomplete="off"
        {{ $attributes->class('w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm') }}
    />
    <p class="mt-1 text-[11px] text-gray-500">
        Start typing for suggestions. Results favor the Philippines; other countries are included.
        @if($googleKey === '')
            <span class="text-amber-700">Using OpenStreetMap search. Set <code class="text-xs">GOOGLE_MAPS_API_KEY</code> for Google Places (richer street-level data where available).</span>
        @endif
    </p>
    @error($errorName)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
