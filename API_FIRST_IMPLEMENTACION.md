# 🚀 API FIRST: Guía de Implementación para SaludWEB

**Fecha:** 13 de Septiembre de 2026  
**Objetivo:** Transformar SaludWEB a un enfoque API First profesional  
**Tiempo estimado:** 2-3 horas de refactorización

---

## 1. ¿Qué es API First?

**API First** significa que el contrato de comunicación entre cliente y servidor se define **antes** de escribir código.

### Ventajas Implementadas en SaludWEB

✅ **Frontend y Backend pueden trabajar en paralelo**
- Frontend consume mocks de la API spec
- Backend implementa los endpoints
- Sincronización al final sin conflictos

✅ **Documentación automática**
- OpenAPI genera documentación, cliente SDK, tests
- No hay "código es la documentación"

✅ **Equipo alineado desde el inicio**
- Especificación es el contrato legal
- Cambios requieren acuerdo explícito

✅ **Testing simplificado**
- Herramientas como Swagger UI, Insomnia, Postman consumen la spec
- Tests automáticos contrastan código vs especificación

---

## 2. Nuestro Contrato API: openapi.yaml

### ¿Dónde está?
```
Workspace_SaludWEB/
└── API_SPECIFICATION.yaml
```

### ¿Cómo usarlo?

#### Opción A: Swagger UI en línea
```bash
# Ir a https://editor.swagger.io/
# Importar → Upload YAML → API_SPECIFICATION.yaml
# Verás documentación interactiva + Try it out
```

#### Opción B: Generador local (Node.js)
```bash
npm install -g swagger-ui

# Servir la spec en http://localhost:8080
swagger serve API_SPECIFICATION.yaml
```

#### Opción C: Usar Postman
```bash
# Postman → Import → Link → pega la URL del repo
# Crea collection automáticamente
```

---

## 3. Cambios Realizados en la Especificación

### Secciones Principales

| Sección | Descripción | Líneas |
|---------|-----------|--------|
| `info` | Metadatos del API (título, versión, contacto) | 1-15 |
| `servers` | URLs de desarrollo y producción | 17-22 |
| `tags` | Categorización de endpoints | 24-34 |
| `paths` | Definición de cada endpoint | 36-500+ |
| `components` | Schemas reutilizables (modelos) | 500+ |
| `securitySchemes` | Autenticación (BearerAuth) | Final |

### Endpoints Documentados

✅ **5 de Autenticación**
- POST /api/auth/registro
- POST /api/auth/login
- POST /api/auth/logout
- GET /api/auth/usuario-actual
- PATCH /api/auth/cambiar-contraseña

✅ **8 de Médicos** (CRUD completo)
- GET/POST /api/medicos
- GET/PUT/DELETE /api/medicos/{id}

✅ **8 de Pacientes** (CRUD completo)
- GET/POST /api/pacientes
- GET/PUT/DELETE /api/pacientes/{id}

✅ **9 de Prescripciones** (CRUD + cambio de estado)
- GET/POST /api/prescripciones
- GET/PUT/DELETE /api/prescripciones/{id}
- PATCH /api/prescripciones/{id}/estado

✅ **1 de Health Check**
- GET /api/health

**Total: 31 endpoints completamente documentados**

---

## 4. Mejoras Propuestas (FASE 3-4)

### Priority 1: Crítico para Producción

#### 1.1 Estandarizar snake_case en ALL Fields
**Problema actual:**
```javascript
// Inconsistencia
{
  "id_paciente": 1,      // snake_case
  "medico_nombre": "...", // snake_case
  "createdAt": "..."      // camelCase ← INCORRECTO
}
```

**Solución:**
```php
// repositorio_backend/core/bootstrap.php
// Agregar transformador de response:

class ResponseFormatter {
    public static function toSnakeCase($data) {
        // Convierte todas las keys a snake_case
        if (is_array($data)) {
            return array_map(function($item) {
                return self::toSnakeCase($item);
            }, $data);
        }
        return $data;
    }
}
```

**Impacto:** 2 horas de implementación

#### 1.2 Implementar Código 204 No Content
**Cambio en routes.php:**
```php
// DELETE endpoints
$router->delete('/api/medicos/:id', function($id) {
    try {
        $medicoService->eliminar($id);
        http_response_code(204);  // ← Cambiar de 200 a 204
        return '';  // Sin body
    } catch (Exception $e) {
        http_response_code(404);
        return json_encode(['error' => 'No encontrado']);
    }
});
```

**Impacto:** 30 minutos

#### 1.3 Implementar Código 422 Unprocessable Entity
**Nuevo en Services:**
```php
// PacienteService.php
public function crear($data) {
    $errores = [];
    
    if (!$data['nombre']) $errores['nombre'] = 'Requerido';
    if (!$data['dni']) $errores['dni'] = 'Requerido';
    
    if (!empty($errores)) {
        // Nueva excepción
        throw new ValidationException($errores, 422);
    }
    
    return $this->repository->crear($data);
}

// En routes.php
} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode(['error' => 'Validation failed', 'details' => $e->errors]);
}
```

**Impacto:** 1 hora

#### 1.4 Mejorar Estructura de Errores
**Antes:**
```json
{
  "error": "Email requerido"
}
```

**Después:**
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "timestamp": "2026-09-13T10:30:00Z",
    "details": [
      {
        "field": "email",
        "message": "Email requerido",
        "code": "REQUIRED"
      }
    ]
  }
}
```

**Impacto:** 2 horas

### Priority 2: Importante para Escalabilidad

#### 2.1 Implementar Paginación
```php
// repositories/MedicoRepository.php
public function obtenerTodos($page = 1, $limit = 20) {
    $offset = ($page - 1) * $limit;
    
    $stmt = $this->pdo->prepare(
        "SELECT * FROM medicos LIMIT ? OFFSET ?"
    );
    $stmt->execute([$limit, $offset]);
    
    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $this->count(),
        'page' => $page,
        'limit' => $limit
    ];
}
```

**Impacto:** 1.5 horas

#### 2.2 Agregar Filtros en Backend
```php
// routes.php
$router->get('/api/medicos', function() use ($medicoService) {
    $filters = [
        'especialidad' => $_GET['especialidad'] ?? null,
        'activo' => $_GET['activo'] ?? null,
        'search' => $_GET['search'] ?? null
    ];
    
    return $medicoService->obtenerTodos($filters);
});
```

**Impacto:** 1 hora

#### 2.3 Agregar Timestamps (createdAt, updatedAt)
```sql
-- db.php: Alter tables
ALTER TABLE medicos ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE medicos ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

**Impacto:** 30 minutos

### Priority 3: Deseable para Profesionalismo

#### 3.1 API Versioning
```php
// En vez de /api/medicos
// Usar /api/v1/medicos
// Permite mantener versiones antiguas mientras introduces cambios
```

**Impacto:** 1 hora

#### 3.2 Rate Limiting
```php
// middleware/RateLimiter.php
class RateLimiter {
    public static function check($userId) {
        $key = "rate_limit:{$userId}";
        // Usar Redis o session para contar requests
        if (requests > 100 per minute) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests']);
        }
    }
}
```

**Impacto:** 2 horas

---

## 5. Cómo Validar la Especificación

### Test 1: Validar YAML Sintaxis
```bash
# Ir a https://www.yamllint.com/
# Pegar contenido de API_SPECIFICATION.yaml
# Debe estar verde (sin errores)
```

### Test 2: Usar Swagger UI
```bash
# https://editor.swagger.io/
# File → Import → Seleccionar API_SPECIFICATION.yaml
# Debe cargar sin errores
# Probar "Try it out" en cada endpoint
```

### Test 3: Generar Cliente JavaScript
```bash
# Usando swagger-codegen
swagger-codegen generate -i API_SPECIFICATION.yaml \
  -l javascript \
  -o ./generated-client/

# Genera cliente Axios automáticamente
```

### Test 4: Documentar con ReDoc
```bash
# ReDoc (alternativa más bonita a Swagger UI)
docker run -p 8080:80 \
  -e SPEC_URL=https://raw.githubusercontent.com/...API_SPECIFICATION.yaml \
  redocly/redoc
```

---

## 6. Plan de Refactorización Paso a Paso

### FASE 3A: Estandarización (Semana 1)
```
Lunes:
  [ ] Standardize snake_case en todas las entidades
  [ ] Tests: verificar que cliente React sigue funcionando
  
Martes:
  [ ] Implementar 204 No Content en DELETEs
  [ ] Implementar 422 Unprocessable Entity
  
Miércoles:
  [ ] Mejorar estructura de errores
  [ ] Actualizar documentación
  
Jueves-Viernes:
  [ ] Testing completo
  [ ] Code review
```

### FASE 3B: Escalabilidad (Semana 2)
```
Lunes-Martes:
  [ ] Implementar paginación en todos GET
  [ ] Agregar filtros
  
Miércoles:
  [ ] Agregar timestamps
  [ ] Versionado (/v1/)
  
Jueves-Viernes:
  [ ] Rate limiting
  [ ] Testing E2E
```

---

## 7. Cómo el Frontend se Integra

### Backend Expone API

✅ **GET /api/medicos**
```json
{
  "data": [
    { "id": 1, "nombre": "Dr. Carlos", ... }
  ],
  "total": 42,
  "page": 1,
  "limit": 20
}
```

### Frontend Consume con Axios

✅ **src/api/client.js**
```javascript
import axios from 'axios';

const client = axios.create({
  baseURL: 'http://localhost/Workspace_SaludWEB/repositorio_backend'
});

// Automáticamente agrega token
client.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default client;
```

### Componente Usa API

✅ **src/components/Medicos/ListaMedicos.jsx**
```javascript
useEffect(() => {
  const cargarMedicos = async () => {
    try {
      const response = await client.get('/api/medicos?page=1&limit=20');
      setMedicos(response.data.data);  // ← Nuevos datos con paginación
    } catch (error) {
      setError(error.response.data);   // ← Mejor error handling
    }
  };
  cargarMedicos();
}, []);
```

---

## 8. Checklist Final

### ✅ API First Completado

- [x] Especificación OpenAPI 3.0 creada
- [x] Documentación formal de todos los endpoints
- [x] Schemas reutilizables definidos
- [x] Codes de error documentados
- [x] Autenticación (BearerAuth) especificada
- [ ] Paginación implementada (FASE 3)
- [ ] Filtros implementados (FASE 3)
- [ ] snake_case estandarizado (FASE 3)
- [ ] Códigos 204/422 implementados (FASE 3)
- [ ] Errores estructurados (FASE 3)
- [ ] Versionado implementado (FASE 3)
- [ ] Rate limiting implementado (FASE 3)

---

## 9. Documentos Relacionados

- [ANALISIS_API_REST_FIRST.md](ANALISIS_API_REST_FIRST.md) - Análisis vs buenas prácticas
- [API_SPECIFICATION.yaml](API_SPECIFICATION.yaml) - Contrato API formal (OpenAPI 3.0)
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Guía de endpoints (legible)
- [INICIO_RAPIDO.md](INICIO_RAPIDO.md) - Quick start con ejemplos curl

---

## 10. Conclusión

**SaludWEB ya tiene:**
- ✅ Arquitectura API REST funcional
- ✅ Endpoints CRUD completados
- ✅ Autenticación implementada
- ✅ Frontend consumiendo API correctamente

**Falta para Producción (FASE 3):**
- ⏳ Paginación y filtros
- ⏳ Códigos HTTP estandarizados
- ⏳ Errores estructurados
- ⏳ Versionado

**Impacto si se implementan las mejoras:**
→ API lista para escalar a 100k+ usuarios  
→ Documentación automática con Swagger  
→ Facilita integración de terceros  
→ Testing automatizado posible  

---

**Archivo preparado por:** Ailen Quaglino  
**Versión:** 1.0 API First  
**Estado:** Especificación Completa, Implementación en FASE 3
