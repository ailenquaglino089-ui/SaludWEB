<?php
// ============================================================
// login.php - Pantalla de acceso a la aplicación SaludWEB
// ============================================================
// Recibe desde routes.php:
//   - $error:      (opcional) mensaje a mostrar si el login falló
//   - $emailValue: (opcional) email que el usuario ya había escrito
// Si no hay error ni email, se muestra el formulario limpio.
// ============================================================

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$error = $error ?? null;
$emailValue = $emailValue ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - SaludWEB</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #eef2ff;
            --text: #1e293b;
            --muted: #64748b;
            --danger: #dc2626;
            --border: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 55%, #9333ea 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 60px rgba(0,0,0,.25);
        }

        .logo { display: flex; align-items: center; gap: 12px; justify-content: center; margin-bottom: 8px; }
        .logo-box {
            background: var(--primary);
            color: white;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: bold;
        }
        .logo-text { font-size: 24px; font-weight: 800; color: var(--text); }

        h1 { text-align: center; font-size: 22px; font-weight: 800; margin-bottom: 6px; }
        .sub { text-align: center; color: var(--muted); font-size: 14px; margin-bottom: 26px; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 700; font-size: 14px; margin-bottom: 6px; color: #334155; }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: border-color .15s ease;
        }
        .form-group input:focus { border-color: var(--primary); }

        .btn {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }
        .btn:hover { background: var(--primary-dark); transform: translateY(-1px); }

        .alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--danger);
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .demo {
            margin-top: 22px;
            padding: 14px;
            background: var(--primary-light);
            border-radius: 10px;
            font-size: 13px;
            color: var(--primary-dark);
            text-align: center;
            line-height: 1.6;
        }
        .demo code { background: white; padding: 2px 8px; border-radius: 6px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <div class="logo-box">⚕️</div>
            <div class="logo-text">SaludWEB</div>
        </div>
        <h1>Iniciar sesión</h1>
        <p class="sub">Accedé con tu cuenta para gestionar prescripciones electrónicas</p>

        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo $basePath; ?>/login">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($emailValue); ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Ingresar</button>
        </form>

        <div class="demo">
            <strong>Demo:</strong> <code>admin@salud.com</code> — <code>admin123</code>
        </div>
    </div>
</body>
</html>