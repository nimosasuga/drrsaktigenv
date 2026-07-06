@props([
    'status' => null,
    'label' => null,
    'size' => 'sm',
])

@php
    $rawStatus = trim((string) ($status ?? $label ?? '-'));
    $normalized = strtoupper(str_replace(['_', '-'], ' ', $rawStatus));
    $normalized = preg_replace('/\s+/', ' ', $normalized);

    $display = $label ?: match (true) {
        $normalized === 'RFU' => 'RFU',
        in_array($normalized, ['B/D', 'BD', 'BREAKDOWN'], true) => 'Breakdown',
        in_array($normalized, ['MONITORING', 'STANDBY'], true) => 'Monitoring',
        $normalized === 'WAITING PART' => 'Waiting Part',
        in_array($normalized, ['PM SUDAH ADA', 'SUDAH PM', 'PM DONE'], true) => 'Sudah PM',
        in_array($normalized, ['BELUM PM', 'PM PENDING'], true) => 'Belum PM',
        default => $rawStatus !== '' ? $rawStatus : '-',
    };

    $toneClass = match (true) {
        $normalized === 'RFU' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
        in_array($normalized, ['B/D', 'BD', 'BREAKDOWN'], true) => 'border-red-100 bg-red-50 text-red-700',
        in_array($normalized, ['MONITORING', 'STANDBY'], true) => 'border-yellow-100 bg-yellow-50 text-yellow-700',
        $normalized === 'WAITING PART' => 'border-amber-100 bg-amber-50 text-amber-700',
        in_array($normalized, ['PM SUDAH ADA', 'SUDAH PM', 'PM DONE'], true) => 'border-cyan-100 bg-cyan-50 text-cyan-700',
        in_array($normalized, ['BELUM PM', 'PM PENDING'], true) => 'border-rose-100 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-50 text-slate-700',
    };

    $sizeClass = $size === 'xs'
        ? 'px-2 py-1 text-[10px]'
        : 'px-2.5 py-1 text-xs';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center rounded-full border {$sizeClass} font-black leading-none {$toneClass}"]) }}>
    {{ $display }}
</span>
