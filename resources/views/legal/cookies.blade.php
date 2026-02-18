@extends('layouts.app')

@section('content')
<section class="new-post legal-doc">
    <div class="new-post__content _container">
        <div class="new-post__top">
            <a href="{{ url()->previous() }}" aria-label="Atrás">
                <svg width="12" height="20" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.610352 10.0068C0.615885 9.81315 0.654622 9.63607 0.726562 9.47559C0.798503 9.3151 0.90918 9.16016 1.05859 9.01074L9.37598 0.958984C9.61393 0.721029 9.90723 0.602051 10.2559 0.602051C10.4883 0.602051 10.6986 0.657389 10.8867 0.768066C11.0804 0.878743 11.2326 1.02816 11.3433 1.21631C11.4595 1.40446 11.5176 1.61475 11.5176 1.84717C11.5176 2.19027 11.3875 2.49186 11.1274 2.75195L3.60693 9.99854L11.1274 17.2534C11.3875 17.519 11.5176 17.8206 11.5176 18.1582C11.5176 18.3962 11.4595 18.6092 11.3433 18.7974C11.2326 18.9855 11.0804 19.1349 10.8867 19.2456C10.6986 19.3618 10.4883 19.4199 10.2559 19.4199C9.90723 19.4199 9.61393 19.2982 9.37598 19.0547L1.05859 11.0029C0.903646 10.8535 0.790202 10.6986 0.718262 10.5381C0.646322 10.3721 0.610352 10.195 0.610352 10.0068Z" fill="black"/>
                </svg>
            </a>
            <h2 class="new-post__title">Política de cookies</h2>
        </div>

        <div class="new-post__field">
            <div class="new-post__label" style="font-weight:400;">
                <p><strong>Digital Gate</strong><br><br><strong>Política de cookies</strong></p>
                <p>En Digital Gate somos transparentes sobre cómo recopilamos y procesamos datos. Esta página explica nuestras prácticas con cookies y cómo gestionarlas.</p><br>
                <p><strong>Quieres saber más sobre las cookies y cómo las usamos?</strong><br>Con gusto lo explicamos. Sigue leyendo.</p>
                <p><em>Nota: Esta Política de cookies no describe cómo tratamos tus datos personales fuera del uso de cookies. Para saber más sobre cómo tratamos tu información personal, consulta nuestra <a href="{{ route('legal.privacy') }}">Política de privacidad</a> en nuestro sitio <a href="https://myfriendi.com" target="_blank" rel="noopener">https://myfriendi.com</a>.</em></p>

                    <br><p><strong>Qué son las cookies?</strong><br>Son pequeños archivos de texto que se envían al navegador o se almacenan en la memoria del dispositivo. Suelen incluir el dominio de origen, la vida útil y un identificador único. Pueden contener ajustes del dispositivo, historial y actividad.</p>

                    <br><p><strong>Tipos de cookies</strong><br><strong>De origen y de terceros:</strong> las de origen las colocamos nosotros; las de terceros, nuestros socios (ver herramientas de consentimiento).<br><br> <strong>De sesión y persistentes:</strong> las de sesión expiran al cerrar el navegador; las persistentes permanecen más tiempo para, por ejemplo, mantener la sesión o para analítica.</p>

                    <br><p><strong>Otras tecnologías</strong><br>Balizas web, píxeles, etiquetas, SDKs y URLs de seguimiento funcionan de forma similar; en este documento nos referimos a todas como “cookies”.</p>

                    <br><p><strong>Para qué usamos cookies?</strong><br>Para prestar, proteger y mejorar el servicio: recordar preferencias, reconocerte al volver, medir campañas y personalizar anuncios. Podemos vincular la info de cookies con otros datos que tengamos.</p>

                    <br><p><strong>Tipos y descripciones</strong><br><strong>Esenciales:</strong> inicio de sesión, preferencias, seguridad.<br><strong>Analíticas:</strong> entender uso y mejorar el servicio.<br><br><strong>Publicidad y marketing:</strong> eficacia de campañas y relevancia de anuncios.<br><strong>Redes sociales:</strong> compartir contenido y, en su caso, fines publicitarios.</p>

                    <br><p><strong>Cómo controlar las cookies?</strong><br>
                    <strong>Nuestras herramientas:</strong> puedes ajustar tus preferencias de cookies en nuestro sitio <a href="https://myfriendi.com" target="_blank" rel="noopener">https://myfriendi.com</a> y, si aplica, en los ajustes de tu cuenta en la app.<br>
                    <strong>Navegador y dispositivo:</strong> la mayoría permiten bloquear/avisar sobre cookies; consulta la ayuda del navegador o del dispositivo. También puedes restablecer identificadores del dispositivo u optar por no permitir su recopilación desde los ajustes del mismo.<br><br>
                    <strong>Publicidad basada en intereses:</strong> puedes optar por no recibir anuncios personalizados en los programas de autorregulación: <a href="http://optout.aboutads.info/?c=2#!/" target="_blank" rel="noopener">Digital Advertising Alliance</a> <a href="http://www.youronlinechoices.eu/" target="_blank" rel="noopener">EDAA</a> <a href="http://youradchoices.com/appchoices" target="_blank" rel="noopener">AppChoices</a> Ten en cuenta que seguirás viendo anuncios, pero no personalizados por esas redes. Si borras cookies tras el opt‑out, tendrás que repetirlo.</p>

                <br><p><strong>Google Analytics y Google Maps</strong><br>Usamos Google Analytics; consulta cómo Google procesa datos en <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener">policies.google.com/technologies/partner-sites</a> el opt‑out del navegador en <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener">tools.google.com/dlpage/gaoptout</a> y la configuración de personalización de anuncios en <a href="https://adssettings.google.com/" target="_blank" rel="noopener">adssettings.google.com</a><br>Al usar funciones que dependen de Google Maps, aceptas sus cookies; más info en <a href="https://www.google.com/policies/technologies/cookies/" target="_blank" rel="noopener">google.com/policies/technologies/cookies/</a></p>

                <br><p><strong>Contacto</strong><br>Si tienes preguntas sobre esta Política de cookies:<br>Por correo:<br>2433, Prime Tower, Business Bay, Dubai, UAE
                +971 58 58 3 22 66<br> <a href="mailto:stan@digitalgate.pro">stan@digitalgate.pro</a></p>
            </div>
        </div>
    </div>
</section>
@push('styles')
<style>
    .new-post__label{
        text-align: justify;
    }
    .new-post__field {
        display: block;
    }
.legal-doc { font-family: 'TildaSans', Arial, sans-serif; font-size: 18px; line-height: 1.55; }
.legal-doc a { text-decoration: none; color: var(--color-green); font-weight: 600; }
.legal-doc a:hover { color: var(--color-green); opacity: .85; }
.legal-doc .new-post__content._container{ max-width: var(--container-width); padding: 0 15px; }
</style>
@endpush
@endsection


