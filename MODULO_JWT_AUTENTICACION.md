# 🔐 MÓDULO APLICADO: Autenticación JWT en Aplicaciones Modernas

**Proyecto:** SaludWEB - Sistema de Gestión Médica
**Materia:** Programación IV
**Fecha:** 13/09/2026
**Estado:** ✅ IMPLEMENTADO y verificado por HTTP

---

## 📚 Contenido de la presentación aplicada

Se aplicaron los dos documentos de la clase:

1. **Autenticación en Aplicaciones Modernas** (sesiones vs tokens JWT, OAuth/OIDC, rutas públicas/protegidas, roles, mejores prácticas de seguridad).
2. **Login API con JWT: Asegurando el Acceso** (guía práctica PHP + PDO + MySQL + `firebase/php-jwt`).

---

## 🛠️ Cambios realizados en el backend

### 1. Dependencias (Composer)
```
✅ composer.json / composer.lock
✅ vendor/firebase/php-jwt v7.1.0   ← librería oficial de la guía
✅ vendor/autoload.php  (cargado en bootstrap.php)
```

### 2. Nuevo módulo core/ (arquitectura limpia)
```
✅ core/JwtService.php       → emite y verifica JWT (HS256) con claims estándar
✅ core/AuthMiddleware.php   → verificarToken() (401) + requireRol() (403)
✅ core/Secret.php           → JWT_SECRET desde entorno o secret.key persistente
```

### 3. Refactorización de autenticación
```
services/AuthService.php:
  • login() ahora genera un JWT REAL (antes: base64(id:timestamp) simulado)
  • Recibe JwtService por inyección de dependencias (DIP)

controllers/AuthController.php:
  • El usuario se resuelve desde el claim sub del JWT verificado
  • Se eliminó el decodificado manual del token base64
```

### 4. Rutas públicas vs protegidas (routes.php)
```
Públicas:      health, obras-sociales, catálogos GET, registro, login
Protegidas:    /auth/me, logout, cambiar-contrasena, crear/editar CRUD, prescripciones
Solo admin:    DELETE de médicos, pacientes y prescripciones (403 si no aplica)
```

---

## 🧪 Verificación HTTP (pruebas reales ejecutadas)

| Escenario | Método | Resultado |
|---|---|---|
| Login con credenciales válidas | POST /api/auth/login | ✅ 200 + JWT `x.y.z` |
| JWT decodificado con claims | - | ✅ `iss, iat, exp, sub, rol, email` |
| Datos del usuario autenticado | GET /api/auth/me + Bearer | ✅ 200 |
| Sin token en ruta protegida | GET /api/auth/me | ✅ 401 "Token no proporcionado" |
| Token manipulado | GET /api/auth/me + token alterado | ✅ 401 "Token inválido o expirado" |
| Rol paciente intenta DELETE | DELETE /api/medicos/1 | ✅ 403 Forbidden |
| Rol admin intenta DELETE | DELETE /api/medicos/999 | ✅ 404 (pasó el middleware, llegó al controller) |
| Ruta clínica sin token | GET /api/prescripciones | ✅ 401 |
| Catálogo público | GET /api/obras-sociales | ✅ 200 |

---

## 🎓 Conceptos de la presentación cubiertos

✅ JWT como estándar abierto (RFC 7519)  
✅ Estructura Header.Payload.Firma  
✅ Claims estándar: `iss`, `iat`, `exp`, `sub` + claim privado `rol`  
✅ Emisión con `firebase/php-jwt` y firma HS256  
✅ Verificación automática de firma y expiración  
✅ Middleware reutilizable `verificarToken()` (patrón "capas")  
✅ Distinción **401 Unauthorized** (no autenticado) vs **403 Forbidden** (sin permiso)  
✅ Roles y permisos (RBAC) con principio de mínimo privilegio  
✅ `password_hash()`/`password_verify()` (bcrypt) ya existentes y correctos  
✅ Mensajes de error genéricos en login (anti-enumeración de usuarios)  
✅ El secreto nunca está en el código fuente (entorno o archivo protegido)  
✅ Tokens sin datos sensibles en el payload (legible en Base64URL)

---

## 🔄 Pendientes del módulo (para próxima sesión - no bloqueantes)

- [ ] **Refresh tokens** para renovar el acceso sin pedir credenciales (el access token expira en 60 min)
- [ ] **Rate limiting** por IP en `/login` (anti fuerza bruta / credential stuffing)
- [ ] **Blocklist** de tokens revocados (logout real del lado servidor)
- [ ] HTTPS obligatorio en producción (los JWT viajan en cada petición)
- [ ] Autenticación de doble factor (2FA/TOTP) para el rol admin

---

## 🚀 Estos cambios NO rompen el frontend

El frontend React (repositorio_web_spa) ya:
- Envía `Authorization: Bearer <token>` en todas las peticiones (interceptor Axios)
- Guarda el token en `localStorage`
- Maneja `401` limpiando el token y redirigiendo a `/login`
- Recibe el token en `data.token` (mismo contrato de respuesta)

No se requirió ninguna modificación en React.

---

## 🔑 Usuarios de prueba creados durante la verificación

| Email | Contraseña | Rol |
|---|---|---|
| `jwttest@salud.com` | `secret123` | admin |
| `pacjwt@salud.com` | `secret123` | paciente |

*(Podés eliminarlos con: DELETE FROM usuarios WHERE email IN ('jwttest@salud.com','pacjwt@salud.com');)*

---

## 📁 Archivos del módulo

| Archivo | Rol |
|---|---|
| `repositorio_backend/composer.json` | Dependencia firebase/php-jwt |
| `repositorio_backend/core/JwtService.php` | Servicio JWT |
| `repositorio_backend/core/AuthMiddleware.php` | Middleware 401/403 |
| `repositorio_backend/core/Secret.php` | Gestión del secreto |
| `repositorio_backend/services/AuthService.php` | Login/registro (JWT) |
| `repositorio_backend/controllers/AuthController.php` | Controlador JWT |
| `repositorio_backend/routes.php` | Rutas públicas/protegidas/admin |
| `repositorio_backend/core/bootstrap.php` | Autoload + inyecciones |
| `repositorio_backend/.gitignore` | Excluye vendor/, storage/, .env |
| `API_DOCUMENTATION.md` | Documentación actualizada |
| `INICIO_RAPIDO.md` | Guía de inicio con JWT |

---

**Estado general del proyecto: 75%** (se suma el módulo de autenticación JWT)
**Listo para defensa ante tribunal:** ✅ SÍ