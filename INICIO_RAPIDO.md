# 🚀 GUÍA DE INICIO - SaludWEB (Programación IV)

**Proyecto desarrollado según los estándares de Programación IV**  
Arquitectura moderna: Backend API REST + Frontend SPA + Mobile

---

## 📋 Requisitos

- PHP 7.4+ con PDO y MySQL
- MySQL 5.7+
- Node.js 16+
- npm o yarn
- XAMPP (o similar, si lo usas)

---

## 🔧 SETUP INICIAL

### 1️⃣ Backend (API REST)

**El backend está en:** `repositorio_backend/`

```bash
# No necesita instalación, es PHP puro
# Solo asegúrate que XAMPP está corriendo

# Luego accede a:
http://localhost/Workspace_SaludWEB/repositorio_backend/api/health
```

**Verificar que funciona:**
```bash
# En tu navegador o terminal, ejecuta:
curl http://localhost/Workspace_SaludWEB/repositorio_backend/api/health

# Deberías ver:
# {"status":"ok","timestamp":"2026-09-13T10:30:00+00:00","message":"API SaludWEB v1.0"}
```

---

### 2️⃣ Frontend (React SPA)

**El frontend está en:** `repositorio_web_spa/`

```bash
# 1. Entra a la carpeta
cd repositorio_web_spa

# 2. Instala dependencias
npm install

# 3. Ejecuta el servidor de desarrollo
npm run dev

# 4. Abre en tu navegador
http://localhost:5173
```

**¿Qué verás?**
- Página de Login (sin credenciales)
- Puedes registrarte o usar datos de prueba
- Dashboard con navegación a Médicos, Pacientes, Prescripciones

---

## 🧪 PRUEBAS DE API (con curl)

### Registrar un usuario

```bash
curl -X POST http://localhost/Workspace_SaludWEB/repositorio_backend/api/auth/registro \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123",
    "nombre": "Juan Pérez",
    "tipo_usuario": "paciente"
  }'
```

### Hacer login

```bash
curl -X POST http://localhost/Workspace_SaludWEB/repositorio_backend/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

**Respuesta esperada:**
```json
{
  "id": 1,
  "email": "test@example.com",
  "nombre": "Juan Pérez",
  "tipo_usuario": "paciente",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzYWx1ZHdlYi1hcGki...",
  "expires_at": "2026-09-14T00:56:24+02:00"
}
```
> El `token` es un **JWT real** (Header.Payload.Firma) firmado con HS256.

### Acceder a una ruta protegida

```bash
# Guarda el token y usalo como Bearer
curl http://localhost/Workspace_SaludWEB/repositorio_backend/api/auth/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzYWx1ZHdlYi1hcGki..."

# Sin token → 401
curl http://localhost/Workspace_SaludWEB/repositorio_backend/api/auth/me
# {"ok":false,"mensaje":"Token no proporcionado","errores":[]}

# Con un token de rol "paciente" intentando borrar (→ 403)
curl -X DELETE http://localhost/Workspace_SaludWEB/repositorio_backend/api/medicos/1 \
  -H "Authorization: Bearer <token-de-paciente>"
# {"ok":false,"mensaje":"Forbidden: no tenés permisos para realizar esta acción","errores":[]}
```

### Obtener lista de médicos (catálogo público)

```bash
curl http://localhost/Workspace_SaludWEB/repositorio_backend/api/medicos
```

---

## 📚 DOCUMENTACIÓN

- **API REST:** Ver [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Backend:** Ver [repositorio_backend/README.md](repositorio_backend/README.md)
- **Frontend:** Ver [repositorio_web_spa/README.md](repositorio_web_spa/README.md)

---

## 🗂️ ESTRUCTURA DEL PROYECTO

```
Workspace_SaludWEB/
├── repositorio_backend/          # Backend API REST (PHP)
│   ├── api/
│   │   └── medicos, pacientes...
│   ├── controllers/              # Controladores HTTP
│   ├── services/                 # Lógica de negocio
│   ├── persistence/              # Acceso a datos (Repositories)
│   ├── core/
│   │   ├── bootstrap.php         # Inicialización
│   │   ├── Router.php            # Enrutador REST
│   │   ├── JwtService.php        # Emisión/verificación de JWT
│   │   ├── AuthMiddleware.php    # Middleware 401/403 por rol
│   │   └── Secret.php            # Gestión del JWT_SECRET
│   ├── vendor/                   # Dependencias (firebase/php-jwt)
│   ├── db.php                    # Conexión a MySQL
│   ├── index.php                 # Punto de entrada
│   └── routes.php                # Definición de rutas API
│
├── repositorio_web_spa/          # Frontend SPA (React + Vite)
│   ├── src/
│   │   ├── api/
│   │   │   └── client.js         # Cliente HTTP (Axios + interceptor Bearer)
│   │   ├── context/
│   │   │   └── AuthContext.js    # Gestión de autenticación
│   │   ├── hooks/
│   │   │   └── useAuth.js        # Hook de autenticación
│   │   ├── components/
│   │   │   ├── Auth/             # Login, Register
│   │   │   ├── Dashboard/        # Panel principal
│   │   │   └── Navbar.jsx        # Navegación
│   │   ├── App.jsx               # App principal
│   │   └── main.jsx              # Punto de entrada React
│   ├── package.json
│   ├── vite.config.js
│   └── index.html
│
├── repositorio_mobile/           # Mobile (próximo a implementar)
└── API_DOCUMENTATION.md          # Documentación de endpoints
```

---

## 🔑 CREDENCIALES DE PRUEBA

**Usuario predeterminado creado en la base de datos:**
- Email: `admin@salud.com`
- Contraseña: `admin123`

*(Debes registrarte para probar o usar el cliente de prueba)*

---

## 🐛 TROUBLESHOOTING

### Error: "Cannot connect to API"
- Verifica que XAMPP está corriendo
- Verifica que la URL del API es correcta: `http://localhost/Workspace_SaludWEB/repositorio_backend`
- Revisa la consola del navegador (F12) para ver errores

### Error: "Database connection failed"
- Verifica que MySQL está corriendo
- Verifica credenciales en `repositorio_backend/db.php`
- Asegúrate que la base de datos `pacientes` existe

### Error: "npm install fails"
- Elimina `node_modules/` y `package-lock.json`
- Corre `npm install` de nuevo
- Asegúrate de usar Node.js 16+

---

## 📦 BUILD PARA PRODUCCIÓN

### Frontend

```bash
cd repositorio_web_spa
npm run build

# Esto generará una carpeta `dist/` lista para producción
```

### Backend

El backend de PHP no necesita compilación. Solo cópialo a tu servidor web.

---

## 🎓 CONCEPTOS IMPLEMENTADOS (Programación IV)

✅ **Arquitectura Cliente-Servidor**
✅ **API REST con JSON**
✅ **Patrones de Diseño**: MVC, Repository, Inyección de Dependencias
✅ **Separación de Responsabilidades**: Backend y Frontend desacoplados
✅ **Autenticación con JWT** (firebase/php-jwt, HS256, claims estándar)
✅ **Rutas públicas vs protegidas** (401) y **roles/permisos** (403)
✅ **bcrypt** con password_hash / password_verify
✅ **CORS (Cross-Origin Resource Sharing)**
✅ **Single Page Application (SPA)**
✅ **Gestión de Estado con Context API**
✅ **Componentes Reutilizables**
✅ **Validaciones en Cliente y Servidor**

---

## 🚀 PRÓXIMOS PASOS

1. **Completar componentes CRUD** en React (Médicos, Pacientes, Prescripciones)
2. **Implementar Mobile App** (React Native o Flutter)
3. **Testing** (Jest, Cypress)
4. **Deployment** a servidor en producción
5. **Optimización** y mejoras de UX

---

## 💬 PREGUNTAS FRECUENTES

**¿Dónde está la base de datos?**
- En MySQL. Nombre: `pacientes`. Se crea automáticamente en el primer acceso a `db.php`

**¿Cómo agrego nuevas rutas?**
- Backend: edita `repositorio_backend/routes.php`
- Frontend: edita `repositorio_web_spa/src/App.jsx`

**¿Cómo consumo la API en React?**
- Usa `client.js` (cliente Axios preconfigurado)
- Ejemplo: `await client.get('/api/medicos')`

**¿Dónde agrego estilos personalizados?**
- Frontend: crea archivos `.css` en la carpeta del componente
- Luego importa: `import './MiComponente.css'`

---

**Creado: 13/09/2026**  
**Versión: 1.0 - Programación IV**

¡Listo para defender ante tribunal! 🎓
