@extends('layouts.app')

@section('content')
<section class="frendi">
    <div class="frendi__content _container">
        <div class="new-post__top" style="gap:12px;">
            <a href="{{ route('admin.dashboard') }}" aria-label="Back">
                <svg width="12" height="20" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.610352 10.0068C0.615885 9.81315 0.654622 9.63607 0.726562 9.47559C0.798503 9.3151 0.90918 9.16016 1.05859 9.01074L9.37598 0.958984C9.61393 0.721029 9.90723 0.602051 10.2559 0.602051C10.4883 0.602051 10.6986 0.657389 10.8867 0.768066C11.0804 0.878743 11.2326 1.02816 11.3433 1.21631C11.4595 1.40446 11.5176 1.61475 11.5176 1.84717C11.5176 2.19027 11.3875 2.49186 11.1274 2.75195L3.60693 9.99854L11.1274 17.2534C11.3875 17.519 11.5176 17.8206 11.5176 18.1582C11.5176 18.3962 11.4595 18.6092 11.3433 18.7974C11.2326 18.9855 11.0804 19.1349 10.8867 19.2456C10.6986 19.3618 10.4883 19.4199 10.2559 19.4199C9.90723 19.4199 9.61393 19.2982 9.37598 19.0547L1.05859 11.0029C0.903646 10.8535 0.790202 10.6986 0.718262 10.5381C0.646322 10.3721 0.610352 10.195 0.610352 10.0068Z" fill="black"/></svg>
            </a>
            <h2 class="new-post__title">Métricas</h2>
        </div>

        <article class="frendi-block">
            <div class="frendi-block__top">
                <div class="frendi-block__user">
                    <img class="frendi-block__user_icon" src="{{ asset('images/user-icon2.png') }}" alt="frendi.com">
                    <div class="frendi-block__user_wrap">
                        <h2 class="frendi-block__user_name">frendi.com</h2>
                        <div class="frendi-block__user_admin">Administrador</div>
                    </div>
                </div>
            </div>
            <div class="frendi-block__title">Parámetros de prueba</div>
            <ul class="frendi-block__text" style="display:flex;flex-direction:column;gap:10px;">
                <li><strong>Rutas</strong>: {{ $percentRouteUsers }}% de usuarios añadieron ruta ({{ $routeUsers }}/{{ $totalUsers }}); {{ $percentRouteUsersWithPhoto }}% añadieron foto ({{ $routeUsersWithPhoto }}/{{ $totalUsers }}).</li>
                <li><strong>Fotos de animales</strong>: {{ $percentPetsLikes }}% de me gusta ({{ $petsLikesCount }}/{{ $totalPetPosts }} publicaciones); {{ $percentPetsComments }}% de comentarios ({{ $petsCommentsCount }}/{{ $totalPetPosts }} publicaciones).</li>
                <li><strong>Mi perro</strong>: {{ $percentMydogShares }}% de reenvíos de enlace único ({{ $mydogSharesCount }}/{{ $totalMyDogPosts }} publicaciones).</li>
                <li><strong>Concursos</strong>: participantes — {{ $contestsParticipants }}, vistas de publicaciones — {{ $postsViews }}.</li>
            </ul>
        </article>

        <div class="frendi-block__bottom" style="margin-top:16px;">
            <div class="frendi-block__coll">
                <a class="button" href="{{ route('admin.dashboard') }}">Atrás</a>
            </div>
        </div>
    </div>
</section>
@endsection 