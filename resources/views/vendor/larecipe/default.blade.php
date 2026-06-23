<!doctype html>
<html>
    <head>
        <!-- META Tags -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{{ isset($title) ? $title . ' | ' : null }}{{ config('app.name') }}</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- SEO -->
        <meta name="author" content="{{ config('larecipe.seo.author') }}">
        <meta name="description" content="{{ config('larecipe.seo.description') }}">
        <meta name="keywords" content="{{ config('larecipe.seo.keywords') }}">
        <meta name="twitter:card" value="summary">
        @if (isset($canonical) && $canonical)
            <link rel="canonical" href="{{ url($canonical) }}" />
        @endif
        @if($openGraph = config('larecipe.seo.og'))
            @foreach($openGraph as $key => $value)
                @if($value)
                    <meta property="og:{{ $key }}" content="{{ $value }}" />
                @endif
            @endforeach
        @endif

        @php $larecipeBase = rtrim(config('app.url'), '/'); @endphp

        {{-- Dark mode: apply before CSS renders to prevent flash of light mode --}}
        <script>
            (function() {
                try {
                    if (localStorage.getItem('larecipeDarkMode') === 'true') {
                        document.documentElement.classList.add('dark');
                    }
                } catch(e) {}
            })();
        </script>

        <!-- CSS -->
        <link rel="stylesheet" href="{{ $larecipeBase }}/vendor/binarytorch/larecipe/assets/css/app.css">

        @php
            $docsFav = \App\Models\Setting::get('site_favicon')
                ? asset(\App\Models\Setting::get('site_favicon'))
                : asset('favicon.ico');
        @endphp
        <!-- Favicon -->
        <link rel="apple-touch-icon" href="{{ $docsFav }}">
        <link rel="shortcut icon" href="{{ $docsFav }}">

        <!-- FontAwesome -->
        <link rel="stylesheet" href="{{ $larecipeBase }}/vendor/binarytorch/larecipe/assets/css/font-awesome.css">
        @if (config('larecipe.ui.fa_v4_shims', true))
            <link rel="stylesheet" href="{{ $larecipeBase }}/vendor/binarytorch/larecipe/assets/css/font-awesome-v4-shims.css">
        @endif

        <!-- Dynamic Colors -->
        @include('larecipe::style')

        <!-- Dark Mode Styles -->
        <style>
            /* ============================================================
               Dark Mode — toggled via html.dark class
               ============================================================ */

            /* CSS variable overrides for dark mode */
            html.dark {
                --navbar: #161b22;
                --sidebar: #161b22;
                --documentation: #0d1117;
                --white: #161b22;
                --black: #e6edf3;
            }

            /* Base page background & text */
            html.dark body,
            html.dark #app {
                background-color: #0d1117;
                color: #e6edf3;
            }

            /* Loading overlay */
            html.dark [v-cloak]::before {
                background-color: #0d1117;
            }

            /* ---- Navbar ---- */
            html.dark nav.flex.items-center {
                background-color: #161b22;
                border-bottom: 1px solid #30363d;
            }

            html.dark nav .text-black,
            html.dark nav p.text-grey-dark {
                color: #e6edf3 !important;
            }

            /* ---- Sidebar ---- */
            html.dark .sidebar {
                background-color: #161b22 !important;
                background-image: none !important;
                border-right-color: #30363d;
            }

            html.dark .sidebar ul li h2 {
                color: #a78bfa;
            }

            html.dark .sidebar ul li ul li a {
                color: #8b949e;
            }

            html.dark .sidebar ul li ul li a:hover,
            html.dark .sidebar ul li ul li a.active {
                color: #e6edf3;
            }

            /* ---- Documentation content area ---- */
            html.dark .documentation {
                background-color: #0d1117;
                color: #e6edf3;
            }

            /* ---- Typography ---- */
            html.dark .documentation h1,
            html.dark .documentation h2,
            html.dark .documentation h3,
            html.dark .documentation h4,
            html.dark .documentation h5,
            html.dark .documentation h6 {
                color: #e6edf3;
            }

            html.dark .documentation h2 {
                border-bottom-color: #30363d;
            }

            html.dark .documentation p,
            html.dark .documentation li,
            html.dark .documentation td,
            html.dark .documentation th {
                color: #c9d1d9;
            }

            html.dark .documentation a {
                color: #58a6ff;
            }

            html.dark .documentation a:hover {
                color: #79c0ff;
            }

            html.dark .documentation hr {
                border-color: #30363d;
            }

            /* ---- Tables ---- */
            html.dark .documentation table {
                border-color: #30363d;
            }

            html.dark .documentation table thead th {
                background-color: #161b22;
                color: #e6edf3;
                border-color: #30363d;
            }

            html.dark .documentation table tbody tr {
                background-color: #0d1117;
                border-color: #30363d;
            }

            html.dark .documentation table tbody tr:nth-child(even) {
                background-color: #161b22;
            }

            html.dark .documentation table tbody td {
                border-color: #30363d;
                color: #c9d1d9;
            }

            /* ---- Inline code ---- */
            html.dark .documentation :not(pre) > code:not([class*="language-"]) {
                background-color: #1f2937;
                color: #a78bfa;
                border: 1px solid #30363d;
                border-radius: 4px;
                padding: 2px 5px;
            }

            /* ---- Code blocks border override (dark theme already handles background) ---- */
            html.dark :not(pre) > code[class*=language-],
            html.dark pre[class*=language-] {
                border-top-color: #a78bfa;
            }

            /* ---- Blockquote alarms ---- */
            html.dark .documentation .alert {
                opacity: 0.92;
            }

            html.dark .documentation blockquote:not(.alert) {
                border-left: 4px solid #30363d;
                background-color: #161b22;
                color: #8b949e;
                padding: 1rem 1.25rem;
                border-radius: 0 4px 4px 0;
            }

            html.dark .documentation blockquote:not(.alert) p {
                color: #8b949e;
            }

            /* ---- Search box ---- */
            html.dark .search-box {
                background-color: #161b22;
            }

            html.dark .search-box input {
                background-color: #0d1117;
                color: #e6edf3;
            }

            html.dark .search-box input:focus {
                background-color: #161b22;
            }

            /* ---- Dropdown menus ---- */
            html.dark .dropdown-list,
            html.dark [class*="dropdown"] ul {
                background-color: #161b22;
                border-color: #30363d;
            }

            html.dark .dropdown-list li:hover,
            html.dark [class*="dropdown"] li:hover {
                background-color: #21262d !important;
            }

            html.dark .dropdown-list a,
            html.dark [class*="dropdown"] a {
                color: #e6edf3;
            }

            /* ---- Buttons ---- */
            html.dark .btn-white,
            html.dark [class*="button-white"],
            html.dark button[class*="white"] {
                background-color: #21262d;
                color: #e6edf3;
                border-color: #30363d;
            }

            html.dark .btn-black,
            html.dark [class*="button-black"] {
                background-color: #30363d;
                color: #e6edf3;
            }

            /* ---- Back-to-top button ---- */
            html.dark .back-to-top {
                background-color: #161b22;
                border-color: #30363d;
                color: #a78bfa;
            }

            /* ---- Table of contents (right sidebar) ---- */
            html.dark .table-of-contents {
                background-color: #0d1117;
                border-color: #30363d;
            }

            html.dark .table-of-contents a {
                color: #8b949e;
            }

            html.dark .table-of-contents a:hover,
            html.dark .table-of-contents a.active {
                color: #58a6ff;
            }

            /* ---- Screenshot placeholder divs ---- */
            html.dark div[style*="background:#f8fafc"] {
                background: #161b22 !important;
                border-color: #30363d !important;
            }

            html.dark div[style*="color:#94a3b8"] {
                color: #6b7280 !important;
            }

            html.dark div[style*="color:#64748b"] {
                color: #8b949e !important;
            }

            /* ---- LaRecipe card dark mode (default/white type) ---- */
            html.dark .card.is-default,
            html.dark .card.is-white {
                background-color: #161b22;
                border-color: #30363d;
                color: #e6edf3;
            }

            html.dark .card.is-default h3,
            html.dark .card.is-white h3,
            html.dark .card.is-default p,
            html.dark .card.is-white p {
                color: #c9d1d9;
            }

            /* ---- Progress bars ---- */
            html.dark .progress-bar-container,
            html.dark [class*="progress"] {
                background-color: #21262d;
            }

            /* ---- Dark mode toggle button in nav ---- */
            #dark-toggle {
                background: none;
                border: 1px solid transparent;
                border-radius: 6px;
                cursor: pointer;
                padding: 6px 10px;
                margin: 0 4px;
                font-size: 15px;
                line-height: 1;
                transition: background 0.15s, border-color 0.15s;
                color: #6b7280;
            }

            #dark-toggle:hover {
                background-color: rgba(99, 102, 241, 0.1);
                border-color: rgba(99, 102, 241, 0.3);
                color: #7c3aed;
            }

            html.dark #dark-toggle {
                color: #a78bfa;
            }

            html.dark #dark-toggle:hover {
                background-color: rgba(167, 139, 250, 0.15);
                border-color: rgba(167, 139, 250, 0.4);
            }

            /* Show sun in dark mode, moon in light mode */
            #dark-toggle .icon-sun  { display: none; }
            #dark-toggle .icon-moon { display: inline; }

            html.dark #dark-toggle .icon-sun  { display: inline; }
            html.dark #dark-toggle .icon-moon { display: none; }
        </style>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @foreach(LaRecipe::allStyles() as $name => $path)
            @if (preg_match('/^https?:\/\//', $path))
                <link rel="stylesheet" href="{{ $path }}">
            @else
                <link rel="stylesheet" href="{{ route('larecipe.styles', $name) }}">
            @endif
        @endforeach

    </head>
    <body>
        <div id="app" v-cloak>
            @include('larecipe::partials.nav')

            @include('larecipe::plugins.search')

            @yield('content')

            <larecipe-back-to-top></larecipe-back-to-top>
        </div>


        <script>
            window.config = @json([]);
        </script>

        <script type="text/javascript">
            if(localStorage.getItem('larecipeSidebar') == null) {
                localStorage.setItem('larecipeSidebar', !! {{ config('larecipe.ui.show_side_bar') ?: 0 }});
            }
        </script>

        <script src="{{ $larecipeBase }}/vendor/binarytorch/larecipe/assets/js/app.js"></script>

        <script>
            window.LaRecipe = new CreateLarecipe(config)
        </script>

        <!-- Dark Mode Toggle Script -->
        <script>
            function larecipeToggleDark() {
                var isDark = document.documentElement.classList.toggle('dark');
                try {
                    localStorage.setItem('larecipeDarkMode', isDark);
                } catch(e) {}
            }
        </script>

        <!-- Google Analytics -->
        @if(config('larecipe.settings.ga_id'))
            <script async src="{{ asset('js/vendor/gtag.js') }}?id={{ config('larecipe.settings.ga_id') }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());

                gtag('config', "{{ config('larecipe.settings.ga_id') }}");
            </script>
        @endif
        <!-- /Google Analytics -->

        @foreach (LaRecipe::allScripts() as $name => $path)
            @if (preg_match('/^https?:\/\//', $path))
                <script src="{{ $path }}"></script>
            @else
                <script src="{{ route('larecipe.scripts', $name) }}"></script>
            @endif
        @endforeach

        <script>
            LaRecipe.run()
        </script>
    </body>
</html>
