# SaludWEB

Sistema de gestión de salud **SaludWEB PRO**. El proyecto está dividido en tres
repositorios que comparten la misma API, la misma identidad visual y el mismo flujo
de turnos: web, backend y mobile.

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
  cuatro funciones, CTA, firma **Ailen Quaglino Fail** y robot con laptop.
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
| SaludWEB_Web | [#68](https://github.com/ailenquaglino089-ui/SaludWEB_Web/pull/68) | `09d78e1` |
| SaludWEB_Mobile | [#49](https://github.com/ailenquaglino089-ui/SaludWEB_Mobile/pull/49) | `9854e96` |
| SaludWEB_Backend | [#53](https://github.com/ailenquaglino089-ui/SaludWEB_Backend/pull/53) | `76f36a4` |

Flujo recomendado para cada repositorio:

```bash
git status
git diff --check
# ejecutar las verificaciones que correspondan
git add <archivos revisados>
git commit -m "docs: describir el cambio"
git push origin HEAD
gh pr create --repo <usuario>/<repositorio> --base main --head docs/access-connectivity --title "..." --body "..."
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
