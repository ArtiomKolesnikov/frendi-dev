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
            <h2 class="new-post__title">Términos de uso</h2>
        </div>

        <div class="new-post__field">
            <div class="new-post__label" style="font-weight:400;">
                <p><strong>Digital Gate</strong><br><br><strong>Términos de uso</strong></p>
                <p><strong>Última revisión:</strong> 21 de agosto de 2023</p>
                <p>Bienvenido a Digital Gate. Dirección: 2433, Prime Tower, Business Bay, Dubái, EAU · Tel.: +971 58 58 3 22 66 · Email: <a href="mailto:stan@digitalgate.pro">stan@digitalgate.pro</a></p>

                <br><p><strong>Aceptación del Acuerdo de Términos de uso</strong><br>Al crear una cuenta (en móvil, app o web) aceptas estos Términos. Si no estás de acuerdo, por favor, no utilices el Servicio. Podemos actualizar este Acuerdo y el Servicio para reflejar cambios legales, nuevas funciones o prácticas de negocio. La versión vigente se publica en el Servicio; si hay cambios materiales en tus derechos u obligaciones, te avisaremos con antelación cuando la ley lo exija.</p>

                <br><p><strong>Elegibilidad</strong></p>
                <ul>
                    <li>Ser mayor de 18 años.</li>
                    <li>Poder celebrar un contrato vinculante.</li>
                    <li>No estar inhabilitado para usar el Servicio por la ley aplicable.</li>
                    <li>Cumplir este Acuerdo y toda ley local, nacional e internacional aplicable.</li>
                    <li>No haber sido condenado por delitos graves ni figurar en registros de delincuentes sexuales.</li>
                </ul>

                <p><strong>Tu cuenta</strong><br>Para usar el Servicio debes registrarte y proporcionar credenciales. Eres responsable de mantener la confidencialidad de tus datos de acceso y de toda actividad bajo tu cuenta. Si sospechas acceso no autorizado, contáctanos de inmediato: <a href="mailto:stan@digitalgate.pro">stan@digitalgate.pro</a></p>

                <br><p><strong>Modificación del Servicio y terminación</strong><br>Podemos añadir o retirar funciones o, en casos excepcionales, suspender el Servicio. Intentaremos notificar cambios materiales cuando sea posible. Para terminar tu cuenta, sigue las instrucciones en “Ajustes”. Si usas cuentas de pago de terceros (p. ej., App Store o Google Play), gestiona las compras dentro de esas plataformas.</p>

                <p><strong>Seguridad; tus interacciones con otros miembros</strong><br>Fomentamos interacciones respetuosas, pero no somos responsables de la conducta de los miembros dentro o fuera del Servicio. Actúa con precaución, no compartas información financiera ni envíes dinero a otros usuarios. Digital Gate no realiza verificaciones de antecedentes y no garantiza la compatibilidad entre miembros.</p>

                <br><p><strong>Derechos que Digital Gate te concede</strong><br>Te otorgamos una licencia personal, mundial, gratuita, no exclusiva, revocable e intransferible para acceder y usar el Servicio según estos Términos. Esta licencia se revoca si incumples este Acuerdo.</p>

                <p><strong>Derechos que concedes a Digital Gate</strong><br>Al publicar contenido, concedes a Digital Gate una licencia mundial, transferible, sublicenciable, libre de royalties para alojar, usar, copiar, mostrar, reproducir, adaptar, publicar, modificar y distribuir tu contenido con el único fin de operar, mantener y mejorar el Servicio y desarrollar nuevos servicios, conforme a la ley aplicable.</p>

                <br><p><strong>Reglas de la comunidad</strong></p>
                <ul>
                    <li>No uses el Servicio con fines ilegales o prohibidos.</li>
                    <li>No dañes, acoses, suplantes, intimides ni difames a nadie.</li>
                    <li>No publiques contenido que infrinja derechos (publicidad, privacidad, copyright, marca, contrato), incite al odio o la violencia, o sea explícito sexualmente.</li>
                    <li>No solicites contraseñas ni datos personales para fines comerciales o ilícitos.</li>
                    <li>No uses cuentas ajenas ni mantengas múltiples cuentas.</li>
                </ul>

                <p><strong>Contenido de otros miembros</strong><br>Cada miembro es responsable de su propio contenido. Si detectas infracciones, repórtalas dentro del Servicio o en <a href="mailto:stan@digitalgate.pro">stan@digitalgate.pro</a></p>

                <br><p><strong>Aviso y procedimiento para reclamaciones de copyright</strong><br>Si consideras que tu obra se ha publicado infringiendo tus derechos, envíanos un aviso incluyendo: (i) firma del titular o representante; (ii) descripción de la obra; (iii) localización del material infractor en el Servicio; (iv) datos de contacto; (v) declaración de buena fe; (vi) declaración bajo juramento de exactitud y legitimación. Contacto: <a href="mailto:stan@digitalgate.pro">stan@digitalgate.pro</a></p>

                <br><p><strong>Descargos de responsabilidad</strong><br>EL SERVICIO SE OFRECE «TAL CUAL» Y «SEGÚN DISPONIBILIDAD», SIN GARANTÍAS EXPRESAS O IMPLÍCITAS EN LA MEDIDA PERMITIDA POR LA LEY</p>

                <p><strong>Servicios de terceros</strong><br>Puede haber anuncios o enlaces a sitios de terceros. Sus términos regirán tu relación con ellos. No somos responsables de sus actos o condiciones.</p>

                <br><p><strong>Limitación de responsabilidad</strong><br>En la máxima medida permitida por la ley, Digital Gate y sus afiliados no serán responsables de daños indirectos, incidentales, especiales, punitivos o consecuentes, ni de pérdidas de datos, uso, fondo de comercio o beneficios derivados del uso del Servicio.</p>

                <p><strong>Ley aplicable y jurisdicción</strong><br>Salvo normas imperativas de protección al consumidor, este Acuerdo se rige por la ley del Emirato de Dubái y las leyes de los Emiratos Árabes Unidos. Las controversias se someterán a los tribunales competentes de Dubái, EAU.</p>

                <br><p><strong>Indemnización</strong><br>En la medida permitida por la ley, te comprometes a indemnizar y mantener indemne a Digital Gate, sus afiliados, directivos, agentes y empleados frente a reclamaciones derivadas de tu uso del Servicio, tu contenido o el incumplimiento de este Acuerdo.</p>

                <p><strong>Acuerdo íntegro; otros</strong><br>Este Acuerdo (incluyendo la Política de privacidad y la Política de cookies y cualesquiera términos adicionales aplicables) constituye el acuerdo completo entre tú y Digital Gate respecto al Servicio. Si alguna disposición es inválida, el resto seguirá plenamente vigente. Tu cuenta no es transferible.</p>

                <br><p><strong>Contacto</strong><br>Digital Gate · 2433, Prime Tower, Business Bay, Dubái, EAU · +971 58 58 3 22 66 · <a href="mailto:stan@digitalgate.pro">stan@digitalgate.pro</a></p>
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


