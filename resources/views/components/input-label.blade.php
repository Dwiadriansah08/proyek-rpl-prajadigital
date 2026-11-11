@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-[0.8125rem] font-medium text-[#1D1D1F] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
