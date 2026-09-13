<?php
// Inicio del bloque PHP: el servidor ejecuta este fragmento antes de generar el HTML.
// Copia del panel visual de recetas del paciente.
// Calcula la ruta base del proyecto para los enlaces
// rtrim() quita la barra "/" final que deja dirname() al obtener la carpeta actual.
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');  // Ruta raíz del proyecto usada en los enlaces (ej: "/Organizacion_Modulos")
// Fin del bloque PHP: el resto del archivo es HTML puro.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Metadatos: codificación UTF-8 y viewport para diseño responsive -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Título que se muestra en la pestaña del navegador -->
    <title>Mis Rx | SaludWEB</title>
    <!-- Bloque de estilos CSS del dashboard del paciente -->
    <style>
        /* ================================================================
           ESTILOS CSS - Dashboard del Paciente (Mis Rx)
           ================================================================ */

        /* variables CSS personalizadas (paleta de colores) */
        :root {
            --primary: #4f46e5;   /* Color primario: Índigo */
            --bg: #f8fafc;        /* Color de fondo */
            --text: #1e293b;      /* Color del texto principal */
            --danger: #e11d48;    /* Color para alertas/acciones peligrosas */
            --success: #10b981;   /* Color para estados activos y correctos */
        }

        /* Estilos generales */
        /* body: define fuente, fondo, color de texto y espaciado de toda la página */
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); padding: 20px; margin: 0; }
        /* .dashboard: contenedor central con ancho máximo de 1200px */
        .dashboard { max-width: 1200px; margin: 0 auto; }

        /* Encabezado superior con logo y acciones */
        /* .header-top: fila que separa el logo (izquierda) de los botones (derecha) */
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 15px; flex-wrap: wrap; }
        /* .logo-container: agrupa horizontalmente la caja del ícono y el texto "SaludWEB" */
        .logo-container { display: flex; align-items: center; gap: 10px; }
        /* .logo-box: caja cuadrada con el ícono 💊 y el color de fondo principal */
        .logo-box { background: var(--primary); color: white; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 24px; font-weight: bold; }
        /* .logo-text: título principal del encabezado */
        .logo-text { font-size: 28px; font-weight: 800; }
        /* .logo-pro: etiqueta "PRO" en rojo que acompaña al nombre de la aplicación */
        .logo-pro { background: #fee2e2; color: var(--danger); padding: 2px 8px; border-radius: 6px; font-size: 14px; margin-left: 5px; }

        /* Botones del encabezado (Volver, Ajustes, Salir) */
        .header-actions { display: flex; gap: 12px; flex-wrap: wrap; }
        /* Clase compartida por los tres enlaces: botón con borde y transición suave */
        .back-link, .config-link, .logout-link { text-decoration: none; font-weight: 800; font-size: 14px; padding: 10px 20px; border-radius: 12px; background: white; border: 2px solid var(--primary); transition: 0.3s; }
        /* "Volver" y "Ajustes" usan el color principal */
        .back-link, .config-link { color: var(--primary); }
        /* El botón "Salir" se diferencia con borde y texto rojos */
        .logout-link { border-color: #ef4444; color: #ef4444; }

        /* Menú de navegación en cuadrícula */
        /* .grid-menu: cuadrícula adaptable con las tarjetas de navegación */
        .grid-menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 30px; }
        /* .menu-card: tarjeta clicable que representa cada acceso del menú */
        .menu-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); border: 2px solid #e2e8f0; text-decoration: none; color: var(--text); text-align: center; }

        /* Sección de contacto y soporte */
        .support-section { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); margin-bottom: 24px; }
        .contact-icons { display: flex; gap: 12px; }
        /* .social-link: círculo para cada ícono de contacto */
        .social-link { display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; border-radius: 50%; text-decoration: none; font-size: 24px; border: 2px solid #e2e8f0; }
        /* .whatsapp-link: fondo verde oficial de WhatsApp */
        .whatsapp-link { background: #25d366; color: white; }
        /* .gmail-link: fondo rojo oficial de Gmail/Email */
        .gmail-link { background: #ea4335; color: white; }

        /* Panel de estado del sistema */
        /* .status-panel: caja gris que lista los servicios y su estado */
        .status-panel { background: #f8fafc; border-radius: 12px; padding: 20px; border: 2px solid #e2e8f0; margin-top: 20px; }
        /* .status-item: fila que separa el nombre del servicio de su estado */
        .status-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0; }
        /* .status-dot: punto de color que indica el estado del servicio */
        .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .dot-ok { background: var(--success); }  /* Verde = funcionando */
        .dot-warn { background: #f59e0b; }       /* Amarillo = advertencia */

        /* Botón primario */
        .btn-primary { background: var(--primary); color: white; padding: 12px 24px; border: none; border-radius: 10px; cursor: pointer; font-weight: 800; text-decoration: none; display: inline-block; }

        /* Modal FAQ */
        /* .modal-overlay: fondo oscuro fijo que cubre la pantalla; oculto por defecto */
        .modal-overlay { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.4); align-items: center; justify-content: center; }
        /* Con la clase "open" el modal se muestra centrado */
        .modal-overlay.open { display: flex; }
        /* .modal-box: caja blanca del diálogo con scroll si el contenido es alto */
        .modal-box { background: white; border-radius: 16px; padding: 32px; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.15); max-height: 80vh; overflow-y: auto; }
        .modal-box h2 { margin-top: 0; }
        /* .faq-item: tarjeta de cada pregunta/respuesta dentro del FAQ */
        .faq-item { text-align: left; margin-bottom: 16px; padding: 12px; background: #f8fafc; border-radius: 10px; border: 2px solid #e2e8f0; }
        .faq-item h3 { margin: 0 0 4px; font-size: 15px; color: #1e293b; }
        .faq-item p { margin: 0; font-size: 14px; color: #475569; }

        /* Responsive */
        /* En pantallas de hasta 768px el encabezado se apila y las tarjetas pasan a 2 columnas */
        @media (max-width: 768px) {
            .header-top { flex-direction: column; align-items: flex-start; }
            .grid-menu { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <!-- Contenedor principal del dashboard -->
    <div class="dashboard">

        <!-- ============================================================
        ENCABEZADO
        ============================================================ -->
        <div class="header-top">
            <div class="logo-container">
                <!-- Caja con el ícono de medicamento (💊) -->
                <div class="logo-box">💊</div>
                <!-- Nombre de la aplicación con etiqueta "PRO" -->
                <div class="logo-text">SaludWEB <span class="logo-pro">PRO</span></div>
            </div>
            <!-- Botones de navegación superior con basePath dinámico -->
            <div class="header-actions">
                <a href="<?php echo $basePath; ?>/notificaciones" class="back-link">← HOME</a>
                <a href="<?php echo $basePath; ?>/configuracion" class="config-link">⚙️ Ajustes</a>
                <a href="<?php echo $basePath; ?>/logout" class="logout-link">🔒 Salir</a>
            </div>
        </div>

        <!-- Subtítulo -->
        <p class="subtitle">Bienvenido a tu centro de control de recetas electrónicas</p>

        <!-- ============================================================
        MENÚ DE NAVEGACIÓN EN CUADRÍCULA
        ============================================================ -->
        <!-- Cuadrícula de tarjetas de navegación principal -->
        <div class="grid-menu">
            <!-- Tarjeta: acceso al listado de recetas electrónicas -->
            <a href="<?php echo $basePath; ?>/prescripciones" class="menu-card">
                <div style="font-size: 36px;">📄</div>
                <strong>Mis Recetas</strong>
                <small style="color: #64748b;">Ver todas</small>
            </a>

            <!-- Tarjeta: acceso al centro de notificaciones y alertas -->
            <a href="<?php echo $basePath; ?>/notificaciones" class="menu-card">
                <div style="font-size: 36px;">🔔</div>
                <strong>Notificaciones</strong>
                <small style="color: #64748b;">Alertas y avisos</small>
            </a>

            <!-- Tarjeta: acceso al listado de prestaciones médicas -->
            <a href="<?php echo $basePath; ?>/prestaciones" class="menu-card">
                <div style="font-size: 36px;">🏥</div>
                <strong>Prestaciones</strong>
                <small style="color: #64748b;">Servicios</small>
            </a>

            <!-- Tarjeta: buscador de farmacias cercanas -->
            <a href="<?php echo $basePath; ?>/buscador-farmacias" class="menu-card">
                <div style="font-size: 36px;">💊</div>
                <strong>Farmacias</strong>
                <small style="color: #64748b;">Buscar cerca</small>
            </a>
        </div>

        <!-- ============================================================
        SOPORTE Y CONTACTO
        ============================================================ -->
        <div class="support-section">
            <h2>📞 Soporte</h2>
            <p>¿Necesitás ayuda? Contactanos por estos canales:</p>
            <!-- Íconos de contacto: WhatsApp y Email -->
            <div class="contact-icons">
                <a href="https://wa.me/541234567890" target="_blank" class="social-link whatsapp-link" title="WhatsApp">💬</a>
                <a href="mailto:soporte@saludweb.com" class="social-link gmail-link" title="Email">✉️</a>
            </div>
            <!-- Datos de contacto en texto plano -->
            <p style="margin-top: 16px; color: #64748b;">
                WhatsApp: +54 9 11 1234-5678 | Email: soporte@saludweb.com
            </p>
            <!-- Botón que abre el modal de preguntas frecuentes -->
            <button onclick="abrirFAQ()" class="btn-primary">❓ Preguntas Frecuentes</button>
        </div>

        <!-- Modal FAQ -->
        <!-- Superposición modal con las preguntas frecuentes del sistema -->
        <div class="modal-overlay" id="modalFAQ">
            <div class="modal-box">
                <h2>❓ Preguntas Frecuentes</h2>
                <!-- Pregunta 1: cómo crear un médico -->
                <div class="faq-item">
                    <h3>¿Cómo creo un nuevo médico?</h3>
                    <p>Andá a la sección Médicos y hacé click en "+ Nuevo Médico". Completá los datos y guardá.</p>
                </div>
                <!-- Pregunta 2: cómo eliminar una receta -->
                <div class="faq-item">
                    <h3>¿Cómo elimino una receta?</h3>
                    <p>En Prescripciones, hacé click en "Eliminar" sobre la receta que querés borrar.</p>
                </div>
                <!-- Pregunta 3: qué es Mis Rx -->
                <div class="faq-item">
                    <h3>¿Qué es Mis Rx?</h3>
                    <p>Es tu panel personal donde podés ver todas tus recetas electrónicas y acceder a las funciones principales del sistema.</p>
                </div>
                <!-- Pregunta 4: cómo contactar con soporte -->
                <div class="faq-item">
                    <h3>¿Cómo contacto con soporte?</h3>
                    <p>Usá los canales de WhatsApp o Email que aparecen en esta misma sección.</p>
                </div>
                <!-- Botón para cerrar el modal -->
                <button class="btn" style="background:#f1f5f9;color:#1e293b;border:2px solid #cbd5e1;margin-top:8px;" onclick="cerrarFAQ()">Cerrar</button>
            </div>
        </div>

        <!-- ============================================================
        ESTADO DEL SISTEMA
        ============================================================ -->
        <div class="support-section">
            <h2>🔧 Estado del Sistema</h2>
            <!-- Panel que enumera los servicios y su estado operativo -->
            <div class="status-panel">
                <div class="status-item">
                    <span>🔌 Servidor de base de datos</span>
                    <span style="color: var(--success);"><span class="status-dot dot-ok"></span>Operacional</span>
                </div>
                <div class="status-item">
                    <span>📡 API REST</span>
                    <span style="color: var(--success);"><span class="status-dot dot-ok"></span>Operacional</span>
                </div>
                <!-- Este servicio está en mantenimiento: punto amarillo -->
                <div class="status-item">
                    <span>☁️ Servicio de recetas electrónicas</span>
                    <span style="color: #f59e0b;"><span class="status-dot dot-warn"></span>Mantenimiento</span>
                </div>
                <div class="status-item">
                    <span>📧 Servicio de notificaciones</span>
                    <span style="color: var(--success);"><span class="status-dot dot-ok"></span>Operacional</span>
                </div>
            </div>
        </div>

    </div>

    <script>
        // abrirFAQ(): muestra el modal agregándole la clase "open".
        function abrirFAQ() { document.getElementById('modalFAQ').classList.add('open'); }
        // cerrarFAQ(): oculta el modal quitándole la clase "open".
        function cerrarFAQ() { document.getElementById('modalFAQ').classList.remove('open'); }
        // Si el usuario hace click fuera del contenido del modal, se cierra el FAQ.
        document.getElementById('modalFAQ').addEventListener('click', function(e) { if (e.target === this) cerrarFAQ(); });
    </script>
</body>
</html>