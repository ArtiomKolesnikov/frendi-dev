@php
    /** @var \App\Models\Post|null $post */
    $isEdit = isset($post);
    $typeLabels = [
        \App\Models\Post::TYPE_ROUTE => 'Ruta / misión',
        \App\Models\Post::TYPE_PET => 'Foto de mascota',
        \App\Models\Post::TYPE_MY_DOG => 'Mi perro en el feed',
        \App\Models\Post::TYPE_CONTEST => 'Concurso',
    ];
    $removed = collect(old('remove_media', []))->map(fn($value) => (int) $value);
    $avatarPath = old('avatar_path', $post->meta['avatar_path'] ?? null);
    $defaultType = $preselectedType ?? \App\Models\Post::TYPE_PET;
@endphp

<form class="new-post__form" method="post" action="{{ $isEdit ? route('posts.update', $post) : route('posts.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="new-post__photo_block" id="avatarUpload" data-close-icon="{{ asset('images/close.svg') }}">
            <input type="file" id="avatarInput" name="avatar" accept="image/*">
            @if($avatarPath)
                <img class="new-post__card_img" src="{{ asset('storage/'.$avatarPath) }}" alt="avatar" style="object-fit:cover;">
                @if($isEdit)
                    <input type="hidden" name="existing_avatar_path" value="{{ $avatarPath }}">
                @endif
                <img class="new-post__card_close" src="{{ asset('images/close.svg') }}" alt="Eliminar" data-remove-avatar>
            @else
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.3333 5.33331C17.0285 5.33331 15.6201 5.33331 13.5208 5.33331C13.4916 5.33331 13.4622 5.33331 13.4327 5.33331C12.9422 5.33331 12.4718 5.52816 12.125 5.87498C11.3912 6.60881 10.8469 7.15311 10 7.99998H5.13807C4.83624 7.99998 4.54676 8.11988 4.33333 8.33331C4.1199 8.54674 4 8.83622 4 9.13805V24.9213C4 25.1922 4.06309 25.4595 4.18426 25.7018C4.47991 26.2931 5.08426 26.6666 5.74536 26.6666H25.7239C26.3275 26.6666 26.9065 26.4268 27.3333 26C27.7602 25.5731 28 24.9942 28 24.3905V14.6666" stroke="black" stroke-width="2" stroke-linecap="round"/>
                    <path d="M28 1.66699C28.5523 1.66699 29 2.11471 29 2.66699V4.33301H30.666C31.2182 4.33301 31.6658 4.78087 31.666 5.33301C31.666 5.88529 31.2183 6.33301 30.666 6.33301H29V8C28.9998 8.55214 28.5522 9 28 9C27.4478 9 27.0002 8.55214 27 8V6.33301H25.333C24.7809 6.33283 24.333 5.88518 24.333 5.33301C24.3332 4.78098 24.781 4.33318 25.333 4.33301H27V2.66699C27 2.11471 27.4477 1.66699 28 1.66699Z" fill="black"/>
                    <path d="M16 11.6667C18.7614 11.6667 21 13.9053 21 16.6667C21 19.4281 18.7614 21.6667 16 21.6667C13.2386 21.6667 11 19.4281 11 16.6667C11 13.9053 13.2386 11.6667 16 11.6667Z" stroke="black" stroke-width="2"/>
                </svg>
            @endif
        </div>

    <div class="new-post__field">
        <label class="new-post__label" for="type">Tipo de publicación</label>
        <select class="new-post__select" id="type" name="type">
            @foreach (\App\Models\Post::TYPES as $type)
                <option value="{{ $type }}" @selected(old('type', $post->type ?? $defaultType) === $type)>
                    {{ $typeLabels[$type] ?? ucfirst($type) }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- No tocar por ahora y no añadir -->
    @if(0)
        <div class="new-post__field contest-field" @if(old('type', $post->type ?? \App\Models\Post::TYPE_PET) !== \App\Models\Post::TYPE_CONTEST) hidden @endif>
            <label class="new-post__label" for="contest_status">Estado del concurso</label>
            <select class="new-post__select" id="contest_status" name="contest_status">
                <option value="new" @selected(old('contest_status', $post->meta['contest_status'] ?? '') === 'new')>Nuevo</option>
                <option value="past" @selected(old('contest_status', $post->meta['contest_status'] ?? '') === 'past')>Pasado</option>
            </select>
        </div>
    @endif

    <div class="new-post__field">
        <label class="new-post__label" for="title">Título</label>
        <input class="new-post__input" id="title" type="text" name="title" value="{{ old('title', $post->title ?? '') }}" placeholder="Por ejemplo, ¡Perro perdido!">
    </div>

    <div class="new-post__two-columns">
        <div class="new-post__field">
            <label class="new-post__label" for="author_display_name">Nombre del autor</label>
            <input class="new-post__input" id="author_display_name" type="text" name="author_display_name" value="{{ old('author_display_name', $post->author_display_name ?? '') }}" placeholder="Tu nombre">
        </div>
        <div class="new-post__field">
            <label class="new-post__label" for="author_contact">Contacto (opcional)</label>
            <input class="new-post__input" id="author_contact" type="text" name="author_contact" value="{{ old('author_contact', $post->author_contact ?? '') }}" placeholder="Teléfono o red social">
        </div>
    </div>

    <div class="new-post__photo">
        <!-- Avatar upload first -->
        

        <div class="new-post__photo_block block-media" id="photoUpload" data-close-icon="{{ asset('images/close.svg') }}">
            <input type="file" id="fileInput" name="media[]" multiple accept="image/*">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.3333 5.33331C17.0285 5.33331 15.6201 5.33331 13.5208 5.33331C13.4916 5.33331 13.4622 5.33331 13.4327 5.33331C12.9422 5.33331 12.4718 5.52816 12.125 5.87498C11.3912 6.60881 10.8469 7.15311 10 7.99998H5.13807C4.83624 7.99998 4.54676 8.11988 4.33333 8.33331C4.1199 8.54674 4 8.83622 4 9.13805V24.9213C4 25.1922 4.06309 25.4595 4.18426 25.7018C4.47991 26.2931 5.08426 26.6666 5.74536 26.6666H25.7239C26.3275 26.6666 26.9065 26.4268 27.3333 26C27.7602 25.5731 28 24.9942 28 24.3905V14.6666" stroke="black" stroke-width="2" stroke-linecap="round"/>
                <path d="M28 1.66699C28.5523 1.66699 29 2.11471 29 2.66699V4.33301H30.666C31.2182 4.33301 31.6658 4.78087 31.666 5.33301C31.666 5.88529 31.2183 6.33301 30.666 6.33301H29V8C28.9998 8.55214 28.5522 9 28 9C27.4478 9 27.0002 8.55214 27 8V6.33301H25.333C24.7809 6.33283 24.333 5.88518 24.333 5.33301C24.3332 4.78098 24.781 4.33318 25.333 4.33301H27V2.66699C27 2.11471 27.4477 1.66699 28 1.66699Z" fill="black"/>
                <path d="M16 11.6667C18.7614 11.6667 21 13.9053 21 16.6667C21 19.4281 18.7614 21.6667 16 21.6667C13.2386 21.6667 11 19.4281 11 16.6667C11 13.9053 13.2386 11.6667 16 11.6667Z" stroke="black" stroke-width="2"/>
            </svg>
        </div>

        <div class="new-post__slider">
            @if($isEdit && $post->media->count())
                <div class="photo-slider swiper new-post__media-swiper" data-media-swiper="existing">
                    <div class="swiper-wrapper">
                        @foreach($post->media as $media)
                            @php $isMarked = $removed->contains($media->id); @endphp
                            <div class="swiper-slide">
                                <div class="media-preview {{ $isMarked ? 'is-marked' : '' }}" data-media-preview="{{ $media->id }}">
                                    <input type="checkbox" name="remove_media[]" value="{{ $media->id }}" data-remove-media @checked($isMarked)>
                                    <img class="new-post__card_close" src="{{ asset('images/close.svg') }}" alt="Eliminar" data-toggle-remove>
                                    <img class="new-post__card_img" src="{{ asset('storage/'.$media->path) }}" alt="media">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-scrollbar"></div>
                </div>
            @endif

            
            <!-- Swiper for new (pending) images -->
            <div class="photo-slider swiper new-post__media-swiper new-post__media-swiper--new" data-media-swiper="main">
                <div class="swiper-wrapper"></div>
                <div class="swiper-scrollbar"></div>
            </div>
        </div>
    </div>

    <div class="new-post__field new-post-description">
        <textarea class="new-post__textarea" id="body" name="body" placeholder="Añade la descripción de la publicación">{{ old('body', $post->body ?? '') }}</textarea>
    </div>

    <button class="button button-green" type="submit">{{ $isEdit ? 'Guardar' : 'Compartir' }}</button>
</form>
