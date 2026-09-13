<?php
// ============================================================
// routes.php - Definición de rutas del proyecto SaludWEB
// ============================================================
// Este archivo conecta la URL que el usuario visita con el código
// que debe ejecutarse. Actúa como un "mapa" del sitio.
//
// Reutiliza el arranque (bootstrap) del backend para tener
// disponibles: $router, $pdo, $medicoService, $pacienteService,
// $prescripcionService, $obraSocialRepo y demás dependencias.
// ============================================================

// Carga el archivo de arranque (bootstrap) del backend
require_once __DIR__ . '/repositorio_backend/core/bootstrap.php';

// ============================================================
// Helpers de autenticación de sesión (web)
// ============================================================

// Devuelve el usuario logueado (array) o null si no hay sesión
function usuarioLogueado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

// Redirige a una ruta interna de la aplicación y corta la ejecución
function redirigir(string $ruta): void
{
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    header('Location: ' . $basePath . $ruta);
    exit;
}

// Exige que haya un usuario logueado; si no, manda al login (401 web)
function requiereLogin(): void
{
    if (!usuarioLogueado()) {
        redirigir('/login');
    }
}

// ============================================================
// Ruta raíz: página de bienvenida de la aplicación (protegida)
// ============================================================
$router->get('/', function () use ($pdo) {
    requiereLogin();
    include __DIR__ . '/home.php';
});

// ============================================================
// Autenticación: login (público) y logout
// ============================================================

// GET /login - Muestra el formulario de acceso
$router->get('/login', function () {
    if (usuarioLogueado()) {
        redirigir('/');
    }
    include __DIR__ . '/login.php';
});

// POST /login - Valida credenciales contra la tabla usuarios
$router->post('/login', function () use ($pdo) {
    require_once __DIR__ . '/repositorio_backend/core/Response.php';

    // Si ya está logueado, no hace falta volver a entrar
    if (usuarioLogueado()) {
        redirigir('/');
    }

    // Lee los datos del formulario
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validación básica de campos vacíos
    if ($email === '' || $password === '') {
        $error = 'Ingresá tu email y contraseña';
        include __DIR__ . '/login.php';
        return;
    }

    // Consulta parametrizada contra inyección SQL (solo usuarios activos)
    $stmt = $pdo->prepare('SELECT id, email, password, nombre, tipo_usuario FROM usuarios WHERE LOWER(email) = ? AND activo = 1 LIMIT 1');
    $stmt->execute([strtolower($email)]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mismo mensaje para usuario inexistente y contraseña incorrecta (seguridad)
    if (!$usuario || !password_verify($password, $usuario['password'])) {
        $error = 'Email o contraseña incorrectos';
        $emailValue = $email;
        include __DIR__ . '/login.php';
        return;
    }

    // Guarda el usuario en la sesión (nunca el hash de la contraseña)
    $_SESSION['usuario'] = [
        'id' => (int) $usuario['id'],
        'email' => $usuario['email'],
        'nombre' => $usuario['nombre'],
        'tipo_usuario' => $usuario['tipo_usuario'],
    ];

    // Redirige a la página principal de la aplicación
    redirigir('/');
});

// GET /logout - Cierra la sesión y vuelve al login
$router->get('/logout', function () {
    // Elimina todas las variables de la sesión
    $_SESSION = [];
    // Destruye la sesión del servidor
    session_destroy();
    // Redirige a la pantalla de acceso
    redirigir('/login');
});

// ============================================================
// Rutas web: vistas de la aplicación
// ============================================================

// Listado de médicos
$router->get('/medicos', function () use ($medicoService) {
    requiereLogin();
    $medicos = $medicoService->obtenerTodos();
    include __DIR__ . '/repositorio_web/lista_medicos.php';
});

// Listado de prescripciones (recetas electrónicas)
$router->get('/prescripciones', function () use ($pdo) {
    requiereLogin();
    include __DIR__ . '/repositorio_web/lista_prescripciones.php';
});

// Dashboard del paciente (Mis Rx)
$router->get('/mis-rx', function () {
    requiereLogin();
    include __DIR__ . '/repositorio_web/mis_rx.php';
});

// Configuración / Ajustes del usuario
$router->get('/configuracion', function () {
    requiereLogin();
    include __DIR__ . '/repositorio_web/configuracion.php';
});

// Páginas secundarias con plantilla común (views/base.php)
$router->get('/notificaciones', function () {
    requiereLogin();
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    include __DIR__ . '/repositorio_web/views/base.php';
});

$router->get('/prestaciones', function () {
    requiereLogin();
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    include __DIR__ . '/repositorio_web/views/base.php';
});

$router->get('/cuenta', function () {
    requiereLogin();
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    include __DIR__ . '/repositorio_web/views/base.php';
});

$router->get('/faq', function () {
    requiereLogin();
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    include __DIR__ . '/repositorio_web/views/base.php';
});

$router->get('/buscador-farmacias', function () {
    requiereLogin();
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    include __DIR__ . '/repositorio_web/views/base.php';
});

$router->get('/preguntas-frecuentes', function () {
    requiereLogin();
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    include __DIR__ . '/repositorio_web/views/base.php';
});

// ============================================================
// API REST: consumida por las vistas de la aplicación
// ============================================================

// GET /api/health - Estado de la aplicación
$router->get('/api/health', function () {
    Response::ok([
        'status' => 'ok',
        'message' => 'API SaludWEB funcionando',
        'timestamp' => date('c'),
        'version' => '1.0.0',
    ], 'API SaludWEB');
});

// Médicos: listado y detalle
$router->get('/api/medicos', function () use ($medicoService) {
    $controller = new MedicoController($medicoService);
    $controller->index();
});

$router->get('/api/medicos/{id}', function ($id) use ($medicoService) {
    $controller = new MedicoController($medicoService);
    $controller->show((int) $id);
});

// Médicos: alta, actualización y baja
$router->post('/api/medicos', function () use ($medicoService) {
    $controller = new MedicoController($medicoService);
    $controller->store();
});

$router->put('/api/medicos/{id}', function ($id) use ($medicoService) {
    $controller = new MedicoController($medicoService);
    $controller->update((int) $id);
});

$router->patch('/api/medicos/{id}', function ($id) use ($medicoService) {
    $controller = new MedicoController($medicoService);
    $controller->update((int) $id);
});

$router->delete('/api/medicos/{id}', function ($id) use ($medicoService) {
    $controller = new MedicoController($medicoService);
    $controller->destroy((int) $id);
});

// Prescripciones: listado, detalle, alta, actualización y cambio de estado
$router->get('/api/prescripciones', function () use ($prescripcionService) {
    $controller = new PrescripcionController($prescripcionService);
    $controller->index();
});

$router->get('/api/prescripciones/{id}', function ($id) use ($prescripcionService) {
    $controller = new PrescripcionController($prescripcionService);
    $controller->show((int) $id);
});

$router->post('/api/prescripciones', function () use ($prescripcionService) {
    $controller = new PrescripcionController($prescripcionService);
    $controller->store();
});

$router->put('/api/prescripciones/{id}', function ($id) use ($prescripcionService) {
    $controller = new PrescripcionController($prescripcionService);
    $controller->update((int) $id);
});

$router->patch('/api/prescripciones/{id}', function ($id) use ($prescripcionService) {
    $controller = new PrescripcionController($prescripcionService);
    $controller->update((int) $id);
});

$router->patch('/api/prescripciones/{id}/estado', function ($id) use ($prescripcionService) {
    $controller = new PrescripcionController($prescripcionService);
    $controller->cambiarEstado((int) $id);
});

$router->delete('/api/prescripciones/{id}', function ($id) use ($pdo) {
    try {
        $stmt = $pdo->prepare('DELETE FROM prescripciones WHERE id = ?');
        $stmt->execute([(int) $id]);
        if ($stmt->rowCount() > 0) {
            Response::ok(null, 'Receta eliminada correctamente');
        } else {
            Response::error('Receta no encontrada', 404);
        }
    } catch (Exception $e) {
        Response::error('Error al eliminar la receta', 500);
    }
});

// Obras sociales: catálogo público (para formularios)
$router->get('/api/obras-sociales', function () use ($obraSocialRepo) {
    try {
        Response::ok($obraSocialRepo->obtenerTodas());
    } catch (Exception $e) {
        Response::error('Error interno del servidor', 500);
    }
});

// ============================================================
// Manejador de rutas no encontradas (404)
// ============================================================
$router->notFound(function () {
    // Si hay sesión, se manda al inicio; si no, al login
    if (usuarioLogueado()) {
        redirigir('/');
    }
    redirigir('/login');
});