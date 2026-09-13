# 📚 Documentación API REST - SaludWEB

**Programación IV - Arquitectura Moderna de Aplicaciones**

---

## 🎯 Resumen Ejecutivo

Esta es una **API REST profesional** que expone endpoints JSON para un sistema de gestión de salud (médicos, pacientes, prescripciones).

- **Arquitectura**: REST + JSON
- **Backend**: PHP puro con PDO
- **Patrón**: MVC (Modelo-Vista-Controlador)
- **Base de datos**: MySQL
- **CORS**: Habilitado para consumidores remotos
- **Autenticación**: JWT (JSON Web Tokens) con `firebase/php-jwt`

---

## 🔐 AUTENTICACIÓN CON JWT (módulo Programación IV)

### Clasificación de rutas

| Tipo de ruta | Requisito | Respuesta si falla |
|---|---|---|
| **Pública** | Ninguno | - |
| **Protegida** | `Authorization: Bearer <jwt>` | `401 Unauthorized` |
| **Admin** | JWT + rol `admin` | `403 Forbidden` |

### Rutas PÚBLICAS (sin token)

```
GET  /api/health                 → health check
GET  /api/obras-sociales         → catálogo de obras sociales
GET  /api/medicos                → catálogo de médicos
GET  /api/medicos/{id}           → detalle de médico
GET  /api/pacientes              → listado de pacientes
GET  /api/pacientes/{id}         → detalle de paciente
POST /api/auth/registro          → crear cuenta
POST /api/auth/login             → obtener JWT
```

### Rutas PROTEGIDAS (requieren JWT)

```
GET    /api/auth/me              → datos del usuario autenticado
POST   /api/auth/logout          → cerrar sesión
POST   /api/auth/cambiar-contrasena
POST   /api/medicos              → crear médico
PUT    /api/medicos/{id}         → actualizar médico
PATCH  /api/medicos/{id}         → actualizar médico
POST   /api/pacientes            → crear paciente
PUT    /api/pacientes/{id}       → actualizar paciente
PATCH  /api/pacientes/{id}       → actualizar paciente
GET    /api/prescripciones       → listar recetas
GET    /api/prescripciones/{id}  → ver receta
POST   /api/prescripciones       → crear receta
PUT    /api/prescripciones/{id}  → actualizar receta
PATCH  /api/prescripciones/{id}  → actualizar receta
PATCH  /api/prescripciones/{id}/estado → cambiar estado
```

### Rutas SOLO ADMIN (requieren JWT + rol `admin`)

```
DELETE /api/medicos/{id}
DELETE /api/pacientes/{id}
DELETE /api/prescripciones/{id}
```

### El token JWT

Formato: `xxxxx.yyyyy.zzzzz` (Header.Payload.Firma) firmado con **HS256**.

Claims utilizados:

```json
{
  "iss": "saludweb-api",
  "iat": 1789336584,
  "exp": 1789340184,
  "sub": 2,
  "rol": "admin",
  "email": "usuario@salud.com",
  "nombre": "Juan"
}
```

- Vigencia: **60 minutos** (claim `exp`).
- El secreto se lee de la variable de entorno `JWT_SECRET` o se genera/persiste en `storage/secret.key`.
- **Nunca** se guardan contraseñas ni datos sensibles en el payload (es decodificable).
- `401` = no autenticado · `403` = autenticado pero sin permiso. No confundirlos.

---

## 🚀 Endpoints Disponibles

### Health Check
```
GET /api/health
```
Verifica que la API está operativa.

**Response (200 OK):**
```json
{
  "status": "ok",
  "timestamp": "2026-09-13T10:30:00+00:00",
  "message": "API SaludWEB v1.0 - Programación IV"
}
```

---

## 👨‍⚕️ MÉDICOS

### Listar todos
```
GET /api/medicos
```
**Response (200 OK):**
```json
[
  {
    "id": 1,
    "nombre": "Dr. Juan Pérez",
    "matricula": "12345",
    "especialidad": "Medicina General",
    "activo": 1,
    "creado_at": "2026-01-15 10:00:00"
  },
  ...
]
```

### Obtener uno
```
GET /api/medicos/{id}
```
**Response (200 OK):** Un médico en JSON, o **404** si no existe.

### Crear
```
POST /api/medicos
Content-Type: application/json

{
  "nombre": "Dra. Ana López",
  "matricula": "99999",
  "especialidad": "Cardiología",
  "activo": 1
}
```
**Response (201 Created):** El médico creado con su ID.

### Actualizar (completo o parcial)
```
PUT /api/medicos/{id}
PATCH /api/medicos/{id}
Content-Type: application/json

{
  "especialidad": "Pediatría"
}
```
**Response (200 OK):** El médico actualizado.

### Eliminar
```
DELETE /api/medicos/{id}
```
**Response (200 OK):** Mensaje de confirmación, o **404** si no existe.

---

## 👤 PACIENTES

### Listar todos
```
GET /api/pacientes
```

### Obtener uno
```
GET /api/pacientes/{id}
```

### Crear
```
POST /api/pacientes
Content-Type: application/json

{
  "dni": "12345678",
  "nombre": "María García",
  "id_obra_social": 1,
  "activo": 1
}
```

### Actualizar
```
PUT /api/pacientes/{id}
PATCH /api/pacientes/{id}
```

### Eliminar
```
DELETE /api/pacientes/{id}
```

---

## 💊 PRESCRIPCIONES

### Listar todas
```
GET /api/prescripciones
```

### Obtener una
```
GET /api/prescripciones/{id}
```

### Crear
```
POST /api/prescripciones
Content-Type: application/json

{
  "id_paciente": 1,
  "id_medico": 1,
  "medicamentos": [
    { "nombre": "Paracetamol", "dosis": "500mg", "cantidad": 20 },
    { "nombre": "Ibuprofeno", "dosis": "400mg", "cantidad": 10 }
  ],
  "indicaciones": "Tomar cada 8 horas",
  "fecha_vencimiento": "2026-12-31"
}
```

### Actualizar
```
PUT /api/prescripciones/{id}
PATCH /api/prescripciones/{id}
```

### Cambiar estado
```
PATCH /api/prescripciones/{id}/estado
Content-Type: application/json

{
  "estado": "dispensada"
}
```
Estados permitidos: `activa`, `vencida`, `dispensada`, `cancelada`

### Eliminar
```
DELETE /api/prescripciones/{id}
```

---

## 🔐 Códigos HTTP

| Código | Significado |
|--------|-------------|
| **200** | OK - Solicitud exitosa |
| **201** | Created - Recurso creado exitosamente |
| **400** | Bad Request - Datos inválidos |
| **401** | Unauthorized - No autenticado / token ausente, inválido o expirado |
| **403** | Forbidden - Autenticado pero sin permiso (rol insuficiente) |
| **404** | Not Found - Recurso no encontrado |
| **422** | Unprocessable Entity - Validación de negocio fallida |
| **429** | Too Many Requests - Excediste el límite de intentos de login (5 cada 15 min por IP) |
| **500** | Internal Server Error - Error en el servidor (respuesta genérica con `requestId`) |
| **501** | Not Implemented - Endpoint no implementado aún |

---

## 📦 Estructura de Errores

Todos los errores devuelven JSON:

```json
{
  "error": "Descripción del error"
}
```

Formato real del contrato (`ok` / `mensaje` / `errores`):

```json
// 422 - Validación fallida
{ "ok": false, "mensaje": "El nombre es obligatorio", "errores": [] }

// 500 - Error interno (detalle oculto al cliente + requestId para soporte)
{ "ok": false, "mensaje": "Error interno del servidor", "errores": [], "requestId": "05c5ac79c49087a4", "code": "ERR_INTERNAL" }
```

> En errores **5xx** el detalle real nunca se expone: se reemplaza por un mensaje
> genérico y se genera un `requestId` para correlacionar con los logs.

---

## 🛡️ Seguridad (módulo "Seguridad Básica para APIs")

### CORS
- Whitelist por `CORS_ORIGINS` (nunca `*`): `http://localhost:5173`, `http://127.0.0.1:5173`, `http://localhost`, `http://127.0.0.1`.
- Si el `Origin` de la petición **no** está en la lista, **no** se envía el header `Access-Control-Allow-Origin` (el navegador bloquea la respuesta).
- Preflight `OPTIONS` → `204 No Content` + `Vary: Origin`.

### Headers de seguridad en toda respuesta
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Referrer-Policy: no-referrer
X-XSS-Protection: 1; mode=block
```

### Rate limiting en login (anti fuerza bruta)
- **5 intentos fallidos por IP cada 15 minutos** en `POST /api/auth/login`.
- Al superar el límite:

```json
// 429 Too Many Requests
{ "ok": false, "mensaje": "Demasiados intentos fallidos. Intentá nuevamente en 15 minutos.", "errores": [] }
```

- El contador se limpia automáticamente tras un login exitoso.

### Validación y saneamiento de entradas
- Emails normalizados (`trim` + `minúsculas`) y verificados con `FILTER_VALIDATE_EMAIL` (máx. 255).
- Nombres sin etiquetas HTML (`strip_tags`) y con límite de longitud.
- Contraseñas entre **6 y 72** caracteres (límite de bcrypt).
- Límites de tamaño en todos los campos de catálogos y en el número de medicamentos por prescripción (máx. 50).

---

## 🔐 AUTENTICACIÓN

### Registrar nuevo usuario
```
POST /api/auth/registro
Content-Type: application/json

{
  "email": "usuario@example.com",
  "password": "MiContraseña123",
  "nombre": "Juan García",
  "tipo_usuario": "paciente"
}
```
**Tipos permitidos:** `paciente`, `medico`, `admin`

**Response (201 Created):**
```json
{
  "id": 1,
  "email": "usuario@example.com",
  "nombre": "Juan García",
  "tipo_usuario": "paciente",
  "id_paciente": null,
  "id_medico": null,
  "activo": 1,
  "creado_at": "2026-09-13 10:30:00"
}
```

### Login
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "usuario@example.com",
  "password": "MiContraseña123"
}
```

**Response (200 OK):**
```json
{
  "id": 2,
  "email": "usuario@example.com",
  "nombre": "Juan García",
  "tipo_usuario": "admin",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzYWx1ZHdlYi1hcGki...",
  "expires_at": "2026-09-14T00:56:24+02:00"
}
```
> El `token` es un JWT real (Header.Payload.Firma). Usalo en cada petición protegida.

### Casos de error de autenticación

```json
// 401 - Sin token
{ "ok": false, "mensaje": "Token no proporcionado", "errores": [] }

// 401 - Token manipulado o expirado
{ "ok": false, "mensaje": "Token inválido o expirado", "errores": [] }

// 403 - Rol sin permiso
{ "ok": false, "mensaje": "Forbidden: no tenés permisos para realizar esta acción", "errores": [] }

// 429 - Límite de intentos de login alcanzado
{ "ok": false, "mensaje": "Demasiados intentos fallidos. Intentá nuevamente en 15 minutos.", "errores": [] }
```

### Obtener usuario actual
```
GET /api/auth/me
Authorization: Bearer <token>
```
**Response (200 OK):** Datos del usuario autenticado.

### Cambiar contraseña
```
POST /api/auth/cambiar-contraseña
Authorization: Bearer <token>
Content-Type: application/json

{
  "passwordActual": "MiContraseña123",
  "passwordNueva": "NuevaContraseña456"
}
```
**Response (200 OK):** Mensaje de confirmación.

### Logout
```
POST /api/auth/logout
Authorization: Bearer <token>
```
**Response (200 OK):**
```json
{
  "mensaje": "Sesión cerrada correctamente"
}
```

---

```
HTTP Request (Cliente/Frontend)
    ↓
Router.php (enrutamiento)
    ↓
Controller (maneja HTTP)
    ↓
Service (lógica de negocio)
    ↓
Repository (acceso a datos)
    ↓
PDO (driver MySQL)
    ↓
MySQL (base de datos)
```

---

## 🔄 Próximas Implementaciones

- [x] Autenticación con JWT (módulo aplicado ✅)
- [x] Rate limiting por IP en login (módulo de seguridad aplicado ✅)
- [ ] Refresh tokens y blocklist de tokens revocados
- [ ] Endpoints de Medicamentos
- [ ] Endpoints de Triages
- [ ] Validación de reglas de negocio complejas
- [ ] Generación de QR para recetas
- [ ] Auditoría de operaciones

---

## 💡 Ejemplo completo: Crear una receta

```bash
# 1. Verificar que la API funciona
curl http://localhost/Workspace_SaludWEB/repositorio_backend/api/health

# 2. Obtener pacientes
curl http://localhost/Workspace_SaludWEB/repositorio_backend/api/pacientes

# 3. Obtener médicos
curl http://localhost/Workspace_SaludWEB/repositorio_backend/api/medicos

# 4. Crear una prescripción
curl -X POST http://localhost/Workspace_SaludWEB/repositorio_backend/api/prescripciones \
  -H "Content-Type: application/json" \
  -d '{
    "id_paciente": 1,
    "id_medico": 1,
    "medicamentos": [
      {"nombre": "Paracetamol", "dosis": "500mg"}
    ],
    "indicaciones": "Cada 8 horas",
    "fecha_vencimiento": "2026-12-31"
  }'
```

---

**Documentación versión 1.0**  
Creada: 2026-09-13
