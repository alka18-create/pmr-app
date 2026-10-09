@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
])

@php
    $pageTitle = $title ? $title.' · '.config('app.name') : config('app.name');
    $pageDescription = $description ?: config('app.description', config('app.name'));
    $pageUrl = $url ?: url()->current();
    $pageImage = $image ? asset('storage/'.$image) : null;
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<link rel="canonical" href="{{ $pageUrl }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $pageUrl }}">
@if ($pageImage)
    <meta property="og:image" content="{{ $pageImage }}">
@endif
<meta name="twitter:card" content="{{ $pageImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
