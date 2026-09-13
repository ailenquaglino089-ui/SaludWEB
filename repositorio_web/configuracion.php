<?php
// Inicio del bloque PHP: el servidor ejecuta este fragmento antes de generar el HTML.
// Copia de la vista web de configuración y preferencias.
// Calcula la ruta base del proyecto para los enlaces
// rtrim() recorta la barra "/" final que deja dirname() al obtener la carpeta actual.
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');  // Ruta raíz del proyecto usada en todos los enlaces (ej: "/Organizacion_Modulos")
// Fin del bloque PHP: a partir de aquí el archivo es HTML puro que se envía al navegador.
?>
<!-- Página de configuración / preferencias del usuario (vista legacy del proyecto) -->
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Metadatos: codificación de caracteres UTF-8 y viewport para diseño responsive -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Título que se muestra en la pestaña del navegador -->
    <title>Ajustes | SaludWEB</title>
    <!-- Bloque de estilos CSS embebidos de la página de configuración -->
    <style>
        /* ================================================================
           ESTILOS CSS - Página de configuración / ajustes
           ================================================================ */

        /* Variables de diseño y paleta de colores */
        :root {
            --primary: #4f46e5;   /* Color principal: Índigo */
            --bg: #f8fafc;        /* Color de fondo */
            --text: #1e293b;      /* Color del texto */
            --success: #10b981;   /* Color para estados operacionales */
        }

        /* Estilos generales */
        /* body: define la fuente, el fondo, el color de texto y el espaciado de toda la página */
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); padding: 20px; margin: 0; }
        /* .container: centra el contenido y limita su ancho máximo a 1000px */
        .container { max-width: 1000px; margin: 0 auto; }

        /* Encabezado con logotipo y botón de retorno */
        /* .header: distribuye en una fila el logo (izquierda) y las acciones (derecha) */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        /* .logo-container: agrupa horizontalmente la caja del ícono y el texto "Ajustes" */
        .logo-container { display: flex; align-items: center; gap: 10px; }
        /* .logo-box: caja cuadrada con el ícono ⚙️ y el color de fondo principal */
        .logo-box { background: var(--primary); color: white; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 20px; font-weight: bold; }
        /* .logo-text: estilo del título grande del encabezado */
        .logo-text { font-size: 24px; font-weight: 800; }
        /* .back-link: botón con borde para volver a la pantalla de inicio */
        .back-link { text-decoration: none; color: var(--primary); font-weight: 800; padding: 10px 20px; border-radius: 12px; background: white; border: 2px solid var(--primary); }

        /* Menú de navegación con pestañas */
        /* .nav-menu: cuadrícula adaptable que distribuye las pestañas de navegación */
        .nav-menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 30px; }
        /* .nav-item: estilo de cada enlace/pestaña del menú */
        .nav-item { padding: 12px 16px; border-radius: 10px; background: white; text-decoration: none; color: var(--text); font-weight: 700; text-align: center; border: 2px solid #e2e8f0; }
        /* Al pasar el mouse, o en la pestaña de la página actual, se invierten los colores */
        .nav-item:hover, .nav-item.active { background: var(--primary); color: white; }  /* active = página actual */

        /* Contenido principal con secciones */
        /* .content: tarjeta blanca que contiene todas las secciones de ajustes */
        .content { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
        /* .section: separación vertical entre cada sección de la página */
        .section { margin-bottom: 32px; }
        /* .section-title: título de cada sección, acompañado de su ícono */
        .section-title { font-size: 20px; font-weight: 800; margin: 0 0 16px 0; display: flex; align-items: center; gap: 10px; }
        /* .btn-close-section: botón "✕" que permite ocultar/eliminar la sección */
        .btn-close-section { margin-left: auto; background: none; border: none; cursor: pointer; font-size: 18px; color: #94a3b8; padding: 4px 8px; border-radius: 6px; }
        /* Al pasar el mouse sobre el botón de cerrar, se pinta de rojo */
        .btn-close-section:hover { background: #fee2e2; color: #ef4444; }
        /* .section.hidden: oculta la sección cuando JavaScript le agrega la clase "hidden" */
        .section.hidden { display: none; }

        /* Grupos de configuración (filas con toggles) */
        /* .settings-group: caja gris que agrupa cada preferencia de configuración */
        .settings-group { background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 2px solid #e2e8f0; }
        /* .settings-row: fila que separa la etiqueta del control (checkbox/toggle) */
        .settings-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; }
        /* .settings-label: texto que describe cada preferencia */
        .settings-label { font-weight: 700; }

        /* Botones genéricos */
        /* .btn: clase base de todos los botones de la página */
        .btn { display: inline-block; padding: 12px 20px; border-radius: 10px; border: none; cursor: pointer; font-weight: 800; text-decoration: none; font-size: 14px; }
        /* .btn-primary: botón con el color principal (índigo) y texto blanco */
        .btn-primary { background: var(--primary); color: white; }
        /* .btn-secondary: botón gris claro de aspecto neutro */
        .btn-secondary { background: #f1f5f9; color: #1e293b; border: 2px solid #cbd5e1; }
        /* .btn-danger: botón rojo reservado para acciones destructivas (ej: eliminar cuenta) */
        .btn-danger { background: #ef4444; color: white; }

        /* Iconos de contacto (WhatsApp / Email) */
        /* .contact-icons: fila horizontal con los accesos de contacto */
        .contact-icons { display: flex; gap: 12px; margin-top: 16px; }
        /* .social-link: círculo que contiene cada ícono de contacto */
        .social-link { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 24px; border: 2px solid #e2e8f0; }
        /* .whatsapp-link: fondo verde oficial de WhatsApp */
        .whatsapp-link { background: #25d366; color: white; }
        /* .gmail-link: fondo rojo oficial de Gmail/Email */
        .gmail-link { background: #ea4335; color: white; }

        /* Caja informativa */
        /* .info-box: recuadro con borde izquierdo índigo para mostrar información o avisos */
        .info-box { background: #eef2ff; border-left: 4px solid var(--primary); padding: 14px 16px; border-radius: 12px; }

        /* Toast de notificación */
        /* .toast: notificación flotante fija en la esquina inferior derecha */
        .toast { position: fixed; bottom: 30px; right: 30px; background: #10b981; color: white; padding: 16px 24px; border-radius: 12px; font-weight: 700; box-shadow: 0 8px 24px rgba(0,0,0,0.15); display: none; z-index: 9999; animation: slideUp 0.3s ease; }
        /* Variante .toast.error: versión en rojo para mensajes de error */
        .toast.error { background: #ef4444; }
        /* Animación slideUp: el toast ingresa deslizándose desde abajo y con transparencia */
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* Modal */
        /* .modal-overlay: fondo oscuro semitransparente que cubre toda la pantalla; oculto por defecto */
        .modal-overlay { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.4); align-items: center; justify-content: center; }
        /* Cuando el modal posee la clase "open", se muestra centrado en la pantalla */
        .modal-overlay.open { display: flex; }
        /* .modal-box: caja blanca del diálogo; habilita scroll si el contenido es muy alto */
        .modal-box { background: white; border-radius: 16px; padding: 32px; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.15); max-height: 80vh; overflow-y: auto; }
        /* Título del modal sin margen superior */
        .modal-box h2 { margin-top: 0; }
        /* Párrafos del modal con color e interlineado legibles */
        .modal-box p { color: #475569; line-height: 1.6; }

        /* Responsive */
        /* En pantallas de hasta 768px de ancho se apilan el encabezado y el menú de navegación */
        @media (max-width: 768px) {
            .header { flex-direction: column; align-items: flex-start; }
            .nav-menu { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<!-- Contenedor principal: centra el contenido limitando su ancho -->
<div class="container">

    <!-- ============================================================
    ENCABEZADO
    ============================================================ -->
    <!-- Encabezado: logo y título a la izquierda, botón "HOME" a la derecha -->
    <div class="header">
        <div class="logo-container">
            <!-- Caja con el ícono de engranaje (⚙️) -->
            <div class="logo-box">⚙️</div>
            <!-- Título grande del encabezado -->
            <div class="logo-text">Ajustes</div>
        </div>
        <!-- Contenedor de las acciones del encabezado -->
        <div class="header-actions">
            <!-- Enlace para volver al inicio (HOME); $basePath es generado por PHP -->
            <a href="<?php echo $basePath; ?>/notificaciones" class="back-link">← HOME</a>
        </div>
    </div>

    <!-- Menú de navegación principal -->
    <!-- Cuadrícula de pestañas para desplazarse entre las secciones del sistema -->
    <div class="nav-menu">
        <a href="<?php echo $basePath; ?>/" class="nav-item">🏠 Home</a>
        <a href="<?php echo $basePath; ?>/prescripciones" class="nav-item">📄 Prescripciones</a>
        <a href="<?php echo $basePath; ?>/prestaciones" class="nav-item">🏥 Prestaciones</a>
        <a href="<?php echo $basePath; ?>/notificaciones" class="nav-item">🔔 Notificaciones</a>
        <!-- Pestaña "Ajustes": lleva la clase "active" porque es la página actual -->
        <a href="<?php echo $basePath; ?>/configuracion" class="nav-item active">⚙️ Ajustes</a>
        <a href="<?php echo $basePath; ?>/cuenta" class="nav-item">👤 Cuenta</a>
        <a href="<?php echo $basePath; ?>/faq" class="nav-item">❓ FAQ</a>
        <!-- Enlace de salida: usa estilo rojo para diferenciarse de la navegación normal -->
        <a href="<?php echo $basePath; ?>/logout" class="nav-item" style="border-color:#ef4444; color:#ef4444;">🔒 Salir</a>
    </div>

    <!-- Toast de notificación -->
    <!-- Contenedor vacío que JavaScript utiliza para mostrar mensajes temporales -->
    <div class="toast" id="toast"></div>

    <!-- Modal para FAQ / Documentación -->
    <!-- Superposición modal; su título y cuerpo los rellena JavaScript dinámicamente -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-box">
            <h2 id="modalTitle"></h2>
            <div id="modalBody"></div>
            <!-- Botón para cerrar el modal -->
            <button class="btn btn-secondary" onclick="cerrarModal()" style="margin-top:16px;">Cerrar</button>
        </div>
    </div>

    <!-- ============================================================
    CONTENIDO PRINCIPAL: secciones de configuración
    ============================================================ -->
    <!-- Tarjeta principal que agrupa todas las secciones de ajustes -->
    <div class="content">

        <!-- Sección: Notificaciones -->
        <!-- Cada sección usa su atributo data-seccion para poder ocultarse o restaurarse desde JavaScript -->
        <div class="section" data-seccion="notificaciones">
            <h2 class="section-title">🔔 Notificaciones
                <!-- Botón ✕: oculta la sección y registra la preferencia -->
                <button class="btn-close-section" onclick="eliminarSeccion('notificaciones')" title="Eliminar sección">✕</button>
            </h2>
            <!-- Grupo de preferencias: avisos de prescripciones -->
            <div class="settings-group">
                <div class="settings-row">
                    <span class="settings-label">Avisos de prescripciones</span>
                    <!-- Checkbox que guarda la preferencia cada vez que cambia de estado -->
                    <input type="checkbox" id="chkPrescripciones" onchange="guardarPreferencia('avisos_prescripciones', this.checked)">
                </div>
            </div>
            <!-- Grupo de preferencias: recordatorios de medicamentos -->
            <div class="settings-group">
                <div class="settings-row">
                    <span class="settings-label">Recordatorios de medicamentos</span>
                    <!-- Checkbox que guarda la preferencia cada vez que cambia de estado -->
                    <input type="checkbox" id="chkRecordatorios" onchange="guardarPreferencia('recordatorios', this.checked)">
                </div>
            </div>
        </div>

        <!-- Sección: Privacidad y Seguridad -->
        <div class="section" data-seccion="privacidad">
            <h2 class="section-title">🔒 Privacidad y Seguridad
                <button class="btn-close-section" onclick="eliminarSeccion('privacidad')" title="Eliminar sección">✕</button>
            </h2>
            <!-- Grupo que muestra el estado de la autenticación de dos factores -->
            <div class="settings-group">
                <div class="settings-row">
                    <span class="settings-label">Autenticación de dos factores</span>
                    <!-- Texto que refleja el estado actual del 2FA (Activado / Desactivado) -->
                    <span class="settings-value" id="estado2fa">Desactivado</span>
                </div>
            </div>
            <!-- Botón que activa/desactiva el 2FA según su estado actual -->
            <button class="btn btn-primary" id="btn2fa" onclick="toggle2FA()">🔐 Activar 2FA</button>
        </div>

        <!-- Sección: Contacto y Soporte -->
        <div class="section" data-seccion="contacto">
            <h2 class="section-title">📞 Contacto y Soporte
                <button class="btn-close-section" onclick="eliminarSeccion('contacto')" title="Eliminar sección">✕</button>
            </h2>
            <!-- Íconos de contacto: acceso directo a WhatsApp y a Email -->
            <div class="contact-icons">
                <!-- Enlace a WhatsApp en pestaña nueva -->
                <a href="https://wa.me/541234567890" target="_blank" class="social-link whatsapp-link" title="WhatsApp">💬</a>
                <!-- Enlace para redactar un correo a soporte -->
                <a href="mailto:soporte@saludweb.com" class="social-link gmail-link" title="Email">✉️</a>
            </div>
            <!-- Datos de contacto en texto plano -->
            <p style="color: #64748b;">WhatsApp: +54 9 11 1234-5678 | Email: soporte@saludweb.com</p>
        </div>

        <!-- Sección: Centro de Ayuda -->
        <div class="section" data-seccion="ayuda">
            <h2 class="section-title">❓ Centro de Ayuda
                <button class="btn-close-section" onclick="eliminarSeccion('ayuda')" title="Eliminar sección">✕</button>
            </h2>
            <!-- Botones que abren el modal con las preguntas frecuentes -->
            <button class="btn btn-secondary" onclick="abrirFAQ()">📖 Ver Preguntas Frecuentes</button>
            <!-- Botón que abre el modal con la documentación del sistema -->
            <button class="btn btn-secondary" onclick="abrirDocumentacion()">📚 Ver Documentación</button>
        </div>

        <!-- Sección: Administración de datos -->
        <div class="section" data-seccion="datos">
            <h2 class="section-title">📊 Mis Datos
                <button class="btn-close-section" onclick="eliminarSeccion('datos')" title="Eliminar sección">✕</button>
            </h2>
            <!-- Botón rojo que, con doble confirmación, elimina la cuenta -->
            <button class="btn btn-danger" onclick="eliminarCuenta()">🗑️ Eliminar Cuenta</button>
        </div>

        <!-- Botón para restaurar secciones eliminadas -->
        <!-- Se muestra únicamente cuando existe al menos una sección oculta -->
        <div id="restaurarSecciones" style="display:none; text-align:center; margin-top:16px;">
            <button class="btn btn-secondary" onclick="restaurarSecciones()">↩️ Restaurar secciones eliminadas</button>
        </div>

    </div>

</div>

<script>
    // ============================================================
    // Cargar preferencias guardadas al iniciar
    // ============================================================
    // Lee las preferencias del usuario desde localStorage y las aplica a la interfaz al cargar la página.
    function cargarPreferencias() {
        // Obtiene el JSON guardado en localStorage; si no existe, usa un objeto vacío '{}'.
        const prefs = JSON.parse(localStorage.getItem('saludweb_prefs') || '{}');
        // Marca el checkbox de avisos de prescripciones según lo guardado (por defecto está activo).
        document.getElementById('chkPrescripciones').checked = prefs.avisos_prescripciones !== false;
        // Marca el checkbox de recordatorios según lo guardado (por defecto está activo).
        document.getElementById('chkRecordatorios').checked = prefs.recordatorios !== false;

        // Si el usuario tiene el 2FA activado, se refleja en el estado y en el botón.
        if (prefs.dosfa_activado) {
            // Muestra "Activado" en la etiqueta de estado.
            document.getElementById('estado2fa').textContent = 'Activado';
            // Cambia el texto del botón para ofrecer desactivar el 2FA.
            document.getElementById('btn2fa').textContent = '🔐 Desactivar 2FA';
            // Cambia el estilo del botón al rojo (acción de peligro).
            document.getElementById('btn2fa').className = 'btn btn-danger';
        }

        // Ocultar secciones eliminadas
        // Recupera la lista (array) de secciones que el usuario decidió ocultar.
        const seccionesEliminadas = prefs.secciones_eliminadas || [];
        // Recorre cada ID de sección eliminada.
        seccionesEliminadas.forEach(function(id) {
            // Busca la sección por su atributo data-seccion.
            var el = document.querySelector('[data-seccion="' + id + '"]');
            // Si la sección existe, le agrega la clase "hidden" para ocultarla.
            if (el) el.classList.add('hidden');
        });
        // Si existe al menos una sección oculta, muestra el botón de restaurar.
        if (seccionesEliminadas.length > 0) {
            document.getElementById('restaurarSecciones').style.display = 'block';
        }
    }

    // ============================================================
    // Eliminar sección
    // ============================================================
    // Oculta una sección de la página y registra el cambio en localStorage.
    function eliminarSeccion(id) {
        // Pide confirmación al usuario; si cancela, la función termina sin hacer nada.
        if (!confirm('¿Eliminar esta sección? Podés restaurarla después.')) return;
        // Busca el elemento de la sección por su atributo data-seccion.
        var el = document.querySelector('[data-seccion="' + id + '"]');
        // Si la sección existe, la oculta agregándole la clase "hidden".
        if (el) el.classList.add('hidden');
        // Lee las preferencias actuales desde localStorage.
        const prefs = JSON.parse(localStorage.getItem('saludweb_prefs') || '{}');
        // Se asegura de que exista el array de secciones eliminadas.
        prefs.secciones_eliminadas = prefs.secciones_eliminadas || [];
        // Si el ID todavía no estaba en la lista, lo agrega.
        if (prefs.secciones_eliminadas.indexOf(id) === -1) {
            prefs.secciones_eliminadas.push(id);
        }
        // Vuelve a guardar las preferencias actualizadas en localStorage.
        localStorage.setItem('saludweb_prefs', JSON.stringify(prefs));
        // Muestra el botón que permite restaurar las secciones.
        document.getElementById('restaurarSecciones').style.display = 'block';
        // Notifica al usuario con un mensaje flotante.
        mostrarToast('Sección eliminada');
    }

    // ============================================================
    // Restaurar secciones
    // ============================================================
    // Vuelve a mostrar todas las secciones ocultas y limpia la lista guardada.
    function restaurarSecciones() {
        // Pide confirmación antes de restaurar.
        if (!confirm('¿Restaurar todas las secciones eliminadas?')) return;
        // Lee las preferencias actuales.
        const prefs = JSON.parse(localStorage.getItem('saludweb_prefs') || '{}');
        // Vacía la lista de secciones eliminadas.
        prefs.secciones_eliminadas = [];
        // Guarda las preferencias actualizadas en localStorage.
        localStorage.setItem('saludweb_prefs', JSON.stringify(prefs));
        // Busca todas las secciones ocultas y les quita la clase "hidden".
        document.querySelectorAll('.section.hidden').forEach(function(el) {
            el.classList.remove('hidden');
        });
        // Oculta el botón de restaurar porque ya no quedan secciones ocultas.
        document.getElementById('restaurarSecciones').style.display = 'none';
        // Notifica al usuario que las secciones fueron restauradas.
        mostrarToast('Secciones restauradas');
    }

    // ============================================================
    // Guardar preferencia en localStorage
    // ============================================================
    // Guarda una clave/valor (por ejemplo, un toggle activado) en localStorage.
    function guardarPreferencia(clave, valor) {
        // Lee las preferencias actuales.
        const prefs = JSON.parse(localStorage.getItem('saludweb_prefs') || '{}');
        // Asigna el valor a la clave indicada.
        prefs[clave] = valor;
        // Guarda el objeto actualizado serializado como JSON.
        localStorage.setItem('saludweb_prefs', JSON.stringify(prefs));
        // Muestra un mensaje confirmando que la preferencia se guardó.
        mostrarToast('Preferencia guardada');
    }

    // ============================================================
    // Toggle 2FA
    // ============================================================
    // Activa o desactiva la autenticación de dos factores y actualiza la interfaz.
    function toggle2FA() {
        // Lee las preferencias actuales.
        const prefs = JSON.parse(localStorage.getItem('saludweb_prefs') || '{}');
        // Invierte el valor booleano actual de dosfa_activado.
        prefs.dosfa_activado = !prefs.dosfa_activado;
        // Guarda el nuevo estado en localStorage.
        localStorage.setItem('saludweb_prefs', JSON.stringify(prefs));

        // Referencia al texto que muestra el estado del 2FA.
        const estado = document.getElementById('estado2fa');
        // Referencia al botón que permite activar/desactivar el 2FA.
        const btn = document.getElementById('btn2fa');
        // Si el 2FA quedó activado...
        if (prefs.dosfa_activado) {
            // Muestra el estado como "Activado".
            estado.textContent = 'Activado';
            // Colorea el estado en verde (color de éxito).
            estado.style.color = '#10b981';
            // Cambia el texto del botón.
            btn.textContent = '🔐 Desactivar 2FA';
            // Aplica el estilo rojo al botón.
            btn.className = 'btn btn-danger';
            // Notifica la activación.
            mostrarToast('2FA activado correctamente');
        } else {
            // Si quedó desactivado: muestra "Desactivado".
            estado.textContent = 'Desactivado';
            // Quita el color verde (vuelve al color por defecto).
            estado.style.color = '';
            // Cambia el texto del botón.
            btn.textContent = '🔐 Activar 2FA';
            // Devuelve el botón al estilo primario (índigo).
            btn.className = 'btn btn-primary';
            // Notifica la desactivación.
            mostrarToast('2FA desactivado');
        }
    }

    // ============================================================
    // Modal: FAQ
    // ============================================================
    // Abre el modal con el contenido de preguntas frecuentes.
    function abrirFAQ() {
        // Establece el título del modal.
        document.getElementById('modalTitle').textContent = '❓ Preguntas Frecuentes';
        // Inserta el cuerpo del modal con el listado de preguntas y respuestas.
        document.getElementById('modalBody').innerHTML = `
            <p><strong>¿Cómo crear un médico?</strong><br>Andá a Médicos y hacé click en "+ Nuevo Médico".</p>
            <p><strong>¿Cómo eliminar una receta?</strong><br>En Prescripciones, hacé click en "Eliminar" sobre la receta que querés borrar.</p>
            <p><strong>¿Para qué sirve Mis Rx?</strong><br>Es tu panel personal de recetas electrónicas.</p>
            <p><strong>¿Cómo contacto con soporte?</strong><br>Usá los canales de WhatsApp o Email en la sección Contacto.</p>
        `;
        // Agrega la clase "open" para mostrar la superposición modal.
        document.getElementById('modalOverlay').classList.add('open');
    }

    // ============================================================
    // Modal: Documentación
    // ============================================================
    // Abre el modal con información técnica del sistema.
    function abrirDocumentacion() {
        // Establece el título del modal.
        document.getElementById('modalTitle').textContent = '📚 Documentación del Sistema';
        // Inserta el cuerpo del modal con datos de arquitectura y tecnología.
        document.getElementById('modalBody').innerHTML = `
            <p><strong>Arquitectura:</strong> MVC (Modelo-Vista-Controlador) con capas de persistencia y servicio.</p>
            <p><strong>Base de datos:</strong> MySQL con tablas: médicos, pacientes, prescripciones, medicamentos, obras_sociales, triages, auditoria.</p>
            <p><strong>API REST:</strong> Endpoints para CRUD de médicos y prescripciones.</p>
            <p><strong>Frontend:</strong> HTML + CSS + JavaScript vanilla con fetch a la API.</p>
            <p><strong>Ruteo:</strong> Front Controller vía .htaccess + Router PHP.</p>
        `;
        // Agrega la clase "open" para mostrar el modal.
        document.getElementById('modalOverlay').classList.add('open');
    }

    // ============================================================
    // Cerrar modal
    // ============================================================
    // Quita la clase "open" para ocultar la superposición modal.
    function cerrarModal() {
        document.getElementById('modalOverlay').classList.remove('open');
    }

    // ============================================================
    // Eliminar cuenta
    // ============================================================
    // Pide doble confirmación, borra las preferencias locales y cierra la sesión.
    function eliminarCuenta() {
        // Primera confirmación de la acción.
        if (!confirm('¿Estás seguro de que deseas eliminar tu cuenta? Esta acción no se puede deshacer.')) return;
        // Segunda confirmación por seguridad.
        if (!confirm('¿Estás realmente seguro? Se eliminarán todos tus datos.')) return;
        // Elimina las preferencias almacenadas en localStorage.
        localStorage.removeItem('saludweb_prefs');
        // Muestra un toast indicando que se redirigirá al usuario.
        mostrarToast('Cuenta eliminada. Redirigiendo...', true);
        // Espera 1.5 segundos antes de redirigir.
        setTimeout(function() {
            // Redirección a /logout (cerrar sesión); $basePath es reemplazado por PHP.
            window.location.href = '<?php echo $basePath; ?>/logout';
        }, 1500);
    }

    // ============================================================
    // Toast
    // ============================================================
    // Muestra una notificación flotante durante unos segundos.
    function mostrarToast(mensaje, esError) {
        // Referencia al elemento div del toast.
        var toast = document.getElementById('toast');
        // Asigna el mensaje a mostrar.
        toast.textContent = mensaje;
        // Aplica la clase "error" si esError es true (toast rojo).
        toast.className = 'toast' + (esError ? ' error' : '');
        // Hace visible el toast.
        toast.style.display = 'block';
        // Pasados 2.5 segundos vuelve a ocultarlo.
        setTimeout(function() { toast.style.display = 'none'; }, 2500);
    }

    // Cerrar modal al hacer click fuera
    // Si el usuario hace click sobre el fondo oscuro (fuera de la caja), se cierra el modal.
    document.getElementById('modalOverlay').addEventListener('click', function(e) {
        // e.target es el elemento cliqueado y this es el overlay; si coinciden, se cierra el modal.
        if (e.target === this) cerrarModal();
    });

    // Inicializar
    // Al cargar la página se aplican las preferencias guardadas del usuario.
    cargarPreferencias();
</script>
</body>
</html>