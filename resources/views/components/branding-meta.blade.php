@props([
    'title' => null,
    'suffix' => null,
    'description' => null,
])

@php
    $pageTitle = $title ?? $brand->appName();
    $pageSuffix = $suffix ?? $brand->tagline();
    $pageDescription = $description ?? ('Aplikasi Informasi dan Donasi ' . $brand->mosqueName());
@endphp

<title>{{ $pageTitle }} - {{ $pageSuffix }}</title>
<meta name="description" content="{{ $pageDescription }}">
<meta name="theme-color" content="{{ $brand->primaryColor() }}">
<meta name="apple-mobile-web-app-title" content="{{ $brand->appName() }}">

<link rel="icon" type="image/png" href="{{ $brand->faviconUrl() }}">
<link rel="shortcut icon" href="{{ $brand->faviconUrl() }}">
<link rel="apple-touch-icon" href="{{ $brand->logoIconUrl() }}">
