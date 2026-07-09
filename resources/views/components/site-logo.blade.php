@props([
    'alt' => null,
])

<img
    src="{{ \App\Models\Setting::logoUrl() }}"
    alt="{{ $alt ?? \App\Models\Setting::siteName() }}"
    {{ $attributes->class(['w-auto object-contain']) }}
/>
