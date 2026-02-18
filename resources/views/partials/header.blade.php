<header class="header">
    <div class="header__content _container">
        <div class="header__body">
            <div class="header__brand">
                <a class="header__logo" href="{{ route('feed') }}" aria-label="логотип">Frendi</a>
                @if(session()->has('admin_id'))
                    <span class="frendi-block__user_admin header__admin-badge">Administrador</span>
                @endif
            </div>
            <div class="header__wrap">
                <div class="header__button">
                    <a class="button button-green" href="{{ route('posts.create') }}" data-create-post>Nueva publicación</a>
                </div>
            </div>
        </div>
    </div>
</header>
