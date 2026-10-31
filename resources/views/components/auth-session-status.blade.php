@props(['status'])

@if ($status)
    <p {{ $attributes->merge(['class' => 'flex items-start gap-2 bg-succes-doux p-3.5 text-[0.95rem] text-succes']) }} role="status">
        <x-icone nom="coche" class="mt-0.5 size-4 shrink-0" /> {{ $status }}
    </p>
@endif
