@extends('layouts.app')

@section('content')
<section class="frendi">
    <div class="frendi__content _container">
        <div class="new-post__top" style="gap:12px;">
            
            <div style="display:flex; gap:8px; margin-left:auto;">
                
                <a class="button button-white" href="{{ route('admin.metrics') }}">Métricas</a>
                <a class="button button-white" href="{{ route('admin.contest') }}">Ganadores</a>
                <form method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="button" type="submit">Logout</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div id="postsWrap">
            @include('admin._posts', ['posts' => $posts])
        </div>

        <div id="infiniteSentinel" style="height:1px;"></div>
        <template id="loaderTpl">
            <div class="frendi-block" id="loader" style="text-align:center;padding:16px;opacity:0.7;">Cargando…</div>
        </template>

    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initSliders(scope) {
            (scope || document).querySelectorAll('.frendi-slider').forEach(function (el) {
                if (el.__inited) return; el.__inited = true;
                new Swiper(el, {
                    loop: false,
                    slidesPerView: Number(el.dataset.slidesPerView || 1),
                    spaceBetween: Number(el.dataset.spaceBetween || 12),
                    centeredSlides: false,
                    pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
                });
            });
        }
        initSliders(document);

        let nextPageUrl = @json($posts->nextPageUrl());
        const sentinel = document.getElementById('infiniteSentinel');
        const postsWrap = document.getElementById('postsWrap');
        let loading = false;

        function viewportH(){ return (window.visualViewport && window.visualViewport.height) || window.innerHeight; }
        function gapBelow() {
            return document.documentElement.scrollHeight - (window.pageYOffset + viewportH());
        }

        async function loadMore(showLoaderNearBottom = false) {
            if (!nextPageUrl || loading) return;
            loading = true;
            let loader = null;
            if (showLoaderNearBottom && gapBelow() < 600) {
                loader = document.getElementById('loader') || document.getElementById('loaderTpl').content.cloneNode(true).firstElementChild;
                if (!document.getElementById('loader')) postsWrap.appendChild(loader);
            }
            try {
                const res = await fetch(nextPageUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('Network error');
                const data = await res.json();
                const tmp = document.createElement('div');
                tmp.innerHTML = data.html;
                Array.from(tmp.children).forEach(ch => postsWrap.appendChild(ch));
                nextPageUrl = data.next_page_url;
                initSliders(postsWrap);
            } catch (e) {
                console.error(e);
            } finally {
                if (loader) loader.remove();
                loading = false;
            }
        }

        async function prefetchIfNeeded() {
            let prefetched = 0;
            while (nextPageUrl && !loading && (gapBelow() < 2000 || document.documentElement.scrollHeight < window.innerHeight + 1200) && prefetched < 3) {
                await loadMore(false);
                prefetched++;
            }
        }

        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) loadMore(true); });
        }, { rootMargin: '3500px 0px' });
        io.observe(sentinel);
        if ('onscrollend' in window) { window.addEventListener('scrollend', prefetchIfNeeded); }

        const debounced = (fn, d=120) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; };
        window.addEventListener('scroll', debounced(prefetchIfNeeded, 120), { passive: true });
        window.addEventListener('resize', debounced(prefetchIfNeeded, 200));

        (window.requestIdleCallback || function(cb){return setTimeout(cb, 0);})(prefetchIfNeeded);
        prefetchIfNeeded();
    });
</script>
@endpush

{{-- Bottom sheets reused from feed for dropdown/comments/share --}}
<div class="drop-down__overlay" id="dropdownOverlay" hidden></div>
<section class="drop-down" id="postDropdown" hidden data-post-id="">

<section class="drop-down" id="contestSheet" hidden data-post-id="">
    <div class="drop-down__content">
        <div class="wrap">
            <div class="inner">
                <div class="drop-down__body">
                    <div class="drop-down__wrap">
                        <label class="new-post__label">Período</label>
                        <input class="new-post__input" type="text" placeholder="2025‑09‑15 — 2025‑09‑22" data-contest-period>
                    </div>
                    <div style="display:flex;gap:12px;justify-content:center;margin-top:8px;">
                        <button class="button" type="button" data-contest-ok>Aceptar</button>
                        <button class="button button-white" type="button" data-contest-cancel>Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
    <div class="drop-down__content">
        <div class="wrap">
            <div class="inner">
                <div class="drop-down__body">
                    <div class="drop-down__wrap">
                        <button class="drop-down__item" type="button" data-action="edit">
                            <img class="drop-down__item_icon" src="{{ asset('images/drop-down-icon2.svg') }}" alt="icon">
                            <h2 class="drop-down__item_title">Editar</h2>
                        </button>
                    </div>
                    <div class="drop-down__wrap">
                        <button class="drop-down__item" type="button" data-action="delete">
                            <img class="drop-down__item_icon" src="{{ asset('images/drop-down-icon3.svg') }}" alt="icon">
                            <h2 class="drop-down__item_title">Eliminar</h2>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="drop-down__overlay" id="commentsOverlay" hidden></div>
<section class="drop-down" id="commentsSheet" hidden data-post-id="">
    <div class="drop-down__content">
        <div class="wrap">
            <div class="inner">
                <div class="comments__body" data-comments-body>
                    <h2 class="comments__title">Comentarios</h2>
                    <div class="comments__wrap" data-comments-wrap>
                        <!-- Filled dynamically: skeleton, list, or empty state -->
                    </div>
                    <div class="comments__bottom">
                        <img class="comments__bottom_icon" src="{{ asset('images/avatar-placeholder.svg') }}" alt="icon">
                        <div class="comments__add">
                            <input class="comments__add_input" type="text" placeholder="Agregar un comentario" data-comment-input>
                            <button class="comments__add_button" type="button" data-comment-send>
                                <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.5 12L3.5 21L21.5 12L3.5 3L6.5 12ZM6.5 12H12.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="drop-down__overlay" id="shareOverlay" hidden></div>
<section class="drop-down" id="shareSheet" hidden>
    <div class="drop-down__content">
        <div class="wrap">
            <div class="inner">
                <div class="drop-down__body">
                    <div class="drop-down__wrap">
                        <div class="comments__add">
                            <input class="comments__add_input" type="text" readonly value="" data-share-input>
                            <button class="comments__add_button" type="button" data-share-copy>Copiar</button>
                        </div>
                    </div>
                    <div data-share-actions></div>
                </div>
            </div>
        </div>
    </div>
</section>

<form id="dropdownDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form> 