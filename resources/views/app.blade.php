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

    {{-- =========================================================
         SEO DASAR
    ========================================================== --}}

    <meta name="description"
        content="PT Kawasan Industri Tanjung Buton (KITB) menghadirkan informasi resmi mengenai profil perusahaan, kawasan industri, fasilitas, berita, publikasi, karier, dan informasi lainnya.">

    <meta name="keywords"
        content="KITB, Kawasan Industri Tanjung Buton, PT Kawasan Industri Tanjung Buton, kawasan industri, Tanjung Buton, industrial estate, Riau">

    <meta name="robots" content="index, follow">

    <meta name="author" content="Bayu Albar Ladici">

    <meta name="application-name" content="KITB - PT Kawasan Industri Tanjung Buton">

    <meta name="generator" content="Laravel, Vue 3, Inertia.js">

    <link rel="canonical" href="https://tanjungbuton-industrial.co.id/">

    {{-- =========================================================
         OPEN GRAPH / FACEBOOK / WHATSAPP
    ========================================================== --}}

    <meta property="og:type" content="website">

    <meta property="og:site_name" content="KITB - PT Kawasan Industri Tanjung Buton">

    <meta property="og:title" content="KITB - PT Kawasan Industri Tanjung Buton">

    <meta property="og:description"
        content="Informasi resmi PT Kawasan Industri Tanjung Buton mengenai profil perusahaan, kawasan industri, fasilitas, berita, publikasi, dan peluang karier.">

    <meta property="og:url" content="https://tanjungbuton-industrial.co.id/">

    <meta property="og:image" content="https://tanjungbuton-industrial.co.id/logoside.png">

    <meta property="og:image:secure_url" content="https://tanjungbuton-industrial.co.id/logoside.png">

    <meta property="og:image:type" content="image/png">

    <meta property="og:image:alt" content="Logo PT Kawasan Industri Tanjung Buton">

    <meta property="og:locale" content="id_ID">

    {{-- =========================================================
         TWITTER / X
    ========================================================== --}}

    <meta name="twitter:card" content="summary_large_image">

    <meta name="twitter:title" content="KITB - PT Kawasan Industri Tanjung Buton">

    <meta name="twitter:description"
        content="Informasi resmi PT Kawasan Industri Tanjung Buton mengenai profil perusahaan, kawasan industri, fasilitas, berita, publikasi, dan peluang karier.">

    <meta name="twitter:image" content="https://tanjungbuton-industrial.co.id/logoside.png">

    <meta name="twitter:image:alt" content="Logo PT Kawasan Industri Tanjung Buton">

    {{-- =========================================================
         BROWSER / BRANDING
    ========================================================== --}}

    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">

    <meta name="theme-color" content="#0b1728" media="(prefers-color-scheme: dark)">

    <link rel="icon" href="/favicon.ico" sizes="any">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    {{-- =========================================================
         VITE
    ========================================================== --}}

    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    {{-- =========================================================
         INERTIA HEAD
    ========================================================== --}}

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
