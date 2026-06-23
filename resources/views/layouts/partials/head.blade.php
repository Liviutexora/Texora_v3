@php
    $logo = \App\Models\Setting::get('site_logo');
    $logo = $logo ? 'storage/'.$logo : null;
    $favicon = \App\Models\Setting::get('site_favicon');
    $favicon = $favicon ? 'storage/'.$favicon : null;
    $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Slotara'));
    $socialTwitter = ltrim(\App\Models\Setting::get('social_twitter', ''), '@');
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Dynamic SEO Meta Tags --}}
<title>@yield('title', $siteName)</title>
<meta name="description" content="@yield('description', \App\Models\Setting::get('site_description'))">
<meta name="keywords" content="@yield('keywords', \App\Models\Setting::get('site_keywords'))">
<meta name="author" content="{{ \App\Models\Setting::get('site_author', $siteName) }}">
<meta name="robots" content="{{ \App\Models\Setting::get('meta_robots', 'index, follow') }}">

{{-- Open Graph Meta Tags --}}
<meta property="og:title" content="@yield('og_title', $siteName)">
<meta property="og:description" content="@yield('og_description', \App\Models\Setting::get('site_description'))">
<meta property="og:image" content="@yield('og_image', asset($logo))">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

{{-- Twitter Card Tags --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('twitter_title', $siteName)">
<meta name="twitter:description" content="@yield('twitter_description', \App\Models\Setting::get('site_description'))">
<meta name="twitter:image" content="@yield('twitter_image', asset($logo))">
<meta name="twitter:site" content="@yield('twitter_site', '@' . $socialTwitter)">
<meta name="twitter:creator" content="@yield('twitter_creator', '@' . $socialTwitter)">


{{-- Favicon --}}
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
@if($favicon)
<link rel="icon" type="image/x-icon" href="{{ asset($favicon) }}">
@else
<link rel="alternate icon" href="{{ asset('favicon.ico') }}">
@endif


{{-- Analytics --}}
@if($ga_id = \App\Models\Setting::get('google_analytics_id'))
    <!-- Google Analytics -->
    <script async src="{{ asset('js/vendor/gtag.js') }}?id={{ $ga_id }}"></script>
    <script>
        'use strict';
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', '{{ $ga_id }}');
    </script>
@endif

@if($gtm_id = \App\Models\Setting::get('google_tag_manager_id'))
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                '{{ asset('js/vendor/gtm.js') }}?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '{{ $gtm_id }}');
    </script>
@endif

@if($fb_pixel = \App\Models\Setting::get('facebook_pixel_id'))
    <!-- Facebook Pixel -->
    <script>
        ! function(f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function() {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            '{{ asset('js/vendor/fbevents.js') }}');
        fbq('init', '{{ $fb_pixel }}');
        fbq('track', 'PageView');
    </script>
@endif

<script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => url('/'),
        'logo' => asset($logo),
        'sameAs' => [
            'https://facebook.com/YourPage',
            'https://twitter.com/YourHandle',
            'https://www.linkedin.com/company/yourcompany',
            ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
            
</script>

<link rel="canonical" href="{{ url()->current() }}" />
