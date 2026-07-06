@props([
    'title',
    'value' => null,
    'subtitle' => null,
    'withBorder' => true,
    'borderClass' => 'border-slate-100',
    'backgroundClass' => 'bg-white',
    'paddingClass' => 'px-3 py-2',
    'titleClass' => 'text-slate-900',
    'badgeClass' => 'bg-blue-50 text-blue-700',
    'badgeTextClass' => 'text-xs',
])

@php
    $borderClasses = $withBorder ? trim("border {$borderClass}") : '';
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl {$borderClasses} {$backgroundClass} {$paddingClass} shadow-sm"]) }}>
    <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-3 text-sm font-black">
        <span class="wrap-break-word min-w-0 leading-snug {{ $titleClass }}">{{ $title }}</span>

        @if($value !== null)
        <span class="shrink-0 rounded-full px-2.5 py-1 font-black {{ $badgeTextClass }} {{ $badgeClass }}">
            {{ $value }}
        </span>
        @endif
    </div>

    @if($subtitle)
    <p class="mt-1 wrap-break-word min-w-0 text-xs font-bold leading-snug text-slate-500">{{ $subtitle }}</p>
    @endif

    @if(!$slot->isEmpty())
    <div class="mt-2">
        {{ $slot }}
    </div>
    @endif
</div>
