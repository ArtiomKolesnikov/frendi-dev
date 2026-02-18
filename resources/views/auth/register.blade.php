@extends('layouts.app')

@section('content')
<section class="new-post">
    <div class="new-post__content _container">
        <div class="new-post__top">
            <a href="{{ route('feed') }}" aria-label="Назад">
                <svg width="12" height="20" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.610352 10.0068C0.615885 9.81315 0.654622 9.63607 0.726562 9.47559C0.798503 9.3151 0.90918 9.16016 1.05859 9.01074L9.37598 0.958984C9.61393 0.721029 9.90723 0.602051 10.2559 0.602051C10.4883 0.602051 10.6986 0.657389 10.8867 0.768066C11.0804 0.878743 11.2326 1.02816 11.3433 1.21631C11.4595 1.40446 11.5176 1.61475 11.5176 1.84717C11.5176 2.19027 11.3875 2.49186 11.1274 2.75195L3.60693 9.99854L11.1274 17.2534C11.3875 17.519 11.5176 17.8206 11.5176 18.1582C11.5176 18.3962 11.4595 18.6092 11.3433 18.7974C11.2326 18.9855 11.0804 19.1349 10.8867 19.2456C10.6986 19.3618 10.4883 19.4199 10.2559 19.4199C9.90723 19.4199 9.61393 19.2982 9.37598 19.0547L1.05859 11.0029C0.903646 10.8535 0.790202 10.6986 0.718262 10.5381C0.646322 10.3721 0.610352 10.195 0.610352 10.0068Z" fill="black"/>
                </svg>
            </a>
            <h2 class="new-post__title">Registro</h2>
        </div>

        @if ($errors->any())
            <x-alert>
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form class="new-post__form" method="post" action="{{ route('user.register.submit') }}">
            @csrf
            <div class="new-post__field">
                <label class="new-post__label" for="name">Nombre</label>
                <input class="new-post__input" id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Nombre" autocomplete="name">
            </div>
            <div class="new-post__field">
                <label class="new-post__label" for="email">Correo</label>
                <input class="new-post__input" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Correo" autocomplete="email">
            </div>
            <div class="new-post__field">
                <label class="new-post__label" for="password">Contraseña</label>
                <input class="new-post__input" id="password" type="password" name="password" placeholder="Contraseña" autocomplete="new-password">
            </div>
            <div class="new-post__field">
                <label class="new-post__label" for="password_confirmation">Confirmar contraseña</label>
                <input class="new-post__input" id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirmar contraseña" autocomplete="new-password">
            </div>
            <button class="button button-green" type="submit">Crear cuenta</button>
            <a class="button button-green" href="{{ route('user.login') }}">Iniciar sesión</a>

        </form>

        <div class="new-post__field" style="margin-top:16px;">
            <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px;">
                <li><a href="{{ route('legal.cookies') }}" class="link">Política de cookies</a></li>
                <li><a href="{{ route('legal.privacy') }}" class="link">Política de privacidad</a></li>
                <li><a href="{{ route('legal.terms') }}" class="link">Términos de uso</a></li>
            </ul>
        </div>
    </div>
</section>
@endsection 
@push('styles')
<style>
.link{ text-decoration:none; color:var(--color-green); font-weight:600; font-size:16px; }
</style>
@endpush