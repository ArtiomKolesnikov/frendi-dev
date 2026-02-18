<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#108081">
    <!-- Отключение кэширования для разработки -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $title ?? 'Frendi' }}</title>
    <!-- Open Graph / Twitter Cards -->
    <meta property="og:title" content="{{ $og['title'] ?? ($title ?? 'Frendi') }}">
    <meta property="og:description" content="{{ $og['description'] ?? 'Frendi de paseos, mascotas y concursos.' }}">
    <meta property="og:image" content="{{ $og['image'] ?? route('og.default') }}">
    <meta property="og:image:secure_url" content="{{ $og['image'] ?? route('og.default') }}">
    <meta property="og:url" content="{{ $og['url'] ?? url()->current() }}">
    <meta property="og:type" content="{{ $og['type'] ?? 'website' }}">
    <meta property="og:site_name" content="Frendi">
    <meta property="og:image:width" content="{{ $og['image_width'] ?? 1200 }}">
    <meta property="og:image:height" content="{{ $og['image_height'] ?? 630 }}">
    <meta name="description" content="{{ $og['description'] ?? 'Frendi de paseos, mascotas y concursos.' }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $og['title'] ?? ($title ?? 'Frendi') }}">
    <meta name="twitter:description" content="{{ $og['description'] ?? 'Frendi de paseos, mascotas y concursos.' }}">
    <meta name="twitter:image" content="{{ $og['image'] ?? route('og.default') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/favicon-512.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ asset('styles/styles.css') }}?v={{ env('CACHE_VERSION', 1) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="x-client-token" content="{{ \App\Support\ClientContext::token(request()) }}">
    @stack('styles')
    @if (config('analytics.ym_id'))
        <script type="text/javascript">
            (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
            (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
            ym({{ json_encode(config('analytics.ym_id')) }}, "init", { clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true });
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/{{ e(config('analytics.ym_id')) }}" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    @endif
    @if (config('analytics.ga_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('analytics.ga_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '{{ config('analytics.ga_id') }}');
        </script>
    @endif
    <script>
        (function(){
            var ua = navigator.userAgent || navigator.vendor || window.opera;
            var isiOS = /iPad|iPhone|iPod/.test(ua) || (ua.indexOf('Mac') !== -1 && navigator.maxTouchPoints > 1);
            if (isiOS) { document.documentElement.classList.add('ios'); }
        })();
    </script>
    <style>
        .scroll-top-btn{position:fixed;right:calc(16px + env(safe-area-inset-right));bottom:calc(16px + env(safe-area-inset-bottom));z-index:9999;border:none;border-radius:12px;padding:12px 14px;background:#1f1f1f;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.18);cursor:pointer;transition:transform .2s ease,opacity .2s ease,background .2s ease}
        .scroll-top-btn:hover{background:#2a2a2a}
        .scroll-top-btn[hidden]{opacity:0;pointer-events:none;transform:translateY(8px)}
        @media (prefers-color-scheme:dark){.scroll-top-btn{background:#2b2b2b}}
        @media (min-width:768px){.scroll-top-btn{right:24px;bottom:24px}}
        .text-container{position:relative}
        .text-content--clamp{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:4;overflow:hidden;white-space:normal;position:relative}
        .text-container .text-see-more{position:absolute;right:0;bottom:0;border:0;background:linear-gradient(90deg, rgba(255,255,255,0) 0%, #fff 28%);color:#1f1f1f;padding:0 0 0 8px;cursor:pointer}
        .text-see-more:focus{outline:none}
        .photo-viewer__overlay{position:fixed;inset:0;background:rgba(0,0,0,.86);z-index:9998;display:none}
        .photo-viewer{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center}
        .photo-viewer.is-active,.photo-viewer__overlay.is-active{display:flex}
        .photo-viewer .swiper{width:100%;height:100%}
        .photo-viewer .swiper-slide{display:flex;align-items:center;justify-content:center}
        .photo-viewer img{max-width:100%;max-height:100%;object-fit:contain}
        .photo-viewer__close{position:fixed;top:calc(12px + env(safe-area-inset-top));right:calc(12px + env(safe-area-inset-right));background:rgba(0,0,0,.5);color:#fff;border:0;border-radius:50%;width:36px;height:36px;cursor:pointer}
        .text-sheet__overlay{position:fixed;inset:0;background:rgba(0,0,0,.42);z-index:9998;display:none}
        .text-sheet{position:fixed;left:50%;bottom:0;transform:translateX(-50%);width:100%;max-width:640px;background:#fff;border-radius:16px 16px 0 0;z-index:9999;display:none}
        .text-sheet.is-active,.text-sheet__overlay.is-active{display:block}
        .text-sheet__body{padding:16px;max-height:70vh;overflow:auto}
        @media (pointer: coarse) and (-webkit-touch-callout: none) {
            .frendi__content._container{ padding:0 5px !important; }
        }
        html.ios .frendi__content._container{ padding:0 5px !important; }
        .winner-label{ font-weight:700; font-size:14px; line-height:1.2; margin:4px 0 6px; display:inline-block; background:linear-gradient(90deg,#108081 0%, #2f8f46 25%, #d4af37 50%, #2f8f46 75%, #108081 100%); -webkit-background-clip:text; background-clip:text; color:transparent; -webkit-text-fill-color:transparent !important; background-size:300% 100%; animation:wlabel-shift 2.4s ease-in-out infinite; }
        @keyframes wlabel-shift{ 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
        /* Like splash animation */
        .frendi-block{position:relative}
        .like-splash{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%) scale(.6);opacity:0;pointer-events:none;color:var(--color-green);z-index:4;animation:paw-pop .8s ease-out forwards}
        @keyframes paw-pop{0%{opacity:0;transform:translate(-50%,-50%) scale(.6)}20%{opacity:.95;transform:translate(-50%,-50%) scale(1)}100%{opacity:0;transform:translate(-50%,-60%) scale(1.35)}}
    </style>
</head>
<body data-is-admin="{{ session()->has('admin_id') ? '1' : '' }}" data-user-name="{{ auth()->check() ? auth()->user()->name : '' }}">
    {{-- DEBUG: auth()->check()={{ auth()->check() ? 'YES' : 'NO' }}, user={{ auth()->check() ? auth()->user()->name : 'NULL' }} --}}
    <div class="wrapper">
        @include('partials.header')
        <main class="main">
            @yield('content')
        </main>
        @include('partials.footer')
    </div>

    <button id="scrollTopBtn" class="scroll-top-btn" aria-label="Прокрутить вверх" hidden>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 3l6 6-1.4 1.4L11 6.8V17h-2V6.8L5.4 10.4 4 9l6-6z" fill="currentColor"/></svg>
    </button>

    <div id="photoOverlay" class="photo-viewer__overlay"></div>
    <section id="photoViewer" class="photo-viewer" aria-hidden="true">
        <button type="button" class="photo-viewer__close" aria-label="Close" data-photo-close>&times;</button>
        <div class="swiper" data-photo-swiper>
            <div class="swiper-wrapper" data-photo-wrapper></div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <div id="textOverlay" class="text-sheet__overlay" hidden></div>
    <section id="textSheet" class="text-sheet" hidden>
        <div class="text-sheet__body" data-text-body></div>
    </section>

    <script>
        (function(){
            const btn=document.getElementById('scrollTopBtn');
            const showAt=200; // показывать после 200px прокрутки
            const onScroll=()=>{ if(window.pageYOffset>showAt){ btn.removeAttribute('hidden'); } else { btn.setAttribute('hidden',''); } btn.style.setProperty('inset-inline-end', 'calc(16px + env(safe-area-inset-right))'); btn.style.setProperty('inset-block-end', 'calc(16px + env(safe-area-inset-bottom))'); };
            window.addEventListener('scroll', onScroll, {passive:true});
            onScroll();
            btn.addEventListener('click', ()=>{
                try{ window.scrollTo({top:0,behavior:'smooth'}); } catch(e){ window.scrollTo(0,0); }
            });
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
    <script src="{{ asset('js/main.js') }}?v={{ env('CACHE_VERSION', 1) }}" defer></script>
    @stack('scripts-before-body-end')
    @stack('scripts')
</body>
</html>
