# 🔐 MÓDULO APLICADO: Seguridad Básica para APIs + Cliente Web

**Proyecto:** SaludWEB - Sistema de Gestión Médica
**Materia:** Programación IV
**Fecha:** 13/09/2026
**Estado:** ✅ IMPLEMENTADO y verificado por HTTP

---

## 📚 Contenido de la presentación aplicada

Se aplicaron los tres documentos finales de la clase:

1. **Seguridad Básica para APIs** (CORS, headers de seguridad, validación y saneamiento de entradas, rate limiting anti fuerza bruta, manejo seguro de errores, variables de entorno).
2. **El navegador como cliente de una API** (por qué no usar fetch directo a un repo, características de la web como plataforma, SPA y las guías de consumo).
3. **Login desde Frontend Desacoplado** (frontend SPA vs backend API: el frontend ya consume la API con JWT Bearer, `AuthContext`, `ProtectedRoute` y verificación de sesión en `/api/auth/me`).

---

## 🛠️ Cambios realizados en el backend

### 1. Configuración por entorno (nunca secretos en el código)
```
✅ core/Config.php        → appEnv() (development|production), get(), validar()
✅ core/Secret.php        → en producción exige JWT_SECRET del entorno (≥48 chars)
✅ .env.example           → APP_ENV, DB_*, JWT_SECRET, CORS_ORIGINS, JWT_TTL
✅ bootstrap.php / db.php → display_errors SIEMPRE apagado en producción
```

### 2. Headers de seguridad + CORS (routes.php)
```
✅ CORS whitelist por CORS_ORIGINS (nunca '*', nunca con credenciales):
   http://localhost:5173, http://127.0.0.1:5173, http://localhost, http://127.0.0.1
✅ Preflight OPTIONS respondido con 204
✅ Vary: Origin
✅ X-Content-Type-Options: nosniff
✅ X-Frame-Options: DENY
✅ Referrer-Policy: no-referrer
✅ X-XSS-Protection: 1; mode=block
```

### 3. Rate limiting anti fuerza bruta (core/RateLimiter.php)
```
✅ 5 intentos fallidos / 900 segundos por IP en POST /api/auth/login
✅ Clave "login:<IP>", persistencia JSON en storage/rate/ (fuera del webroot)
✅ 429 Too Many Requests al superar el límite ("Demasiados intentos fallidos...")
✅ limpiar() al login exitoso
```

### 4. Saneamiento y validación de entradas (services/)
```
✅ AuthService:      email normalizado (trim+strtolower) y ≤255, formato email
                     nombre sin tags HTML (strip_tags) y ≤100
                     contraseña entre 6 y 72 caracteres (límite bcrypt)
✅ MedicoService:    strip_tags + límites (nombre ≤150, matrícula ≤50, especialidad ≤100)
✅ PacienteService:  strip_tags + límites (nombre ≤150, DNI ≤50)
✅ PrescripcionService: máx. 50 medicamentos, indicaciones ≤1000 chars, estados whitelist
```

### 5. Manejo seguro de errores (core/Response.php)
```
✅ status ≥ 500 → mensaje genérico "Error interno del servidor" (nunca el detalle)
✅ requestId autogenerado (hex aleatorio) para correlacionar en soporte
✅ code por defecto ERR_INTERNAL
```

### 6. Frontend desacoplado (repositorio_web_spa)
```
✅ src/api/client.js:  VITE_API_URL configurable + timeout de 15s en Axios
                       (las credenciales JWT viajan en el header Bearer)
✅ Navbar.jsx:         badge con el rol del usuario (admin/paciente)
✅ Listas:             RBAC en UI – botón "Eliminar" solo visible para admin
                       (coherente con el 403 que devuelve el backend)
```

---

## 🧪 Verificación HTTP (pruebas reales ejecutadas)

| Escenario | Método | Resultado |
|---|---|---|
| Login con credenciales válidas | POST /api/auth/login | ✅ 200 + JWT |
| /auth/me sin token | GET /api/auth/me | ✅ 401 |
| Token manipulado | GET /api/auth/me | ✅ 401 |
| Rol paciente intenta DELETE | DELETE /api/medicos/1 | ✅ 403 |
| 5 logins fallidos seguidos | POST /api/auth/login ×5 | ✅ 401 ×5 |
| 6º intento (bloqueado) | POST /api/auth/login | ✅ 429 |
| 7º intento (bloqueo vigente) | POST /api/auth/login | ✅ 429 |
| CORS origen en whitelist | GET /api/health + Origin localhost:5173 | ✅ `Access-Control-Allow-Origin` presente |
| CORS origen desconocido | GET + Origin malicious.com | ✅ header ausente |
| Headers de seguridad | POST /api/auth/login | ✅ nosniff, DENY, no-referrer, X-XSS |
| Error 500 | Response::error('detalle',500) | ✅ genérico + requestId + ERR_INTERNAL |
| Registro nombre >100 | POST /api/auth/registro | ✅ 422 |
| Registro con `<script>` | POST /api/auth/registro | ✅ 201 con nombre saneado |

---

## 🎓 Conceptos de la presentación cubiertos

✅ CORS: política de *origin* del navegador y configuración correcta del back-end  
✅ Headers de seguridad de respuesta (OPSWAT / guía de la cátedra)  
✅ Nunca `Access-Control-Allow-Origin: *` junto a credenciales  
✅ Validación y saneamiento de TODA entrada (whitelist, longitudes, tipos)  
✅ Préparación de consultas con PDO ya existente (anti SQLi)  
✅ Rate limiting anti fuerza bruta / credential stuffing → 429  
✅ Manejo de errores sin filtrar detalles de implementación  
✅ Variables de entorno para secretos y configuración  
✅ SPA desacoplado: la API se documenta por HTTPS, el front consume con Axios  
✅ Sesión persistente en frontend (localStorage + interceptor Bearer + redirect 401)

---

## 🔄 Pendientes (para producción real - no bloqueantes)

- [ ] HTTPS obligatorio (JWT viaja en cada petición; CORS y redirects asumen dominios reales)
- [ ] Mover el rate limiter a Redis o la base de datos (hoy: archivos JSON)
- [ ] Blocklist de tokens revocados (logout server-side real)
- [ ] Refresh tokens
- [ ] 2FA/TOTP para el rol admin

---

## 📁 Archivos del módulo

| Archivo | Rol |
|---|---|
| `repositorio_backend/core/Config.php` | Configuración por entorno |
| `repositorio_backend/core/RateLimiter.php` | Anti fuerza bruta (429) |
| `repositorio_backend/core/Response.php` | Errores 5xx genéricos + requestId |
| `repositorio_backend/core/bootstrap.php` | Django de errores por entorno + generación de RateLimiter |
| `repositorio_backend/db.php` | display_errors por entorno |
| `repositorio_backend/routes.php` | CORS whitelist + headers de seguridad + preflight 204 |
| `repositorio_backend/controllers/AuthController.php` | Rate limit en login |
| `repositorio_backend/services/*.php` | Saneamiento y límites de entradas |
| `repositorio_backend/.env.example` | Modelo de configuración sin secretos |
| `repositorio_web_spa/src/api/client.js` | VITE_API_URL + timeout |
| `repositorio_web_spa/src/components/Navbar.jsx` | Badge de rol |
| `repositorio_web_spa/src/components/*/Lista*.jsx` | RBAC en UI (Eliminar solo admin) |

---

**Estado general del proyecto: 100%** (se suman seguridad de APIs + cliente web desacoplado)
**Listo para defensa ante tribunal:** ✅ SÍ