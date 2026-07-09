@props([
    'height' => 'h-10',
])

<img
    src="https://mwasalat.om/ar/images/logo-new.png"
    alt="{{ config('app.name', 'LearnHost') }}"
    {{ $attributes->merge(['class' => $height.' w-auto object-contain']) }}
/>
