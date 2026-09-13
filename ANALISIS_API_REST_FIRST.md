# 📋 ANÁLISIS: API REST de SaludWEB vs Buenas Prácticas

**Fecha:** 13 de Septiembre de 2026  
**Evaluación:** Auditoría contra criterios REST

---

## 1. Evaluación de Recursos y Endpoints

### ✅ CUMPLIDAS (Buenas Prácticas)

| Criterio | SaludWEB | Verificación |
|----------|----------|-------------|
| **Sustantivos en plural** | `/medicos`, `/pacientes`, `/prescripciones` | ✅ Correcto |
| **Sin verbos en URL** | No hay `/getMedicos`, `/createPaciente` | ✅ Correcto |
| **Minúsculas** | Todos los endpoints en minúsculas | ✅ Correcto |
| **Identificadores claros** | `/medicos/{id}`, `/pacientes/{id}` | ✅ Correcto |
| **Relaciones jerárquicas** | `/medicos/{id}/pacientes` es posible | ✅ Bien modelado |
| **Recursos anidados razonables** | Máximo 2 niveles de nesting | ✅ Correcto |

### ⚠️ MEJORAS SUGERIDAS

#### 1. Endpoints de Autenticación - Falta versión clara

**Estado Actual:**
```
POST /api/auth/registro
POST /api/auth/login
POST /api/auth/logout
PATCH /api/auth/cambiar-contraseña
GET /api/auth/usuario-actual
```

**Mejora Propuesta:**
```
POST /auth/register         ← En inglés, más estándar
POST /auth/login
POST /auth/logout
PATCH /auth/change-password ← Snake-case consistente
GET /auth/me               ← Más conciso que "usuario-actual"
```

#### 2. Health Check - Endpoint faltante (crítico para DevOps)

**Agregar:**
```
GET /health              ← Sin autenticación
GET /api/health          ← Con autenticación (opcional)
```

#### 3. Paginación - No implementada

**Endpoints actuales retornan TODO.** Con 1000 pacientes → problema de performance.

**Mejora Propuesta:**
```
GET /api/patients?page=1&limit=20
GET /api/medications?page=2&limit=50&sort=date_desc
```

#### 4. Filtros y búsqueda - Parcialmente implementados

**Estado Actual:** Frontend filtra en cliente (ineficiente).

**Mejora Propuesta:**
```
GET /api/patients?search=juan&status=active
GET /api/prescriptions?status=active&date_from=2026-01-01&date_to=2026-12-31
GET /api/doctors?specialty=cardiology
```

#### 5. Cambio de Estado - Endpoint específico innecesario

**Estado Actual:**
```
PATCH /api/prescripciones/{id}/estado
```

**Mejor Práctica (unificado):**
```
PATCH /api/prescriptions/{id}
Body: { "status": "dispensada" }
```

---

## 2. Evaluación de Métodos HTTP

### ✅ CUMPLIDAS

| Operación | Método | Código | SaludWEB | Estado |
|-----------|--------|--------|----------|--------|
| Listar | GET | 200 | ✅ Correcto | ✅ |
| Detalle | GET | 200 | ✅ Correcto | ✅ |
| Crear | POST | 201 | ✅ Correcto | ✅ |
| Actualizar | PUT | 200 | ⚠️ Debería ser PATCH | ⚠️ |
| Actualizar parcial | PATCH | 200 | ✅ Correcto (prescripciones estado) | ✅ |
| Eliminar | DELETE | 204 | ⚠️ Devuelve 200 | ⚠️ |

### ⚠️ MEJORAS RECOMENDADAS

**1. PUT vs PATCH**
- Actual: Usamos PUT para editar médicos/pacientes
- Mejor: Usar PATCH para actualizaciones parciales
- Razón: El cliente no necesita enviar TODOS los campos

**2. Código 204 No Content**
- Actual: DELETE devuelve 200 OK
- Mejor: Devolver 204 No Content (sin body)
- Razón: No hay contenido que retornar

**3. Falta códigos de estado específicos**
- Falta: 422 Unprocessable Entity (validación de negocio)
- Actual: Solo 400 Bad Request

---

## 3. Evaluación de Códigos de Estado HTTP

### Estado Actual en SaludWEB

```javascript
// repositorio_backend/routes.php

✅ 201 Created      → POST exitoso
✅ 200 OK           → GET, PUT, PATCH exitosos
⚠️ 204 No Content   → DELETE debería retornar esto
✅ 400 Bad Request  → Datos inválidos
✅ 401 Unauthorized → Sin autenticación
❌ 403 Forbidden    → NO IMPLEMENTADO
✅ 404 Not Found    → Recurso inexistente
⚠️ 422 Unprocessable Entity → NO IMPLEMENTADO
✅ 500 Server Error → Excepciones no manejadas
```

### Códigos Faltantes Críticos

| Código | Caso de Uso | Ejemplo |
|--------|-----------|---------|
| **403** | Autenticado pero sin permiso | Usuario intenta editar médico pero solo es paciente |
| **422** | Validación de negocio falla | DNI duplicado, email en uso |
| **409** | Conflicto de datos | Intentar cambiar prescripción ya dispensada |
| **429** | Too Many Requests | Rate limiting (para futuro) |

---

## 4. Evaluación de JSON

### ✅ CUMPLIDAS

```javascript
// Usuario - Bien estructurado
{
  "id": 1,
  "email": "user@example.com",
  "nombre": "Juan Pérez",
  "tipo_usuario": "paciente",
  "createdAt": "2026-09-13T10:30:00Z"    ✅ ISO 8601
}

// Médico - Estructura clara
{
  "id": 1,
  "nombre": "Dr. Carlos",
  "especialidad": "Cardiología",
  "activo": true
}
```

### ⚠️ INCONSISTENCIAS ENCONTRADAS

**Problema #1: Mezcla de convenciones de nombres**
```javascript
// ❌ Inconsistente en diferentes entidades
{
  "id_paciente": 1,         // snake_case
  "medico_nombre": "...",   // snake_case
  "fecha_prescripcion": "...", // snake_case
  "createdAt": "...",       // camelCase ← INCONSISTENTE
}
```

**Problema #2: Omisión de fechas en algunas respuestas**
```javascript
// Médicos NO incluyen fecha de creación
{
  "id": 1,
  "nombre": "...",
  "email": "...",
  // ❌ Falta createdAt, updatedAt
}

// Prescripciones SÍ incluyen
{
  "id": 1,
  "fecha_prescripcion": "2026-09-13",
  // ✅ Presente
}
```

**Problema #3: Arrays de datos sin envolvimiento**

Actual (algunos endpoints):
```javascript
[
  { "id": 1, "nombre": "..." },
  { "id": 2, "nombre": "..." }
]  // ❌ Array raíz
```

Mejor Práctica:
```javascript
{
  "data": [
    { "id": 1, "nombre": "..." },
    { "id": 2, "nombre": "..." }
  ],
  "total": 2,
  "page": 1,
  "limit": 20
}  // ✅ Envuelto con metadata
```

---

## 5. Evaluación de Estructura de Errores

### ✅ IMPLEMENTADO

```php
// Ejemplo en controllers:
try {
    // ... lógica
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
```

### ⚠️ PROBLEMAS IDENTIFICADOS

**Problema #1: Estructura de error inconsistente**

Actual:
```json
{
  "error": "Usuario no autenticado"
}
```

Mejor:
```json
{
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Usuario no autenticado",
    "timestamp": "2026-09-13T10:30:00Z"
  }
}
```

**Problema #2: Errores de validación no estructurados**

Actual:
```json
{
  "error": "Email requerido"
}
```

Mejor:
```json
{
  "error": "Validation failed",
  "details": [
    {
      "field": "email",
      "message": "Email requerido",
      "code": "REQUIRED"
    },
    {
      "field": "password",
      "message": "Mínimo 6 caracteres",
      "code": "MIN_LENGTH"
    }
  ]
}
```

**Problema #3: Falta HTTP status en error de validación de negocio**

Actual:
```
HTTP 400 Bad Request
{ "error": "DNI ya registrado" }
```

Mejor:
```
HTTP 422 Unprocessable Entity
{ "error": "Validation failed", "details": [...] }
```

---

## 6. Evaluación: ¿API orientada a páginas?

### ✅ BIEN - No hay antipatrones

✅ No hay verbos en endpoints  
✅ No hay endpoints específicos para pantallas  
✅ No mezclan datos de negocio con UI  
✅ API es reutilizable para múltiples clientes  

### ⚠️ PENDIENTE

⚠️ No hay versionado explícito (`/v1/`, `/v2/`)  
⚠️ No hay documentación OpenAPI/Swagger automática  

---

## 7. Resumen de Cumplimiento REST

| Aspecto | Cumplimiento | Calificación |
|---------|--------------|------------|
| **Recursos y URIs** | 85% | ✅ Bien |
| **Métodos HTTP** | 80% | ✅ Bien |
| **Códigos de Estado** | 60% | ⚠️ Mejorable |
| **JSON Structure** | 70% | ⚠️ Mejorable |
| **Error Handling** | 50% | ⚠️ Crítico |
| **No orientada a páginas** | 100% | ✅ Excelente |
| **API First mindset** | 75% | ✅ Bien |
| **TOTAL** | **74%** | ⚠️ **BUENA, CON MEJORAS** |

---

## 8. Propuesta de Refactorización (FASE 3)

### Priority 1 (Crítico)
- [ ] Unificar convenciones: `snake_case` en TODOS los campos
- [ ] Implementar código 422 para validaciones
- [ ] Mejorar estructura de errores
- [ ] Agregar DELETE con código 204

### Priority 2 (Importante)
- [ ] Implementar paginación (limit, page, total)
- [ ] Agregar filtros en backend
- [ ] Cambiar `/auth/` a endpoints en inglés
- [ ] Agregar campos de timestamp (createdAt, updatedAt)

### Priority 3 (Deseable)
- [ ] Crear especificación OpenAPI 3.0
- [ ] Agregar versionado (`/v1/`)
- [ ] Rate limiting
- [ ] HATEOAS links (opcional)

---

## 9. Conclusión

**Estado:** La API de SaludWEB es **FUNCIONAL y FUNDAMENTALMENTE CORRECTA** ✅

Sigue las buenas prácticas de REST en un 74% de los casos. Es suficiente para defensa ante tribunal, pero hay mejoras específicas que elevarían la calidad a nivel profesional.

**Recomendación:** 
- ✅ MANTENER como está para defensa
- ⚠️ REFACTORIZAR después con las Priority 1 antes de producción

**Documento siguiente:** Especificación OpenAPI 3.0 formal → [API_SPECIFICATION.yaml](API_SPECIFICATION.yaml)
