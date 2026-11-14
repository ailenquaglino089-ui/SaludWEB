# SaludWEB

Workspace del sistema de gestión de salud **SaludWEB PRO**, dividido en tres
repositorios que comparten la misma API y la misma identidad visual.

## Repositorios

| Carpeta | GitHub | Contenido |
| --- | --- | --- |
| `SaludWEB_Web` | [SaludWEB_Web](https://github.com/ailenquaglino089-ui/SaludWEB_Web) | SPA en React + Vite, HOME pública, turnera y paneles por rol. |
| `SaludWEB_Backend` | [SaludWEB_Backend](https://github.com/ailenquaglino089-ui/SaludWEB_Backend) | API REST en PHP, JWT, turnos, recetas y estadísticas. |
| `SaludWEB_Mobile` | [SaludWEB_Mobile](https://github.com/ailenquaglino089-ui/SaludWEB_Mobile) | App en React Native + Expo que consume la misma API. |

## Iteración 13/11/2026

Esta iteración se concentró en la SPA web:

- La HOME pública ahora replica el flyer de presentación: fondo azul tecnológico,
  logo blanco, mujer de cabello largo y claro con flequillo recto, anteojos de
  armazón claro, remera roja de mangas largas y collar gris en forma de luna,
  cuatro funciones, CTA, firma **Ailen Quaglino Fail** y robot con laptop.
- `/` siempre muestra la HOME. Si la persona tiene sesión, los botones cambian a
  **Ir al panel** y **Ver turnera**.
- El Dashboard del médico suma **Pacientes que reservaron** y la pantalla
  **Mi agenda** en `/turnera/mi-agenda`.
- El código nuevo quedó comentado por bloques y los README de los tres repositorios
  documentan el alcance, la arquitectura y la verificación.

El backend y la app móvil **no cambiaron en esta iteración**. La agenda web reutiliza
los endpoints existentes de turnos y el backend sigue aplicando los permisos por rol.

## Demo rápida

Enlace público para compartir: <https://saludweb.taileeeb72.ts.net/>. Usar la raíz, sin
agregar `/login` ni `/register`, para que la otra persona vea primero la HOME.

1. Abrir la HOME en `/` y presentar el flyer.
2. Reservar un turno como paciente desde `/turnera`, eligiendo profesional, día y hora.
3. Entrar como médico y abrir **Mi agenda** para ver el mismo turno con fecha, hora y
   paciente.
4. Entrar como administrador para recorrer el panorama general del consultorio.

Credenciales de prueba:

- Paciente: `paciente@prueba.com` / `paciente123`
- Médico: `medico@prueba.com` / `medico123`

## Documentación

- [`SaludWEB_Web/README.md`](SaludWEB_Web/README.md): instalación, HOME, agenda médica,
  verificación y guion de demo.
- [`SaludWEB_Web/docs/PRESENTACION.md`](SaludWEB_Web/docs/PRESENTACION.md): guion completo
  para la defensa o presentación.
- [`SaludWEB_Backend/README.md`](SaludWEB_Backend/README.md): API, autenticación, turnos
  y pruebas.
- [`SaludWEB_Mobile/README.md`](SaludWEB_Mobile/README.md): app móvil, navegación y
  relación con la API.
- [`logo-canva/README.md`](logo-canva/README.md): paquete de marca y uso del logo en la
  HOME.
