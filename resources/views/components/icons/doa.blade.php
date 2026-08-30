@props(['class' => 'w-5 h-5', 'filled' => false])
@if($filled)
<svg {{ $attributes->merge(['class' => $class]) }} fill="currentColor" viewBox="0 0 24 24"><path d="M12 1C9.5 1 8 3 8 5.5c0 1.5.5 2.5 1.5 3.5L4 16c-1 1-1 2.5 0 3.5s2.5 1 3.5 0l2-2c.3.6.8 1.2 1.5 1.5v3a1 1 0 002 0v-3c.7-.3 1.2-.9 1.5-1.5l2 2c1 1 2.5 1 3.5 0s1-2.5 0-3.5l-5.5-7C15.5 8 16 7 16 5.5 16 3 14.5 1 12 1zm0 2c1.4 0 2 1.2 2 2.5S13.4 8 12 8s-2-1.2-2-2.5S10.6 3 12 3z"/></svg>
@else
<svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v3m0 12v3"/></svg>
@endif
