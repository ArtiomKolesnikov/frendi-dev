document.addEventListener('DOMContentLoaded', () => {
    // GA helper
    const GA_ID = document.querySelector('script[src^="https://www.googletagmanager.com/gtag/js?id="]') ? true : false;
    function trackEvent(name, params){
        try{
            console.log('[GA4] 📤 SENDING EVENT:', name, params || {});
            if (typeof window.gtag === 'function') {
                // Wrap gtag to log after send
                const paramsWithCallback = {
                    ...(params || {}),
                    event_callback: function() {
                        console.log('[GA4] ✅ EVENT SENT SUCCESSFULLY:', name, params || {});
                        // Call original callback if exists
                        if (params && typeof params.event_callback === 'function') {
                            params.event_callback();
                        }
                    }
                };
                window.gtag('event', name, paramsWithCallback);
                console.log('[GA4] ⏳ Event queued:', name);
            } else {
                console.warn('[GA4] ⚠️ gtag not available for event:', name);
            }
        }catch(err){
            console.error('[GA4] ❌ Error sending event:', name, err);
        }
    }
    // Photo viewer (tap any post image)
    (function(){
        const overlay = document.getElementById('photoOverlay');
        const viewer = document.getElementById('photoViewer');
        const wrapper = document.querySelector('[data-photo-wrapper]');
        const closeBtn = document.querySelector('[data-photo-close]');
        let swiper = null;
        function openViewer(images, startIndex, sourceCard){
            if (!viewer || !overlay || !wrapper) return;
            wrapper.innerHTML = '';
            images.forEach((src)=>{
                const slide = document.createElement('div');
                slide.className = 'swiper-slide';
                slide.innerHTML = `<img src="${src}" alt=""/>`;
                wrapper.appendChild(slide);
            });
            overlay.classList.add('is-active');
            viewer.classList.add('is-active');
            document.body.style.overflow='hidden';
            if (swiper) { try{ swiper.destroy(true,true);}catch(_){} }
            swiper = new Swiper('[data-photo-swiper]', { initialSlide: startIndex||0, pagination:{el:'.swiper-pagination', clickable:true} });

            // GA: image view opened (generic + route-specific)
            if (sourceCard) {
                try{
                    const type = sourceCard.getAttribute('data-post-type') || '';
                    const postId = sourceCard.getAttribute('data-post-id') || '';
                    const owner = sourceCard.getAttribute('data-post-owner') || '';
                    if (postId) trackEvent('image_open', { post_id: postId, post_type: type });
                    if (type === 'route') {
                        trackEvent('route_image_open', { post_id: postId });
                        if (owner === 'user') {
                            trackEvent('route_image_view_other', { post_id: postId });
                        }
                    }
                }catch(_){/* noop */}
            }
        }
        function closeViewer(){ if (!viewer||!overlay) return; viewer.classList.remove('is-active'); overlay.classList.remove('is-active'); document.body.style.overflow=''; }
        closeBtn?.addEventListener('click', closeViewer);
        overlay?.addEventListener('click', closeViewer);
        viewer?.addEventListener('click', (e)=>{
            const isImg = !!(e.target && e.target.closest && e.target.closest('.frendi-block__image'));
            const isInnerImg = e.target?.tagName === 'IMG';
            if (!isInnerImg) closeViewer();
        });
        const openHandler = (e)=>{
            const img = e.target.closest && e.target.closest('.frendi-block__image');
            if (!img) return;
            // If a drag/swipe gesture, ignore
            if (e.type === 'pointerup' && (Math.abs((e.pageX||0)-(openHandler._sx||0))>10 || Math.abs((e.pageY||0)-(openHandler._sy||0))>10)) return;
            const card = img.closest('.frendi-block');
            if (!card) return;
            const imgs = Array.from(card.querySelectorAll('.frendi-block__image')).map(i=>i.getAttribute('src'));
            const idx = imgs.indexOf(img.getAttribute('src')) || 0;
            openViewer(imgs, idx, card);
        };
        ['click','pointerup'].forEach((t)=>{
            document.addEventListener(t, openHandler, { passive: true, capture: true });
        });
        // Precise tap detection for iOS/Android
        let tapStartX=0, tapStartY=0, tapStartT=0, tapTarget=null;
        document.addEventListener('touchstart', (e)=>{
            const t = e.touches && e.touches[0];
            const img = e.target.closest && e.target.closest('.frendi-block__image');
            if (!t || !img) { tapTarget=null; return; }
            tapTarget = img;
            tapStartX = t.clientX; tapStartY = t.clientY; tapStartT = Date.now();
            openHandler._sx = t.pageX; openHandler._sy = t.pageY;
        }, { passive: true, capture: true });
        document.addEventListener('touchend', (e)=>{
            if (!tapTarget) return;
            const t = e.changedTouches && e.changedTouches[0];
            if (!t) { tapTarget=null; return; }
            const dx = Math.abs(t.clientX - tapStartX);
            const dy = Math.abs(t.clientY - tapStartY);
            const dt = Date.now() - tapStartT;
            const sameTarget = !!(e.target.closest && e.target.closest('.frendi-block__image'));
            if (dx < 10 && dy < 10 && dt < 300 && sameTarget) {
                const card = tapTarget.closest('.frendi-block');
                if (card) {
                    const imgs = Array.from(card.querySelectorAll('.frendi-block__image')).map(i=>i.getAttribute('src'));
                    const idx = imgs.indexOf(tapTarget.getAttribute('src')) || 0;
                    openViewer(imgs, idx, card);
                } else {
                    // Fallback: open image directly
                    window.open(tapTarget.getAttribute('src'), '_blank');
                }
            }
            tapTarget=null;
        }, { passive: true, capture: true });
        document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') closeViewer(); });
    })();

    // Clamp preview: open full text in sheet
    (function(){
        const overlay = document.getElementById('textOverlay');
        const sheet = document.getElementById('textSheet');
        const body = sheet?.querySelector('[data-text-body]');
        function openSheet(html){ if(!overlay||!sheet||!body) return; body.innerHTML = html; overlay.classList.add('is-active'); sheet.classList.add('is-active'); sheet.removeAttribute('hidden'); overlay.removeAttribute('hidden'); }
        function closeSheet(){ if(!overlay||!sheet) return; overlay.classList.remove('is-active'); sheet.classList.remove('is-active'); setTimeout(()=>{overlay.setAttribute('hidden',''); sheet.setAttribute('hidden','');}, 200); }
        overlay?.addEventListener('click', closeSheet);
        document.addEventListener('click', (e)=>{
            const btn = e.target.closest && e.target.closest('[data-see-more]');
            if (!btn) return;
            const contentEl = btn.previousElementSibling;
            const text = (contentEl?.getAttribute('data-full-text')) || (contentEl?.innerText || '');
            const card = btn.closest('.frendi-block');
            const title = card?.querySelector('.frendi-block__title')?.textContent || '';
            openSheet(`<h3 style="margin:0 0 12px 0;">${title}</h3><p style="white-space:pre-wrap;">${text}</p>`);
        });
    })();

    // Prevent pinch-zoom and double-tap zoom on mobile
    document.addEventListener('gesturestart', (e) => e.preventDefault());
    document.addEventListener('gesturechange', (e) => e.preventDefault());
    document.addEventListener('gestureend', (e) => e.preventDefault());
    let lastTouchEnd = 0;
    document.addEventListener('touchend', (e) => {
        const now = Date.now();
        if (now - lastTouchEnd <= 300) {
            e.preventDefault();
        }
        lastTouchEnd = now;
    }, { passive: false });
    const DROPDOWN_TRANSITION = 1000;

    const dropdown = document.getElementById('postDropdown');
    const overlay = document.getElementById('dropdownOverlay');
    const deleteForm = document.getElementById('dropdownDeleteForm');

    const commentsSheet = document.getElementById('commentsSheet');
    const commentsOverlay = document.getElementById('commentsOverlay');

    const shareSheet = document.getElementById('shareSheet');
    const shareOverlay = document.getElementById('shareOverlay');

    const contestSheet = document.getElementById('contestSheet');

    let activePostId = null;
    let activeDeleteUrl = null;
    let activeEditUrl = null;

    function openDropdown(trigger) {
        if (!dropdown || !overlay) {
            return;
        }

        const postId = trigger?.getAttribute('data-post-id');
        const deleteUrl = trigger?.getAttribute('data-delete-url');
        const editUrl = trigger?.getAttribute('data-edit-url');

        activePostId = postId ?? null;
        activeDeleteUrl = deleteUrl ?? null;
        activeEditUrl = editUrl ?? null;

        dropdown.setAttribute('data-post-id', activePostId ?? '');

        dropdown.removeAttribute('hidden');
        overlay.removeAttribute('hidden');

        window.requestAnimationFrame(() => {
            dropdown.classList.add('is-active');
            overlay.classList.add('is-active');
        });

        document.body.classList.add('dropdown-open');

        ensureSwipeBound(dropdown, closeDropdown);
    }

    function closeDropdown() {
        if (!dropdown || !overlay) {
            return;
        }

        dropdown.classList.remove('is-active');
        overlay.classList.remove('is-active');

        activePostId = null;
        activeDeleteUrl = null;
        activeEditUrl = null;
        document.body.classList.remove('dropdown-open');

        window.setTimeout(() => {
            if (!dropdown.classList.contains('is-active')) {
                dropdown.setAttribute('hidden', 'hidden');
                overlay.setAttribute('hidden', 'hidden');
            }
        }, DROPDOWN_TRANSITION);
    }

    function openComments(postId) {
        if (!commentsSheet || !commentsOverlay) {
            return;
        }

        commentsSheet.setAttribute('data-post-id', String(postId));
        commentsSheet.removeAttribute('hidden');
        commentsOverlay.removeAttribute('hidden');

        window.requestAnimationFrame(() => {
            commentsSheet.classList.add('is-active');
            commentsOverlay.classList.add('is-active');
        });

        document.body.classList.add('dropdown-open');

        // Attach swipe-to-close
        attachSwipeToSheet(commentsSheet, closeComments);

        loadComments(postId);
    }

    function closeComments() {
        if (!commentsSheet || !commentsOverlay) {
            return;
        }

        commentsSheet.classList.remove('is-active');
        commentsOverlay.classList.remove('is-active');

        document.body.classList.remove('dropdown-open');

        window.setTimeout(() => {
            if (!commentsSheet.classList.contains('is-active')) {
                commentsSheet.setAttribute('hidden', 'hidden');
                commentsOverlay.setAttribute('hidden', 'hidden');
            }
        }, DROPDOWN_TRANSITION);
    }

    function renderCommentsSkeleton() {
        const wrap = document.querySelector('#commentsSheet [data-comments-wrap]');
        if (!wrap) return;
        wrap.innerHTML = '';
        for (let i = 0; i < 5; i++) {
            wrap.insertAdjacentHTML('beforeend', `
                <div class="comment comment--mod">
                    <div class="comment__cap"></div>
                    <div class="comment__user">
                        <div class="comment__cap"></div>
                        <div class="comment__cap"></div>
                    </div>
                </div>
            `);
        }
    }

    function renderCommentsEmpty() {
        const wrap = document.querySelector('#commentsSheet [data-comments-wrap]');
        if (!wrap) return;
        wrap.innerHTML = `
            <div class="comments__info">
                <h4 class="comments__subtitle">Aún no hay comentarios</h4>
                <p class="comments__text">Empieza la conversación.</p>
            </div>
        `;
    }

    function renderCommentsList(items) {
        const wrap = document.querySelector('#commentsSheet [data-comments-wrap]');
        if (!wrap) return;
        wrap.innerHTML = '';
        items.forEach((c) => {
            wrap.insertAdjacentHTML('beforeend', commentMarkup(c));
        });
        // ensure delete button sticks to the far right
        wrap.querySelectorAll('.comment').forEach((el) => {
            const delBtn = el.querySelector('[data-comment-delete]');
            if (delBtn) {
                delBtn.style.marginLeft = 'auto';
            }
        });
    }

    function commentMarkup(c) {
        const name = c.author_display_name ?? 'Anónimo';
        const time = c.created_at ? new Date(c.created_at).toLocaleString() : 'now';
        const body = (c.body ?? '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const idAttr = c.id ? `data-comment-id="${c.id}"` : '';
        const admin = Boolean(document.body?.dataset?.isAdmin);
        return `
            <div class="comment" ${idAttr}>
                <img class="comment__icon" src="/images/avatar-placeholder.svg" alt="icon">
                <div class="comment__user">
                    <span class="frendi-block__time">${time}</span>
                    <h3 class="comment__user_name">${name}</h3>
                    <p class="comment__user_text" data-comment-body>${body}</p>
                </div>
                ${admin && c.id ? `<button class=\"comment__button\" data-comment-edit aria-label=\"Edit\"></button>
                <button class=\"comment__button\" data-comment-delete aria-label=\"Delete\"></button>` : ''}
            </div>
        `;
    }

    async function loadComments(postId) {
        renderCommentsSkeleton();
        try {
            const res = await fetch(`/api/posts/${postId}/comments`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Failed to load');
            const data = await res.json();
            const items = Array.isArray(data?.data) ? data.data : [];
            if (!items.length) {
                renderCommentsEmpty();
            } else {
                renderCommentsList(items);
                bindAdminCommentActions(postId);
            }
        } catch (e) {
            renderCommentsEmpty();
        }
    }

    function bindAdminCommentActions(postId) {
        const isAdmin = Boolean(document.body?.dataset?.isAdmin);
        if (!isAdmin) return;
        const sheet = document.getElementById('commentsSheet');
        if (!sheet) return;
        sheet.querySelectorAll('[data-comment-edit]').forEach((btn) => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const commentEl = btn.closest('[data-comment-id]');
                const commentId = commentEl?.getAttribute('data-comment-id');
                const bodyEl = commentEl?.querySelector('[data-comment-body]');
                if (!commentId || !bodyEl) return;
                const currentText = bodyEl.textContent || '';
            const next = prompt('Editar comentario:', currentText);
                if (next == null) return;
                bodyEl.textContent = next;
                try {
                    const endpoint = isAdmin ? `/admin/posts/${postId}/comments/${commentId}` : `/api/posts/${postId}/comments/${commentId}`;
                    const res = await fetch(endpoint, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                        body: JSON.stringify({ body: next })
                    });
                    if (!res.ok) throw new Error('Update failed');
                } catch (_) {
                    // reload on failure to keep consistency
                    loadComments(postId);
                }
            });
        });
        sheet.querySelectorAll('[data-comment-delete]').forEach((btn) => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const commentEl = btn.closest('[data-comment-id]');
                const commentId = commentEl?.getAttribute('data-comment-id');
                if (!commentId) return;
                const confirmed = confirm('¿Eliminar comentario?');
                if (!confirmed) return;
                commentEl.remove();
                adjustCommentsCounter(postId, -1);
                try {
                    const endpoint = isAdmin ? `/admin/posts/${postId}/comments/${commentId}` : `/api/posts/${postId}/comments/${commentId}`;
                    const res = await fetch(endpoint, {
                        method: 'DELETE',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                    });
                    if (!res.ok) throw new Error('Delete failed');
                } catch (_) {
                    loadComments(postId);
                }
            });
        });

        // Inline edit by clicking on comment text (admin only)
        if (!sheet.__inlineEditBound) {
            sheet.addEventListener('click', (e) => {
                const bodyEl = e.target?.closest?.('[data-comment-body]');
                if (!bodyEl) return;
                const isAdminNow = Boolean(document.body?.dataset?.isAdmin);
                if (!isAdminNow) return;
                const commentEl = bodyEl.closest('[data-comment-id]');
                const cid = commentEl?.getAttribute('data-comment-id');
                const pid = sheet.getAttribute('data-post-id');
                if (!cid || !pid) return;
                if (commentEl.__editing) return;
                commentEl.__editing = true;

                const originalText = (bodyEl.textContent || '').trim();
                const input = document.createElement('input');
                input.type = 'text';
                input.value = originalText;
                input.className = 'comments__add_input';
                input.style.width = '100%';
                input.setAttribute('maxlength', '1000');

                bodyEl.replaceWith(input);
                input.focus();
                input.setSelectionRange(input.value.length, input.value.length);

                const cancel = () => {
                    const p = document.createElement('p');
                    p.className = 'comment__user_text';
                    p.setAttribute('data-comment-body', '');
                    p.textContent = originalText;
                    input.replaceWith(p);
                    commentEl.__editing = false;
                };

                const save = async () => {
                    const next = input.value.trim();
                    const p = document.createElement('p');
                    p.className = 'comment__user_text';
                    p.setAttribute('data-comment-body', '');
                    p.textContent = next;
                    input.replaceWith(p);
                    commentEl.__editing = false;
                    try {
                        const res = await fetch(`/admin/posts/${pid}/comments/${cid}`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                            body: JSON.stringify({ body: next })
                        });
                        if (!res.ok) throw new Error('Update failed');
                    } catch (_) {
                        // revert on failure
                        p.textContent = originalText;
                    }
                };

                input.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        save();
                    } else if (ev.key === 'Escape') {
                        ev.preventDefault();
                        cancel();
                    }
                });
                input.addEventListener('blur', () => {
                    // Save on blur
                    save();
                });
            });
            sheet.__inlineEditBound = true;
        }
    }

    function attachSwipeToSheet(sheetEl, onClose) {
        if (!sheetEl) return;

        let startY = 0;
        let currentY = 0;
        let dragging = false;
        const THRESHOLD = 60;
        const MAX_TRANSLATE = 200;

        if (sheetEl.__swipeBound) return; // prevent double binding
        sheetEl.__swipeBound = true;

        const onTouchStart = (e) => {
            const touch = e.touches ? e.touches[0] : e;
            startY = touch.clientY;
            currentY = startY;

            // If touch starts on actionable element (button/link), do not start sheet drag
            const actionable = e.target.closest && e.target.closest('button, a, [role="button"], .drop-down__item');
            if (actionable) {
                dragging = false;
                return;
            }

            // If touch starts inside a scrollable content (e.g., comments list), do not start sheet drag
            const scrollable = e.target && (e.target.closest && e.target.closest('.comments__wrap'));
            if (scrollable && scrollable.scrollHeight > scrollable.clientHeight) {
                dragging = false;
                return;
            }

            dragging = true;
            sheetEl.style.transition = 'none';
        };

        const onTouchMove = (e) => {
            if (!dragging) return;
            const touch = e.touches ? e.touches[0] : e;
            currentY = touch.clientY;
            const delta = Math.max(0, Math.min(MAX_TRANSLATE, currentY - startY));
            if (delta > 0) {
                // Prevent iOS pull-to-refresh while dragging the sheet
                e.preventDefault();
                sheetEl.style.transform = `translate(-50%, ${delta}px)`;
            }
        };

        const onTouchEnd = () => {
            if (!dragging) return;
            const delta = currentY - startY;
            dragging = false;
            sheetEl.style.transition = '';
            sheetEl.style.transform = '';
            if (delta > THRESHOLD) {
                onClose();
            }
        };

        // Bind once per open
        sheetEl.addEventListener('touchstart', onTouchStart, { passive: true });
        sheetEl.addEventListener('touchmove', onTouchMove, { passive: false });
        sheetEl.addEventListener('touchend', onTouchEnd, { passive: true });

        // Also support mouse drag for desktop testing
        sheetEl.addEventListener('mousedown', onTouchStart);
        window.addEventListener('mousemove', onTouchMove);
        window.addEventListener('mouseup', onTouchEnd, { once: true });
    }

    // Delegated event for opening dropdown menu (works for dynamically loaded posts)
    document.addEventListener('click', (event) => {
        const button = event.target.closest('.frendi-block__points');
        if (!button) return;
        event.preventDefault();
        openDropdown(button);
    });

    // Like toggle (delegated)
    document.addEventListener('click', async (event) => {
        const el = event.target.closest && event.target.closest('[data-like]');
        if (!el) return;
        event.preventDefault();
        if (el.__busy) return; el.__busy = true;
        const postId = el.closest('[data-post-id]')?.getAttribute('data-post-id') || el.getAttribute('data-post-id');
        if (!postId) { el.__busy = false; return; }
        const counter = el.querySelector('.frendi-block__item_num');
        try {
                const tokenMeta = document.querySelector('meta[name="x-client-token"]');
                const payload = new URLSearchParams();
                payload.set('type', 'like');
                const res = await fetch(`/api/posts/${postId}/reactions`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Client-Token': tokenMeta?.content || ''
                    },
                    body: payload.toString()
                });
            if (res.ok) {
                const data = await res.json();
                if (counter && typeof data?.likes_count === 'number') {
                    counter.textContent = String(data.likes_count);
                }
                if (data && 'user_reaction' in data) {
                    if (data.user_reaction === 'like') { el.classList.add('is-liked'); }
                    else { el.classList.remove('is-liked'); }
                }
                
                // GA: pet like (only when actually liked, not unliked)
                if (data && data.user_reaction === 'like') {
                    try{
                        const card = el.closest('.frendi-block');
                        const type = card?.getAttribute('data-post-type') || '';
                        console.log('[GA4] Like detected, post_type:', type, 'post_id:', postId);
                        if (type === 'pet') {
                            console.log('[GA4] Sending pet_like event for post:', postId);
                            trackEvent('pet_like', { post_id: postId });
                        }
                    }catch(err){
                        console.error('[GA4] Error sending pet_like:', err);
                    }
                }
            }
        } catch (_) {
            // ignore; keep previous value
        } finally {
            el.__busy = false;
        }
    });

    // Like heart animation
    (function(){
        const HEART_SVG = '<svg width="96" height="88" viewBox="0 0 24 22" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.57831 8.50903 2.99871 7.05 2.99871C5.59096 2.99871 4.19169 3.57831 3.16 4.61C2.1283 5.64169 1.54871 7.04097 1.54871 8.5C1.54871 9.95903 2.1283 11.3583 3.16 12.39L4.22 13.45L12 21.23L19.78 13.45L20.84 12.39C21.351 11.8792 21.7563 11.2728 22.0329 10.6053C22.3095 9.93789 22.4518 9.22248 22.4518 8.5C22.4518 7.77752 22.3095 7.06211 22.0329 6.39464C21.7563 5.72718 21.351 5.12075 20.84 4.61Z"/></svg>';
        function spawnHeart(card){
            const s = document.createElement('div');
            s.className = 'like-splash';
            s.innerHTML = HEART_SVG;
            card.appendChild(s);
            setTimeout(()=>{ s.remove(); }, 850);
        }
        document.addEventListener('click', (e)=>{
            const like = e.target.closest && e.target.closest('[data-like]');
            if (!like) return;
            const card = like.closest('.frendi-block');
            if (!card) return;
            spawnHeart(card);
        }, { passive: true });
    })();

    function openShare(url) {
        if (!shareSheet || !shareOverlay) return;
        const input = shareSheet.querySelector('[data-share-input]');
        if (input) {
            input.value = url;
        }
        shareSheet.removeAttribute('hidden');
        shareOverlay.removeAttribute('hidden');
        window.requestAnimationFrame(() => {
            shareSheet.classList.add('is-active');
            shareOverlay.classList.add('is-active');
        });
        document.body.classList.add('dropdown-open');

        attachSwipeToSheet(shareSheet, closeShare);
    }

    function closeShare() {
        if (!shareSheet || !shareOverlay) return;
        shareSheet.classList.remove('is-active');
        shareOverlay.classList.remove('is-active');
        document.body.classList.remove('dropdown-open');
        window.setTimeout(() => {
            if (!shareSheet.classList.contains('is-active')) {
                shareSheet.setAttribute('hidden', 'hidden');
                shareOverlay.setAttribute('hidden', 'hidden');
            }
        }, DROPDOWN_TRANSITION);
    }

    const shareCopyBtn = document.querySelector('#shareSheet [data-share-copy]');
    shareCopyBtn?.addEventListener('click', async () => {
        const input = document.querySelector('#shareSheet [data-share-input]');
        const url = input?.value || '';
        if (!url) return;
        await navigator.clipboard?.writeText?.(url);
        input?.select?.();

        // Try to open native share picker right after user gesture
        if (navigator.share) {
            try {
                await navigator.share({ url });
                return;
            } catch (_) {}
        }
        // Android Chrome: try Intent scheme to open chooser
        if (/Android/i.test(navigator.userAgent)) {
            try {
                window.location.href = `intent:${encodeURIComponent(url)}#Intent;action=android.intent.action.SEND;S.browser_fallback_url=${encodeURIComponent(url)};scheme=https;type=text/plain;end`;
                return;
            } catch (_) {}
        }
    });

    const shareNativeBtn = document.querySelector('#shareSheet [data-share-native]');
    shareNativeBtn?.addEventListener('click', async () => {
        const input = document.querySelector('#shareSheet [data-share-input]');
        const url = input?.value || '';
        if (!url) return;
        if (navigator.share) {
            try {
                await navigator.share({ url });
                return;
            } catch (_) {}
        }
        // Fallback to opening the url (user can pick app from browser UI)
        window.open(url, '_blank', 'noopener');
    });

    // Share providers config
    const SHARE_PROVIDERS = [
        {
            name: 'Telegram',
            icon: '/images/drop-down-icon2.svg',
            buildUrl: ({ url, title }) => `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`,
        },
        {
            name: 'WhatsApp',
            icon: '/images/drop-down-icon2.svg',
            buildUrl: ({ url, title }) => `https://api.whatsapp.com/send?text=${encodeURIComponent(title)}%20${encodeURIComponent(url)}`,
        },
        {
            name: 'VK',
            icon: '/images/drop-down-icon2.svg',
            buildUrl: ({ url, title }) => `https://vk.com/share.php?url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}`,
        },
        {
            name: 'OK',
            icon: '/images/drop-down-icon2.svg',
            buildUrl: ({ url, title }) => `https://connect.ok.ru/offer?url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}`,
        },
        {
            name: 'Twitter/X',
            icon: '/images/drop-down-icon2.svg',
            buildUrl: ({ url, title }) => `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`,
        },
        {
            name: 'Email',
            icon: '/images/drop-down-icon2.svg',
            buildUrl: ({ url, title }) => `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(url)}`,
        },
    ];

    function renderShareActions(payload) {
        const container = document.querySelector('#shareSheet [data-share-actions]');
        if (!container) return;
        container.innerHTML = '';
        SHARE_PROVIDERS.forEach((p) => {
            const btn = document.createElement('a');
            btn.className = 'drop-down__item';
            btn.href = p.buildUrl({ url: payload.rawUrl || payload.url, title: payload.rawTitle || payload.title });
            btn.target = '_blank';
            btn.rel = 'noopener';
            btn.innerHTML = `
                <img class="drop-down__item_icon" src="${p.icon}" alt="icon">
                <h2 class="drop-down__item_title">${p.name}</h2>
            `;
            container.appendChild(btn);
        });
    }

    function getSharePayload(fromEl) {
        const card = fromEl.closest('.frendi-block');
        const url = fromEl.getAttribute('data-share-url') || card?.querySelector('a[href]')?.href || window.location.href;
        const title = card?.querySelector('.frendi-block__title')?.textContent?.trim() || document.title;
        return { rawUrl: url, url: url, rawTitle: title, title: title };
    }

    // Share link (delegated - works for dynamically loaded posts)
    document.addEventListener('click', async (event) => {
        const el = event.target.closest('[data-share]');
        if (!el) return;
        event.preventDefault();
        const payload = getSharePayload(el);
        
        // GA: share (generic) + legacy my_dog_share mapping — track BEFORE native share
        try{
            const card = el.closest('.frendi-block');
            const type = card?.getAttribute('data-post-type') || '';
            const postId = card?.getAttribute('data-post-id') || '';
            if (postId) trackEvent('share', { post_id: postId, post_type: type });
            if (type === 'my_dog' || type === 'pet') trackEvent('my_dog_share', { post_id: postId });
        }catch(_){/* noop */}
        
        try {
            if (navigator.share) {
                try {
                    await navigator.share({ title: payload.title, url: payload.url });
                    return;
                } catch (_) {}
            }
            // Fallback: open our share sheet with providers
            openShare(payload.url);
            renderShareActions(payload);
            // Focus first action for a11y
            const firstBtn = document.querySelector('#shareSheet [data-share-actions] .drop-down__item');
            firstBtn?.focus?.();
        } catch (e) {
            openShare(payload.url);
            renderShareActions(payload);
        }
    });

    if (overlay) {
        overlay.addEventListener('click', closeDropdown);
        // Allow pull-to-refresh on overlay by default
    }

    if (commentsOverlay) {
        commentsOverlay.addEventListener('click', closeComments);
    }

    if (shareOverlay) {
        shareOverlay.addEventListener('click', () => closeShare());
    }

    // Contest winner sheet
    (function(){
        if (!contestSheet) return;
        const periodInput = contestSheet.querySelector('[data-contest-period]');
        const okBtn = contestSheet.querySelector('[data-contest-ok]');
        const cancelBtn = contestSheet.querySelector('[data-contest-cancel]');
        document.addEventListener('click', (e)=>{
            const btn = e.target.closest && e.target.closest('[data-open-contest]');
            if (!btn) return;
            const pid = btn.getAttribute('data-post-id');
            const commentsSheet = document.getElementById('commentsSheet');
            const commentsOverlay = document.getElementById('commentsOverlay');
            // Переиспользуем готовую механику comments как вы просили
            if (commentsSheet && commentsOverlay) {
                commentsSheet.setAttribute('data-post-id', pid);
                const body = commentsSheet.querySelector('[data-comments-body]');
                if (body) body.innerHTML = `
                    <h2 class="comments__title">Ganadores</h2>
                    <div class="drop-down__wrap winners-period-form">
                        <label class="new-post__label">Período</label>
                        <input class="new-post__input" type="text" placeholder="2025‑09‑15 — 2025‑09‑22" data-contest-period>
                        <div style="display:flex;gap:12px;justify-content:center;margin-top:8px;">
                            <button class="button" type="button" data-contest-ok>Aceptar</button>
                            <button class="button button-white" type="button" data-contest-cancel>Cancelar</button>
                        </div>
                    </div>`;
                commentsSheet.removeAttribute('hidden');
                commentsOverlay.removeAttribute('hidden');
                window.requestAnimationFrame(()=>{ commentsSheet.classList.add('is-active'); commentsOverlay.classList.add('is-active'); });
                const cancel = commentsSheet.querySelector('[data-contest-cancel]');
                const ok = commentsSheet.querySelector('[data-contest-ok]');
                const periodInput = commentsSheet.querySelector('[data-contest-period]');
                const close = ()=>{ commentsSheet.classList.remove('is-active'); commentsOverlay.classList.remove('is-active'); setTimeout(()=>{commentsSheet.setAttribute('hidden',''); commentsOverlay.setAttribute('hidden','');}, 300); };
                cancel?.addEventListener('click', close, { once:true });

                // Load flatpickr dynamically and init range picker
                (async function initRange(){
                    function injectCssOnce(href){ if(document.querySelector('link[data-flatpickr]')) return; const l=document.createElement('link'); l.rel='stylesheet'; l.href=href; l.setAttribute('data-flatpickr',''); document.head.appendChild(l); }
                    function injectScriptOnce(src){ return new Promise((resolve)=>{ if(window.flatpickr){ resolve(); return;} const s=document.createElement('script'); s.src=src; s.async=true; s.onload=()=>resolve(); document.head.appendChild(s); }); }
                    injectCssOnce('https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css');
                    await injectScriptOnce('https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js');
                    if (window.flatpickr && periodInput) {
                        window.flatpickr(periodInput, {
                            mode: 'range',
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'd.m.Y',
                            allowInput: true,
                            onClose: function(selectedDates, dateStr, instance){
                                if (selectedDates && selectedDates.length===2){
                                    const fmt = (d)=> instance.formatDate(d, 'Y-m-d');
                                    periodInput.value = fmt(selectedDates[0]) + ' — ' + fmt(selectedDates[1]);
                                }
                            }
                        });
                    }
                })();

                ok?.addEventListener('click', async ()=>{
                    const period = (periodInput?.value||'').trim(); let start=null,end=null,label='Ganador de la semana pasada';
                    const m = period.split('—'); if (m.length===2){ start=m[0].trim(); end=m[1].trim(); }
                    try {
                        const res = await fetch('/admin/contest', { method:'POST', headers:{ 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||'' }, body: JSON.stringify({ post_id: pid, period_label: label, period_start: start||null, period_end: end||null }) });
                        if(!res.ok) throw new Error('fail');
                    } catch(_) {}
                    close();
                }, { once:true });
            }
        });
        function closeContest(){ contestSheet.classList.remove('is-active'); overlay?.classList.remove('is-active'); window.setTimeout(()=>{contestSheet.setAttribute('hidden',''); overlay?.setAttribute('hidden',''); }, DROPDOWN_TRANSITION); }
        cancelBtn?.addEventListener('click', closeContest);
        okBtn?.addEventListener('click', async ()=>{
            const pid = contestSheet.getAttribute('data-post-id');
            const period = (periodInput?.value || '').trim();
            // Simple parse: split by dash
            let start=null,end=null,label='Ganador de la semana pasada';
            const m = period.split('—');
            if (m.length===2){ start=m[0].trim(); end=m[1].trim(); }
            try{
                const res = await fetch('/admin/contest', { method:'POST', headers:{ 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||'' }, body: JSON.stringify({ post_id: pid, period_label: label, period_start: start||null, period_end: end||null }) });
                if(!res.ok) throw new Error('fail');
                closeContest();
            }catch(_){ closeContest(); }
        });
    })();

    // Pull-to-refresh is prevented during drag in touchmove; do not block touchstart to keep buttons clickable
    // const sheets = [dropdown, commentsSheet, shareSheet].filter(Boolean);
    // sheets.forEach((sheet) => {
    //     sheet.addEventListener('touchstart', () => {}, { passive: true });
    // });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdown();
            closeComments();
            closeShare();
        }
    });

    if (dropdown) {
        dropdown.addEventListener('click', (event) => {
            const item = event.target.closest('.drop-down__item');
            if (!item) {
                return;
            }

            event.preventDefault();

            const action = item.getAttribute('data-action');

            if (action === 'delete' && deleteForm && activeDeleteUrl) {
                deleteForm.setAttribute('action', activeDeleteUrl);
                deleteForm.submit();
            } else if (action === 'edit' && activeEditUrl && activeEditUrl !== '#') {
                window.location.href = activeEditUrl;
            }

            if (action && activePostId) {
                const customEvent = new CustomEvent('post:dropdown-action', {
                    detail: { action, postId: activePostId },
                });
                document.dispatchEvent(customEvent);
            }

            closeDropdown();
        });
    }

    // Delegated event for opening comments (works for dynamically loaded posts)
    document.addEventListener('click', (event) => {
        const el = event.target.closest('[data-open-comments]');
        if (!el) return;
        event.preventDefault();
        const postId = el.getAttribute('data-post-id');
        if (postId) {
            openComments(postId);
        }
    });

    // Send comment
    const sendBtn = document.querySelector('#commentsSheet [data-comment-send]');
    const inputEl = document.querySelector('#commentsSheet [data-comment-input]');

    function adjustCommentsCounter(postId, delta) {
        const article = document.querySelector(`.frendi-block[data-post-id="${postId}"]`);
        if (!article) return;
        const counterEl = article.querySelector('.frendi-block__item[data-open-comments] .frendi-block__item_num');
        if (!counterEl) return;
        const current = parseInt((counterEl.textContent || '0').trim(), 10) || 0;
        const next = Math.max(0, current + delta);
        counterEl.textContent = String(next);
    }

    sendBtn?.addEventListener('click', async () => {
        const postId = commentsSheet?.getAttribute('data-post-id');
        const body = inputEl?.value?.trim();
        if (!postId || !body) return;
        try {
            // Get user name from body data attribute
            const userName = document.body?.getAttribute('data-user-name') || null;
            
            // Optimistic render
            const optimistic = { body, author_display_name: userName, created_at: new Date().toISOString() };
            const wrap = document.querySelector('#commentsSheet [data-comments-wrap]');
            if (wrap) {
                if (wrap.querySelector('.comments__info')) wrap.innerHTML = '';
                wrap.insertAdjacentHTML('beforeend', commentMarkup(optimistic));
            }
            inputEl.value = '';
            adjustCommentsCounter(postId, 1);

            const payload = { body };
            if (userName) payload.author_display_name = userName;

            const res = await fetch(`/api/posts/${postId}/comments`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                body: JSON.stringify(payload)
            });
            // If failed, reload and revert counter to get consistent state
            if (!res.ok) {
                adjustCommentsCounter(postId, -1);
                loadComments(postId);
            }
            // GA: pet comment
            try{
                const card = document.querySelector(`.frendi-block[data-post-id="${postId}"]`);
                const type = card?.getAttribute('data-post-type') || '';
                if (type === 'pet') trackEvent('pet_comment', { post_id: postId });
            }catch(_){/* noop */}
        } catch (e) {
            adjustCommentsCounter(postId, -1);
            loadComments(postId);
        }
    });

    // GA: track create post button click
    console.log('[GA4] Create post click handler registered');
    document.addEventListener('click', (event) => {
        console.log('[GA4] Document click detected, target:', event.target);
        const el = event.target.closest('[data-create-post]');
        console.log('[GA4] Closest [data-create-post]:', el);
        
        if (!el) return;
        
        event.preventDefault();
        event.stopPropagation();
        
        const url = el.href;
        console.log('[GA4] Create post button clicked! href:', url);
        console.log('[GA4] gtag available?', typeof window.gtag === 'function');
        
        // Send GA event with callback
        if (typeof window.gtag === 'function') {
            console.log('[GA4] 📤 SENDING EVENT: create_post_click via gtag...');
            try {
                window.gtag('event', 'create_post_click', {
                    event_callback: function() {
                        console.log('[GA4] ✅ create_post_click EVENT SENT! Navigating to:', url);
                        window.location.href = url;
                    },
                    event_timeout: 500
                });
                console.log('[GA4] ⏳ create_post_click queued, waiting for callback...');
            } catch(err) {
                console.error('[GA4] ❌ gtag error:', err);
                window.location.href = url;
            }
        } else {
            console.warn('[GA4] ⚠️ gtag not available, navigating immediately');
            window.location.href = url;
        }
    }, true); // Use capture phase
    
    console.log('[GA4] Checking for [data-create-post] elements:', document.querySelectorAll('[data-create-post]').length);
});
