<?php
// ============================================================
// home.php - Página principal de la aplicación SaludWEB
// ============================================================
// Muestra la bienvenida y las estadísticas en vivo del sistema.
// $pdo viene inyectado desde routes.php (use ($pdo)).
// $basePath se calcula a partir de la URL actual.
// ============================================================

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$usuario = usuarioLogueado();

// ============================================================
// Estadísticas en vivo de la base de datos
// Cada consulta está protegida con try/catch para que la página
// nunca muestre errores aunque algún dato no esté disponible.
// ============================================================
$conteo = ['medicos' => 0, 'medicos_activos' => 0, 'pacientes' => 0, 'recetas' => 0];

try { $conteo['medicos'] = (int) $pdo->query('SELECT COUNT(*) FROM medicos')->fetchColumn(); } catch (Throwable $e) {}
try { $conteo['medicos_activos'] = (int) $pdo->query('SELECT COUNT(*) FROM medicos WHERE activo = 1')->fetchColumn(); } catch (Throwable $e) {}
try { $conteo['pacientes'] = (int) $pdo->query('SELECT COUNT(*) FROM pacientes')->fetchColumn(); } catch (Throwable $e) {}
try { $conteo['recetas'] = (int) $pdo->query("SELECT COUNT(*) FROM prescripciones WHERE estado = 'activa'")->fetchColumn(); } catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaludWEB - Prescripciones Electrónicas</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #eef2ff;
            --bg: #f8fafc;
            --text: #1e293b;
            --muted: #64748b;
            --success: #10b981;
            --white: #ffffff;
            --border: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ============================================================
           ENCABEZADO
           ============================================================ */
        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--text); }
        .logo-box {
            background: var(--primary);
            color: white;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        }
        .logo-text { font-size: 22px; font-weight: 800; }

        .nav { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .nav a {
            text-decoration: none;
            color: var(--muted);
            font-weight: 700;
            font-size: 14px;
            padding: 8px 14px;
            border-radius: 10px;
            transition: background .15s ease, color .15s ease;
        }
        .nav a:hover { background: var(--primary-light); color: var(--primary); }
        .user-chip {
            background: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 13px;
            padding: 8px 14px;
            border-radius: 10px;
            white-space: nowrap;
        }

        /* ============================================================
           BIENVENIDA
           ============================================================ */
        .hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 55%, #9333ea 100%);
            color: white;
            text-align: center;
            padding: 72px 24px;
        }

        .hero h1 {
            font-size: clamp(32px, 5vw, 56px);
            font-weight: 800;
            letter-spacing: 1px;
            line-height: 1.1;
        }

        .hero .brand { color: #ffe07a; }

        .hero .pro {
            background: #ffe07a;
            color: var(--primary-dark);
            font-size: 0.45em;
            font-weight: 800;
            padding: 4px 14px;
            border-radius: 10px;
            vertical-align: middle;
            display: inline-block;
            transform: translateY(-4px);
        }

        .hero p {
            margin-top: 16px;
            font-size: clamp(15px, 2vw, 19px);
            opacity: .92;
            max-width: 620px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .hero-actions {
            margin-top: 28px;
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            font-weight: 800;
            font-size: 15px;
            padding: 14px 28px;
            border-radius: 12px;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-white { background: white; color: var(--primary); box-shadow: 0 8px 20px rgba(0,0,0,.15); }
        .btn-outline { background: rgba(255,255,255,.12); color: white; border: 2px solid rgba(255,255,255,.6); }

        /* ============================================================
           ESTADÍSTICAS EN VIVO
           ============================================================ */
        .wrap { max-width: 1200px; margin: 0 auto; padding: 48px 24px; }

        .section-title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 20px;
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 10px 20px;
            border-radius: 12px;
            border-left: 6px solid var(--primary);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 44px;
        }

        .stat {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 4px 12px rgba(0,0,0,.05);
            border-top: 5px solid var(--border);
        }

        .stat .num { font-size: 36px; font-weight: 800; color: var(--text); }
        .stat .label { color: var(--muted); font-weight: 600; margin-top: 4px; font-size: 15px; }

        /* Colores por tarjeta de estadística */
        .stat.indigo { background: #eef2ff; border-top-color: #4f46e5; }
        .stat.indigo .num, .stat.indigo .ok { color: #3730a3; }
        .stat.green { background: #ecfdf5; border-top-color: #10b981; }
        .stat.green .num, .stat.green .ok { color: #047857; }
        .stat.amber { background: #fffbeb; border-top-color: #f59e0b; }
        .stat.amber .num, .stat.amber .ok { color: #b45309; }
        .stat.rose { background: #fdf2f8; border-top-color: #ec4899; }
        .stat.rose .num, .stat.rose .ok { color: #be185d; }

        .stat .ok { font-size: 13px; font-weight: 700; }

        /* ============================================================
           MÓDULOS DE LA APLICACIÓN
           ============================================================ */
        .modules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .module {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 26px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }
        .module:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.14); }

        /* Colores por módulo */
        .module.indigo { background: #eef2ff; border-color: #c7d2fe; }
        .module.indigo .icon { background: #4f46e5; }
        .module.indigo:hover { border-color: #4f46e5; box-shadow: 0 12px 28px rgba(79,70,229,.18); }
        .module.violet { background: #f5f3ff; border-color: #ddd6fe; }
        .module.violet .icon { background: #7c3aed; }
        .module.violet:hover { border-color: #7c3aed; box-shadow: 0 12px 28px rgba(124,58,237,.18); }
        .module.teal { background: #f0fdfa; border-color: #99f6e4; }
        .module.teal .icon { background: #0d9488; }
        .module.teal:hover { border-color: #0d9488; box-shadow: 0 12px 28px rgba(13,148,136,.18); }
        .module.orange { background: #fff7ed; border-color: #fed7aa; }
        .module.orange .icon { background: #ea580c; }
        .module.orange:hover { border-color: #ea580c; box-shadow: 0 12px 28px rgba(234,88,12,.18); }

        .module .icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: white;
            box-shadow: 0 6px 14px rgba(0,0,0,.12);
        }
        .module h3 { font-size: 18px; font-weight: 800; }
        .module p { color: var(--muted); font-size: 14px; line-height: 1.5; flex-grow: 1; }

        footer {
            text-align: center;
            padding: 28px 24px;
            color: var(--muted);
            border-top: 1px solid var(--border);
            font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- ============================================================
    ENCABEZADO
    ============================================================ -->
    <header class="topbar">
        <div class="topbar-inner">
            <a class="logo" href="<?php echo $basePath; ?>/">
                <div class="logo-box">⚕️</div>
                <div class="logo-text">SaludWEB</div>
            </a>
            <nav class="nav">
                <span class="user-chip">👤 <?php echo htmlspecialchars($usuario['nombre'] ?? 'Usuario'); ?></span>
                <a href="<?php echo $basePath; ?>/">Inicio</a>
                <a href="<?php echo $basePath; ?>/medicos">Médicos</a>
                <a href="<?php echo $basePath; ?>/prescripciones">Prescripciones</a>
                <a href="<?php echo $basePath; ?>/mis-rx">Mis Rx</a>
                <a href="<?php echo $basePath; ?>/configuracion">Ajustes</a>
                <a href="<?php echo $basePath; ?>/logout" style="color:#ef4444;">Salir</a>
            </nav>
        </div>
    </header>

    <!-- ============================================================
    BIENVENIDA
    ============================================================ -->
    <section class="hero">
        <h1>BIENVENIDOS A <span class="brand">SaludWEB</span> <span class="pro">PRO</span></h1>
        <p>La plataforma digital de prescripciones electrónicas para profesionales de la salud. Gestioná médicos, recetas y pacientes desde un solo lugar, de forma rápida y segura.</p>
        <div class="hero-actions">
            <a class="btn btn-white" href="<?php echo $basePath; ?>/medicos">Ingresar al Panel</a>
            <a class="btn btn-outline" href="<?php echo $basePath; ?>/prescripciones">Ver Recetas</a>
        </div>
    </section>

    <!-- ============================================================
    ESTADÍSTICAS EN VIVO
    ============================================================ -->
    <div class="wrap">
        <h2 class="section-title">📊 Actividad del Sistema</h2>
        <div class="stats">
            <div class="stat indigo">
                <div class="num"><?php echo $conteo['medicos']; ?></div>
                <div class="label">Médicos registrados</div>
                <div class="ok">acceso habilitado</div>
            </div>
            <div class="stat green">
                <div class="num"><?php echo $conteo['medicos_activos']; ?></div>
                <div class="label">Médicos operativos</div>
                <div class="ok">habilitados para emitir</div>
            </div>
            <div class="stat amber">
                <div class="num"><?php echo $conteo['pacientes']; ?></div>
                <div class="label">Pacientes</div>
                <div class="ok">cargados en el sistema</div>
            </div>
            <div class="stat rose">
                <div class="num"><?php echo $conteo['recetas']; ?></div>
                <div class="label">Recetas activas</div>
                <div class="ok">vigentes y dispensables</div>
            </div>
        </div>

        <!-- ============================================================
        MÓDULOS DE LA APLICACIÓN
        ============================================================ -->
        <h2 class="section-title">🧭 Módulos de la Aplicación</h2>
        <div class="modules">
            <a class="module indigo" href="<?php echo $basePath; ?>/medicos">
                <div class="icon">👨‍⚕️</div>
                <h3>Médicos</h3>
                <p>Administrá los profesionales habilitados para emitir recetas electrónicas.</p>
            </a>
            <a class="module violet" href="<?php echo $basePath; ?>/prescripciones">
                <div class="icon">📄</div>
                <h3>Prescripciones</h3>
                <p>Consultá, filtrá y gestioná todas las recetas electrónicas del sistema.</p>
            </a>
            <a class="module teal" href="<?php echo $basePath; ?>/mis-rx">
                <div class="icon">📋</div>
                <h3>Mis Rx</h3>
                <p>Tu centro de control personal de recetas y acceso rápido a los servicios.</p>
            </a>
            <a class="module orange" href="<?php echo $basePath; ?>/configuracion">
                <div class="icon">⚙️</div>
                <h3>Ajustes</h3>
                <p>Configurá notificaciones, seguridad de la cuenta y preferencias del sistema.</p>
            </a>
        </div>
    </div>

    <footer>SaludWEB · Sistema de Prescripciones Electrónicas</footer>

</body>
</html>