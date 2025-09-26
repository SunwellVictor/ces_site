@php
    $meta = app(\App\Services\Meta::class)->renderHead();
@endphp

{{-- Basic Meta Tags --}}
@if($meta['title'])
<title>{{ $meta['title'] }}</title>
@endif

@if($meta['description'])
<meta name="description" content="{{ $meta['description'] }}">
@endif

{{-- Canonical URL --}}
@if($meta['canonical'])
<link rel="canonical" href="{{ $meta['canonical'] }}">
@endif

{{-- Robots Meta --}}
@if($meta['robots'])
<meta name="robots" content="{{ $meta['robots'] }}">
@elseif($meta['noindex'])
<meta name="robots" content="noindex, nofollow">
@endif

{{-- Open Graph Meta Tags --}}
@if($meta['og'])
    @foreach($meta['og'] as $property => $content)
        @if($content)
<meta property="og:{{ $property }}" content="{{ $content }}">
        @endif
    @endforeach
@endif

{{-- Twitter Meta Tags --}}
@if($meta['twitter'])
    @foreach($meta['twitter'] as $name => $content)
        @if($content)
<meta name="twitter:{{ $name }}" content="{{ $content }}">
        @endif
    @endforeach
@endif