@props([
    'label',
    'value',
    'description' => null,
    'borderClass' => 'border-slate-100',
    'valueClass' => 'text-slate-950',
])

<div {{ $attributes->merge(['class' => "rounded-3xl border {$borderClass} bg-white p-4 shadow-sm"]) }}>
    <p class="text-xs font-black uppercase tracking-wider text-slate-400">{{ $label }}</p>
    <p class="mt-2 wrap-break-word text-3xl font-black {{ $valueClass }}">{{ $value }}</p>

    @if($description)
    <p class="mt-1 text-xs font-bold text-slate-500">{{ $description }}</p>
    @endif

    @if(!$slot->isEmpty())
    <div class="mt-3">
        {{ $slot }}
    </div>
    @endif
</div>
