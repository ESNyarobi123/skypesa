{{-- PWA Meta Tags Component --}}

{{-- Web App Manifest --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">

{{-- Theme Colors --}}
<meta name="theme-color" content="#111111">
<meta name="msapplication-TileColor" content="#111111">
<meta name="msapplication-navbutton-color" content="#111111">

{{-- Apple Touch Settings --}}
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="SKYpesa">
<link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('icons/icon-152x152.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/icon-192x192.png') }}">
<link rel="apple-touch-icon" sizes="167x167" href="{{ asset('icons/icon-192x192.png') }}">

{{-- Apple Splash Screens --}}
<link rel="apple-touch-startup-image" href="{{ asset('icons/splash-640x1136.png') }}" 
      media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">
<link rel="apple-touch-startup-image" href="{{ asset('icons/splash-750x1334.png') }}" 
      media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">
<link rel="apple-touch-startup-image" href="{{ asset('icons/splash-1242x2208.png') }}" 
      media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3)">
<link rel="apple-touch-startup-image" href="{{ asset('icons/splash-1125x2436.png') }}" 
      media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)">
<link rel="apple-touch-startup-image" href="{{ asset('icons/splash-1170x2532.png') }}" 
      media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)">

{{-- Standard Icons --}}
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/icon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/icon-16x16.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">

{{-- Microsoft Tile --}}
<meta name="msapplication-TileImage" content="{{ asset('icons/icon-144x144.png') }}">
<meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">

{{-- Mobile Settings --}}
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="SKYpesa">
<meta name="format-detection" content="telephone=no">
<meta name="HandheldFriendly" content="true">
<meta name="MobileOptimized" content="width">
