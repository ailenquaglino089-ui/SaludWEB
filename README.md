# SaludWEB

Sistema de gestión de salud **SaludWEB PRO**. El proyecto está dividido en tres
repositorios que comparten la misma API, la misma identidad visual y el mismo flujo
de turnos: web, backend y mobile.

Repositorio de portada: <https://github.com/ailenquaglino089-ui/SaludWEB>.

## Demo pública

<https://saludweb.taileeeb72.ts.net/>

Compartir siempre la **raíz**. No compartir `/login` ni `/register`: esos enlaces
abren el formulario y no muestran primero la HOME.

Credenciales de demostración:

- Paciente: `paciente@prueba.com` / `paciente123`
- Médico: `medico@prueba.com` / `medico123`

La demo funciona mientras la PC esté encendida, con el backend iniciado y Tailscale
activo.

## Repositorios publicados

| Repositorio | Contenido | Documentación |
| --- | --- | --- |
| [SaludWEB_Web](https://github.com/ailenquaglino089-ui/SaludWEB_Web) | SPA en React + Vite, HOME pública, turnera y paneles por rol. | [README](https://github.com/ailenquaglino089-ui/SaludWEB_Web/blob/main/README.md) |
| [SaludWEB_Backend](https://github.com/ailenquaglino089-ui/SaludWEB_Backend) | API REST en PHP, JWT, turnos, recetas y estadísticas. | [README](https://github.com/ailenquaglino089-ui/SaludWEB_Backend/blob/main/README.md) |
| [SaludWEB_Mobile](https://github.com/ailenquaglino089-ui/SaludWEB_Mobile) | App en React Native + Expo que consume la misma API. | [README](https://github.com/ailenquaglino089-ui/SaludWEB_Mobile/blob/main/README.md) |

Este repositorio es la **portada documental** del workspace. El código vive en los
tres repositorios anteriores; las carpetas locales se excluyen con `.gitignore`.

## Qué se publicó en la iteración 13-14/11/2026

La iteración se concentró en la SPA web. El backend y la app móvil no cambiaron de
código: reutilizan los mismos endpoints y permisos.

### HOME pública

- `/` siempre muestra la HOME, con o sin sesión. Si hay sesión, los botones cambian
  a **Ir al panel** y **Ver turnera**.
- La HOME replica el flyer de presentación: fondo azul tecnológico, logo blanco,
  cuatro funciones, CTA, firma **Ailen Quaglino Fail** y robot con netbook.
- La mujer del flyer es un SVG inline con cabello largo y claro, flequillo recto,
  anteojos con bordes violeta oscuro, remera roja de mangas largas y collar gris en
  forma de luna con brillo suave.
- En mobile el flyer pasa a una columna y los botones ocupan todo el ancho.

### Agenda del médico

- El Dashboard del médico suma **Pacientes que reservaron**, con hasta 5 turnos de
  los próximos 30 días.
- **Ver mi agenda completa** lleva a `/turnera/mi-agenda`, protegida por
  `MedicoRoute`.
- La agenda consulta `GET /api/turnos` y el backend sigue limitando los datos al
  médico autenticado.

### Enlaces y lanzador

- `scripts/iniciar_saludweb.bat` abre Chrome en `/`, no en `/turnera`.
- Login y registro agregan **Volver al inicio** para que en mobile siempre haya una
  salida clara hacia la HOME.
- Se corrigió el enlace público que se comparte por WhatsApp y se documentó cómo
  limpiar una PWA vieja en el teléfono.

### Documentación

- `SaludWEB_Web/README.md`: resumen de la iteración, archivos, verificación y flujo
  de publicación.
- `SaludWEB_Web/docs/PRESENTACION.md`: guion completo para la demo o defensa.
- `SaludWEB_Mobile/README.md` y `SaludWEB_Backend/README.md`: aclaran que esta
  iteración no cambió su código y enlazan al PR principal.
- `SaludWEB_Web/docs/brand/README.md`: paquete de marca y uso del logo en la HOME.

## Iteración 16/11/2026: robot con netbook y HOME estable

La iteración se concentró otra vez en la SPA web. El backend, la base de datos y
la app móvil no cambiaron de código.

### Robot programador

- El robot del flyer ahora usa una **netbook** compacta: marco grueso, pantalla
  chica, cámara, bisagra, teclado, touchpad y manos apoyadas.
- La pantalla muestra una ventana de código con puntos de color, líneas, llaves
  `</>` y cursor.
- El SVG sigue siendo inline en `SaludWEB_Web/src/components/Home/Home.jsx`, sin
  depender de una imagen externa; el `aria-label` describe al robot programando
  en una netbook.

### HOME estable con token vencido

- En `SaludWEB_Web/src/api/client.js` se agregó `esRutaPublica()`.
- Si una ruta pública (`/`, `/login`, `/register`, `/turnera`, etc.) recibe un
  `401`, se limpia la sesión vencida pero **no** se redirige al login.
- Las rutas protegidas conservan la redirección a `/login`.

### Paso a paso

1. Revisar `RobotFlyer` dentro de `Home.jsx`.
2. Reemplazar la laptop por la netbook y ajustar brazos, manos, teclado y
   pantalla.
3. Actualizar el `aria-label` y los comentarios del componente.
4. Revisar `client.js` y separar rutas públicas de protegidas en el interceptor
   401.
5. Ejecutar `npm test` y `npm run build` en `SaludWEB_Web`.
6. Probar con Playwright la raíz `/` con token inválido y `/dashboard`.
7. Documentar en los READMEs y publicar por Pull Request.

### Verificación

- `npm test` en Web: **32/32 pruebas OK**.
- `npm run build` en Web: **OK, 140 módulos**.
- Playwright: `/` se mantiene en la HOME, limpia el token vencido y no muestra
  el login; `/dashboard` sigue redirigiendo a `/login`.
- El detalle completo queda en
  [`SaludWEB_Web/README.md`](https://github.com/ailenquaglino089-ui/SaludWEB_Web/blob/main/README.md).
- Publicación: [PR #69](https://github.com/ailenquaglino089-ui/SaludWEB_Web/pull/69),
  merge `0208692` en `main`.

## Iteración 16/11/2026: guardado de contraseña en el navegador

Se ajustó solo el formulario de login de la SPA web para que Chrome, Edge y otros
navegadores reconozcan las credenciales y ofrezcan guardarlas. **No se tocaron
usuarios, contraseñas, backend, base de datos ni app móvil.**

### Paso a paso

1. Revisar `SaludWEB_Web/src/components/Auth/Login.jsx`.
2. Agregar `autoComplete="on"` al `<form>`.
3. Agregar `name="email"` y `autoComplete="username"` al campo de email.
4. Agregar `name="password"` al campo de contraseña y conservar
   `autoComplete="current-password"`.
5. Verificar en Playwright que el formulario expone esos atributos y que el
   login de la administradora llega a `/dashboard`.
6. Ejecutar `npm test` (32/32) y `npm run build` (OK, 140 módulos).
7. Documentar y publicar por Pull Request.

El detalle completo queda en
[`SaludWEB_Web/README.md`](https://github.com/ailenquaglino089-ui/SaludWEB_Web/blob/main/README.md).
Publicación: [PR #70](https://github.com/ailenquaglino089-ui/SaludWEB_Web/pull/70),
merge `a3f389b` en `main`.

## Verificación

- `npm test` en Web: **32/32 pruebas OK**.
- `npm run build` en Web: **OK, 140 módulos**.
- Smoke test con Playwright en escritorio (1440×1000) y mobile (390×844): HOME
  pública, HOME con sesión, dashboard médico, Mi agenda y redirección del paciente.
- Verificación en la URL pública: flyer visible, anteojos violeta oscuro, mangas
  largas, collar luna y crédito de la integrante.
- Circuito completo contra la API local: reserva del paciente, visualización en la
  agenda del médico y cancelación posterior para dejar la base limpia.

## Publicación en GitHub

Los tres repositorios publican en `main`, que está protegida y exige pull request.
Los merges de esta iteración quedaron así:

| Repositorio | PR | Merge en `main` |
| --- | --- | --- |
| SaludWEB_Web (13-14/11/2026) | [#68](https://github.com/ailenquaglino089-ui/SaludWEB_Web/pull/68) | `09d78e1` |
| SaludWEB_Mobile (13-14/11/2026) | [#49](https://github.com/ailenquaglino089-ui/SaludWEB_Mobile/pull/49) | `9854e96` |
| SaludWEB_Backend (13-14/11/2026) | [#53](https://github.com/ailenquaglino089-ui/SaludWEB_Backend/pull/53) | `76f36a4` |
| SaludWEB_Web (16/11/2026, robot) | [#69](https://github.com/ailenquaglino089-ui/SaludWEB_Web/pull/69) | `0208692` |
| SaludWEB_Web (16/11/2026, login) | [#70](https://github.com/ailenquaglino089-ui/SaludWEB_Web/pull/70) | `a3f389b` |

Flujo recomendado para cada repositorio:

```bash
git status
git diff --check
# ejecutar las verificaciones que correspondan
git add <archivos revisados>
git commit -m "docs: describir el cambio"
git push origin HEAD
gh pr create --repo <usuario>/<repositorio> --base main --head <rama-de-trabajo> --title "..." --body "..."
gh pr merge --repo <usuario>/<repositorio> --merge
git fetch origin
git log --oneline -1 origin/main
```

Antes de publicar, revisar que no se suban `.env`, `storage/`, `vendor/`,
`node_modules/` ni `dist/`. El flujo detallado de cada repositorio está en su README.

## Cómo ejecutar el proyecto

Backend:

```bash
cd SaludWEB_Backend
composer install
composer run dev
```

Web:

```bash
cd SaludWEB_Web
npm install
npm run dev
```

Mobile:

```bash
cd SaludWEB_Mobile
npm install
npm start
```

La API queda en `http://127.0.0.1:8000`; la web en `http://localhost:5173/`.

## Estructura local

```text
Workspace_SaludWEB/
├── SaludWEB_Backend/    # repo independiente: API PHP
├── SaludWEB_Mobile/     # repo independiente: React Native + Expo
├── SaludWEB_Web/        # repo independiente: React + Vite
├── logo-canva/          # copia local del paquete de marca para Canva
├── CALIDAD_PROFESIONAL_SOFTWARE.md
└── README.md            # esta portada
```

Las carpetas `SaludWEB_Backend/`, `SaludWEB_Mobile/` y `SaludWEB_Web/` son
repositorios Git independientes y están excluidas de este repositorio para evitar
gitlinks. `logo-canva/` contiene una copia local del paquete que también está
publicado en `SaludWEB_Web/docs/brand/`.

## Créditos

Proyecto **SaludWEB PRO** — Programación IV.
Integrante: **Ailen Quaglino Fail**.
