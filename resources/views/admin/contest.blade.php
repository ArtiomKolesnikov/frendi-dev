@extends('layouts.app')

@section('content')
<section class="frendi">
    <div class="frendi__content _container">
        <div class="new-post__top" style="gap:12px;">
            <h2 class="new-post__title">Ganadores</h2>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="post" action="{{ route('admin.contest.store') }}" class="contest-form">
            @csrf
            <div>
                <label class="new-post__label">Publicación ganadora</label>
                <select name="post_id" class="new-post__input" required>
                    @foreach($posts as $p)
                        <option value="{{ $p->id }}">#{{ $p->id }} — {{ $p->title ?? Str::limit($p->body, 40) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="new-post__label">Etiqueta del período</label>
                <input type="text" name="period_label" class="new-post__input" placeholder="Ganador de la semana pasada">
            </div>
            <div>
                <label class="new-post__label">Inicio</label>
                <input type="datetime-local" name="period_start" class="new-post__input">
            </div>
            <div>
                <label class="new-post__label">Fin</label>
                <input type="datetime-local" name="period_end" class="new-post__input">
            </div>
            <div>
                <button type="submit" class="button">Save</button>
            </div>
        </form>

        <h3 style="margin:24px 0 12px;">Winners</h3>
        <style>
            .contest-form{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;align-items:end}
            .contest-form .new-post__input,.contest-form select,.contest-form input{width:100%}
            @media (max-width: 640px){
                .contest-form{grid-template-columns:1fr;}
            }
        </style>
        <ul>
            @foreach($winners as $w)
                <li>#{{ $w->id }} — Post #{{ $w->post_id }} — {{ $w->period_label }} ({{ $w->period_start }} - {{ $w->period_end }})</li>
            @endforeach
        </ul>
        {{ $winners->links() }}
    </div>
</section>
@endsection 