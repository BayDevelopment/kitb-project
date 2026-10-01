<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    {{-- ===== SEO DASAR ===== --}}
    <meta name="description"
        content="Aplikasi KITB untuk mengelola profil perusahaan, informasi kawasan, dan hubungan investor secara terpusat.">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="author" content="KITB">
    <meta name="application-name" content="KITB">
    <link rel="canonical" href="https://domain-anda.com">

    {{-- ===== OPEN GRAPH ===== --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="KITB">
    <meta property="og:title" content="KITB Web – Aplikasi Manajemen KITB">
    <meta property="og:description"
        content="Aplikasi KITB untuk mengelola profil perusahaan, informasi kawasan, dan hubungan investor secara terpusat.">
    <meta property="og:url" content="https://domain-anda.com">
    <meta property="og:image" content="https://domain-anda.com/og-image.png">
    <meta property="og:locale" content="id_ID">

    {{-- ===== TWITTER / X ===== --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="KITB Web – Aplikasi Manajemen KITB">
    <meta name="twitter:description"
        content="Aplikasi KITB untuk mengelola profil perusahaan, informasi kawasan, dan hubungan investor secara terpusat.">
    <meta name="twitter:image" content="https://domain-anda.com/og-image.png">

    {{-- ===== BROWSER ===== --}}
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b1728" media="(prefers-color-scheme: dark)">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    <x-inertia::head>
        <title>
            KITB - PT Kawasan Industri Tanjung Buton
        </title>
    </x-inertia::head>
</head>

<body class="font-sans antialiased">
    <x-inertia::app />
</body>

</html>
