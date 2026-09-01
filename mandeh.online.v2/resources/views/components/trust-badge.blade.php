@props(['score', 'verified' => false, 'size' => 'md'])

@php
    $color = 'text-on-surface-variant';
    $label = 'Unverified';
    $icon = 'info';

    if ($verified) {
        $color = 'text-secondary';
        $label = 'Verified Presence';
        $icon = 'verified';
    } elseif ($score >= 80) {
        $color = 'text-success';
        $label = 'High Trust';
        $icon = 'gpp_good';
    } elseif ($score >= 50) {
        $color = 'text-primary';
        $label = 'Trusted';
        $icon = 'check_circle';
    }
@endphp

<div {{ $attributes->merge(['class' => "flex items-center gap-xs $color font-label-sm uppercase tracking-widest"]) }}>
    <span class="material-symbols-outlined text-[{{ $size === 'lg' ? '24px' : '18px' }}] {{ $verified ? 'material-symbols-fill' : '' }}">
        {{ $icon }}
    </span>
    <span>{{ $label }}</span>
</div>
