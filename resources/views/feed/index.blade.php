@extends('layouts.app')

@section('content')
<section class="frendi">
    <div class="frendi__content _container">
        @if(isset($winners) && count($winners))
            @foreach($winners as $w)
                @if($w->post)
                <article class="frendi-block frendi-block--winner" data-post-id="{{ $w->post->id }}" style="border:2px solid #d4af37;border-radius:16px;">
                    @if ($w->period_label)
                        @php $__lbl = $w->period_label; if ($__lbl === 'Победитель прошлой недели') { $__lbl = 'Ganador de la semana pasada'; } @endphp
                        <div class="winner-label">{{ $__lbl }}</div>
                    @endif
                <div class="frendi-block__top">
                    <div class="frendi-block__user">
                            @php $avatar = $w->post->meta['avatar_path'] ?? null; @endphp
                            <img class="frendi-block__user_icon" src="{{ $avatar ? asset('storage/'.$avatar) : asset('images/avatar-placeholder.svg') }}" alt="winner" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                        <div class="frendi-block__user_wrap">
                                <h2 class="frendi-block__user_name">{{ $w->post->author_display_name ?? 'frendi.com' }} <span class="frendi-block__user_admin" title="Ganador">🏆 Ganador</span></h2>
                            </div>
                        </div>
                    @php 
                        $isOwner = $w->post->author_token === \App\Support\ClientContext::token(request());
                        $isAdmin = session()->has('admin_id');
                    @endphp
                    @if ($isOwner || $isAdmin)
                    <div class="frendi-block__points"
                        data-post-id="{{ $w->post->id }}"
                        data-delete-url="{{ route('posts.destroy', $w->post) }}"
                        data-edit-url="{{ route('posts.edit', $w->post) }}">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 9.99976C3.9 9.99976 3 10.8998 3 11.9998C3 13.0998 3.9 13.9998 5 13.9998C6.1 13.9998 7 13.0998 7 11.9998C7 10.8998 6.1 9.99976 5 9.99976ZM19 9.99976C17.9 9.99976 17 10.8998 17 11.9998C17 13.0998 17.9 13.9998 19 13.9998C20.1 13.9998 21 13.0998 21 11.9998C21 10.8998 20.1 9.99976 19 9.99976ZM12 9.99976C10.9 9.99976 10 10.8998 10 11.9998C10 13.0998 10.9 13.9998 12 13.9998C13.1 13.9998 14 13.0998 14 11.9998C14 10.8998 13.1 9.99976 12 9.99976Z" fill="black"></path>
                        </svg>
                    </div>
                    @endif
                    </div>
                    @if ($w->post->media->count() || !empty($w->post->meta['avatar_path']))
                        @php $avatarSlidePath = $w->post->meta['avatar_path'] ?? null; @endphp
                        @php $avatarSlidePath = $avatarSlidePath ? asset('storage/'.$avatarSlidePath) : null; @endphp
                        <div class="frendi-slider swiper" data-slides-per-view="1" data-space-between="24">
                            <div class="swiper-wrapper">
                                @if ($avatarSlidePath)
                                    <div class="swiper-slide">
                                        <img class="frendi-block__image" src="{{ $avatarSlidePath }}" alt="avatar">
                    </div>
                                @endif
                                @foreach ($w->post->media as $media)
                                <div class="swiper-slide">
                                    <img class="frendi-block__image" src="{{ asset('storage/'.$media->path) }}" alt="post image">
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                @endif
                    @if ($w->post->title)
                        <div class="frendi-block__title">{{ $w->post->title }}</div>
                @endif
                    @if ($w->post->body)
                    <div class="text-container">
                            <div class="text-content">{{ Str::limit(strip_tags($w->post->body), 240) }}</div>
                    </div>
                @endif
                <div class="frendi-block__bottom">
                    <div class="frendi-block__coll">
                            @php $viewerToken = $authorToken ?? request()->cookie('frendi_token'); @endphp
                            <div class="frendi-block__item {{ (isset($likedPostIds) && in_array($w->post->id, (array)$likedPostIds, true)) ? 'is-liked' : '' }}" data-like data-post-id="{{ $w->post->id }}">
                                <svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.57831 8.50903 2.99871 7.05 2.99871C5.59096 2.99871 4.19169 3.57831 3.16 4.61C2.1283 5.64169 1.54871 7.04097 1.54871 8.5C1.54871 9.95903 2.1283 11.3583 3.16 12.39L4.22 13.45L12 21.23L19.78 13.45L20.84 12.39C21.351 11.8792 21.7563 11.2728 22.0329 10.6053C22.3095 9.93789 22.4518 9.22248 22.4518 8.5C22.4518 7.77752 22.3095 7.06211 22.0329 6.39464C21.7563 5.72718 21.351 5.12075 20.84 4.61Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="frendi-block__item_num">{{ $w->post->reactions()->where('type', 'like')->count() }}</span>
                            </div>
                            <div class="frendi-block__item" data-open-comments data-post-id="{{ $w->post->id }}">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.0013 18.9997C11.9982 18.9994 13.9384 18.335 15.5162 17.1111C17.0941 15.8872 18.2201 14.1732 18.7169 12.2391C19.2138 10.305 19.0533 8.26059 18.2608 6.4277C17.4682 4.59481 16.0886 3.07756 14.3391 2.11481C12.5896 1.15206 10.5695 0.798504 8.59704 1.10979C6.62454 1.42108 4.81158 2.37954 3.44359 3.83427C2.07559 5.289 1.23026 7.15738 1.04067 9.14527C0.851074 11.1332 1.32799 13.1277 2.39634 14.8148L1.00134 18.9997L5.18634 17.6047C6.62587 18.5185 8.29628 19.0025 10.0013 18.9997Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span class="frendi-block__item_num">{{ $w->post->comments()->count() }}</span>
                        </div>
                            <div class="frendi-block__item" data-share data-post-id="{{ $w->post->id }}" data-share-url="{{ url('/share/'.$w->post->share_slug) }}">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 1.63976V11.514M12.5556 4.19976L9 0.999756L5.44444 4.19976M1 9.31976V14.8055C1 15.3874 1.24082 15.9456 1.66947 16.3571C2.09812 16.7686 2.67951 16.9998 3.28571 16.9998H14.7143C15.3205 16.9998 15.9019 16.7686 16.3305 16.3571C16.7592 15.9456 17 15.3874 17 14.8055V9.31976" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                    </div>
                    <div class="frendi-block__coll">
                            <span class="frendi-block__time">{{ $w->post->created_at?->locale('es')->diffForHumans() }}</span>
                    </div>
                </div>
            </article>
                @endif
            @endforeach
        @endif
        <div id="feedWrap">
            @include('feed._posts', ['posts' => $posts, 'winnerPostIds' => $winnerPostIds ?? collect(), 'authorToken' => $authorToken ?? null, 'likedPostIds' => $likedPostIds ?? []])
        </div>
        <div id="feedSentinel" style="height:1px;"></div>
        <template id="feedLoaderTpl">
            <div class="frendi-block" id="feedLoader" style="text-align:center;padding:16px;opacity:0.7;">Loading...</div>
        </template>
    </div>

</section>

<div class="drop-down__overlay" id="dropdownOverlay" hidden></div>
<section class="drop-down" id="postDropdown" hidden data-post-id="">
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initSliders(scope) {
            (scope || document).querySelectorAll('.frendi-slider').forEach(function (el) {
                if (el.__inited) return; el.__inited = true;
                const swiper = new Swiper(el, {
                    loop: true,
                    slidesPerView: Number(el.dataset.slidesPerView || 1),
                    spaceBetween: Number(el.dataset.spaceBetween || 12),
                    centeredSlides: false,
                    pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
                    autoplay: { delay: 3000, disableOnInteraction: false },
                });
                el.__swiper = swiper;
                try { swiper.autoplay.stop(); } catch(_) {}
                const obs = new IntersectionObserver((entries)=>{
                    entries.forEach((entry)=>{
                        const s = el.__swiper;
                        if (!s || !s.autoplay) return;
                        if (entry.isIntersecting) { try{s.autoplay.start();}catch(_){}}
                        else { try{s.autoplay.stop();}catch(_){}}
                    });
                }, { threshold: 0.5 });
                obs.observe(el);
            });
        }
        initSliders(document);

        let nextPageUrl = @json($posts->nextPageUrl());
        const sentinel = document.getElementById('feedSentinel');
        const wrap = document.getElementById('feedWrap');
        let loading = false;

        const urlFrom = (u)=>{ try { return new URL(u, window.location.origin); } catch(_) { return null; } };
        const initialNext = @json($posts->nextPageUrl());
        let firstPageUrl = null;
        if (initialNext) {
            const u = urlFrom(initialNext); if (u) { u.searchParams.set('page','1'); firstPageUrl = u.pathname + u.search; }
        } else {
            const cu = urlFrom(window.location.href); if (cu) { cu.searchParams.set('page','1'); firstPageUrl = cu.pathname + cu.search; }
        }

        const recentIds = new Set();
        const recentQueue = [];
        const rememberId = (id)=>{ if (!id) return; if (!recentIds.has(id)) { recentIds.add(id); recentQueue.push(id); if (recentQueue.length > 200) { const old = recentQueue.shift(); recentIds.delete(old); } } };
        wrap.querySelectorAll('.frendi-block[data-post-id]').forEach(el=>rememberId(String(el.getAttribute('data-post-id'))));

        function viewportH(){ return (window.visualViewport && window.visualViewport.height) || window.innerHeight; }
        function gapBelow() {
            return document.documentElement.scrollHeight - (window.pageYOffset + viewportH());
        }

        async function loadMore(showLoaderNearBottom = false, depth = 0) {
            if (!nextPageUrl) { if (firstPageUrl) nextPageUrl = firstPageUrl; }
            if (!nextPageUrl || loading) return;
            loading = true;
            let loader = null;
            if (showLoaderNearBottom && gapBelow() < 600) {
                loader = document.getElementById('feedLoader') || document.getElementById('feedLoaderTpl').content.cloneNode(true).firstElementChild;
                if (!document.getElementById('feedLoader')) wrap.appendChild(loader);
            }
            try {
                const res = await fetch(nextPageUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('Network');
                const data = await res.json();
                const tmp = document.createElement('div');
                tmp.innerHTML = data.html;
                const children = Array.from(tmp.children);
                const filtered = children.filter(ch => {
                    const id = ch.getAttribute && ch.getAttribute('data-post-id');
                    return id && !recentIds.has(id);
                });
                filtered.forEach(ch => { wrap.appendChild(ch); rememberId(ch.getAttribute('data-post-id')); });
                nextPageUrl = data.next_page_url || firstPageUrl || null;
                initSliders(wrap);
                if (!filtered.length && depth < 3 && nextPageUrl) {
                    await loadMore(false, depth + 1);
                }
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
        }, { rootMargin: '1200px 0px' });
        io.observe(sentinel);
        if ('onscrollend' in window) { window.addEventListener('scrollend', prefetchIfNeeded); }

        const debounced = (fn, d=120) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; };
        window.addEventListener('scroll', debounced(prefetchIfNeeded, 120), { passive: true });
        window.addEventListener('resize', debounced(prefetchIfNeeded, 200));

        (window.requestIdleCallback || function(cb){return setTimeout(cb, 0);})(prefetchIfNeeded);
        prefetchIfNeeded();

        async function ensurePostVisibleAndScroll(postId, maxLoads = 50) {
            const find = () => wrap.querySelector(`.frendi-block[data-post-id="${postId}"]`);
            let el = find();
            let loads = 0;
            while (!el && nextPageUrl && loads < maxLoads) {
                await loadMore(false);
                el = find();
                loads++;
            }
            if (el) {
                try { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch(_) { window.location.hash = `post-${postId}`; }
                el.style.boxShadow = '0 0 0 3px #d4af37 inset';
                setTimeout(() => { el.style.boxShadow = ''; }, 2000);
            }
        }
        document.querySelectorAll('.frendi-block--winner').forEach((winnerEl)=>{
            const trigger = ()=>{ const postId = winnerEl.getAttribute('data-post-id'); if (postId) ensurePostVisibleAndScroll(postId); };
            winnerEl.addEventListener('click', trigger, { passive: true });
            winnerEl.addEventListener('pointerup', (e)=>{ if (e.pointerType!=='mouse') trigger(); }, { passive: true, capture: true });
            winnerEl.addEventListener('touchend', trigger, { passive: true, capture: true });
            winnerEl.addEventListener('keydown', (e)=>{ if (e.key==='Enter' || e.key===' ') { e.preventDefault?.(); trigger(); } });
        });

        document.addEventListener('visibilitychange', ()=>{
            const sliders = document.querySelectorAll('.frendi-slider');
            sliders.forEach(el=>{ const s = el.__swiper; if (!s||!s.autoplay) return; if (document.hidden) { try{s.autoplay.stop();}catch(_){}} });
        });
    });
</script>
@endpush
