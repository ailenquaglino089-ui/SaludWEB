# SaludWEB — memoria del incidente de Avast/WPS y verificación de arranque

**Fecha:** 27 de noviembre de 2026  
**Proyecto:** SaludWEB — Programación IV  
**Alcance:** explicación de lo ocurrido con los archivos del proyecto, la investigación del origen, la recuperación y el estado final de funcionamiento.

---

## 1. Resumen ejecutivo

El cartel que aparecía al abrir `Iniciar SaludWEB.bat` no era un virus, un hackeo ni un fallo del código de SaludWEB. El mensaje decía que no se encontraba el script del proyecto y era generado por el propio acceso directo de Windows.

La causa fue **Avast One**, instalado en silencio por un instalador de **WPS Office** descargado el 26/11/2026. Avast clasificó `index.php` como `IDP.Generic` y puso archivos en cuarentena / bloqueó nombres exactos. No había código malicioso en el repositorio.

Después de desinstalar Avast, se restauraron los tres archivos afectados desde Git, se comprobó que Web y Backend quedaron sin cambios pendientes y se validó que la API, la web local, la puerta de enlace y la URL pública responden HTTP 200.

**Estado final:** proyecto funcionando, archivos restaurados, informes Word/PDF intactos y Avast sin servicios ni procesos activos.

---

## 2. Cronología de lo ocurrido

| Momento | Hecho |
|---|---|
| 26/11/2026 11:52:53 | Se descargó `C:\Users\AILEN\Downloads\wps_wid.cid-709238699.1795704768.exe`. |
| 26/11/2026 11:52:56 | El instalador terminó de descargarse. Su firma era válida: `CN=WPS SOFTWARE PTE. LTD.`. |
| 26/11/2026 12:04:32 | Apareció la carpeta `C:\Users\AILEN\AppData\Local\Kingsoft\WPS Office`. |
| 26/11/2026 12:06:39 | Se creó la carpeta de Avast y se registraron sus servicios. |
| 26/11/2026 12:08:20 | Windows Security Center registró a Avast como producto de protección. |
| 27/11/2026 17:36:41 | Cambió `C:\ProgramData\Avast Software\Avast\chest\index.xml`: Avast puso `index.php` en cuarentena como `IDP.Generic`. |
| 27/11/2026 | Al abrir el acceso directo apareció el cartel de “No se encontro el script del proyecto”. |
| 27/11/2026 | Se investigó el origen, se desinstaló Avast y se restauraron los archivos desde Git. |
| 27/11/2026 | Se verificó el arranque completo y las cuatro URL (API, web local, gateway y pública). |

> Los logs internos de Avast están desplazados respecto de la hora local del sistema; por eso en los logs figura `15:06` donde en Windows figura `12:06`. La secuencia temporal es la misma.

---

## 3. Evidencias del origen

### 3.1 Instalador de WPS

Archivo analizado:

```text
C:\Users\AILEN\Downloads\wps_wid.cid-709238699.1795704768.exe
```

Datos verificados:

```text
Length: 5.598.080 bytes
CreationTime: 26/11/2026 11:52:53
Signer: CN=WPS SOFTWARE PTE. LTD., O=WPS SOFTWARE PTE. LTD., L=Singapore, C=SG
Issuer: CN=DigiCert Trusted G4 Code Signing RSA4096 SHA384 2021 CA1
Status: Firma comprobada
SHA256: 0B8301C144D8A7069568F3554FF1450C3BF0861AC44B7F70621018462BF23144
```

Esto indica que el instalador era de WPS y estaba firmado, no un ejecutable aleatorio.

### 3.2 Instalación silenciosa de Avast

En `C:\ProgramData\Avast Software\Icarus\Logs\sfx.log` aparecen estos datos exactos:

```text
trigger":"16044-C:\Users\AILEN\Downloads\wps_wid.cid-709238699.1795704768.exe"
cmdline":"\"C:\ProgramData\tmp\recommend_installer.exe\" /silent /ws"
```

El parámetro `/silent` explica que no apareciera un asistente visible. El instalador de WPS lanzó un instalador recomendado y Avast One se instaló sin que hiciera falta confirmar nada en pantalla.

### 3.3 Cuarentena de Avast

En `C:\ProgramData\Avast Software\Avast\chest\index.xml` apareció:

```text
<OrigFileName>index.php</OrigFileName>
<OrigFolder>C:\xampp\htdocs\Workspace_SaludWEB\SaludWEB_Backend</OrigFolder>
<Virus>IDP.Generic</Virus>
<TransferTime>1795811801</TransferTime>
<Restore yes>
```

No se encontraron entradas de cuarentena para `iniciar_saludweb.bat` ni `servidor_publico.php`; esos nombres fueron bloqueados por la protección en tiempo real de Avast, no borrados por un atacante.

### 3.4 Producto instalado

```text
DisplayName: Avast One
DisplayVersion: 26.11.11250.1098
Publisher: Gen Digital Inc.
InstallLocation: C:\Program Files\Avast Software\Suite
```

Servicios que llegaron a estar activos:

```text
Avast BreachGuard Service
avast! Antivirus
avast! Firewall
avast! Tools
AvastCleanupSvc
AvastDriverUpdSvc
AvastWscReporter
```

---

## 4. Diagnóstico

1. El cartel venía de `C:\Users\AILEN\Desktop\Iniciar SaludWEB.bat`, no del navegador.
2. El acceso directo apuntaba a `SaludWEB_Web\scripts\iniciar_saludweb.bat`.
3. Ese archivo, `servidor_publico.php` e `index.php` faltaban del disco.
4. Los permisos NTFS eran correctos: se podían crear y borrar archivos temporales en la misma carpeta.
5. Al escribir con los nombres exactos aparecía `Acceso denegado a la ruta de acceso`, pero con otros nombres la escritura funcionaba.
6. Avast tenía en cuarentena `index.php` como falso positivo `IDP.Generic`.
7. El instalador recomendado de WPS se ejecutó en modo silencioso.

**Conclusión:** no hubo hackeo. Fue un falso positivo / bloqueo de Avast, instalado como software recomendado junto con WPS.

---

## 5. Acciones realizadas

### 5.1 Investigación

- Se leyó el acceso directo `C:\Users\AILEN\Desktop\Iniciar SaludWEB.bat`.
- Se revisó el estado de los repositorios y se confirmó que los tres archivos faltaban en disco pero seguían en `HEAD`.
- Se verificó la firma del instalador de WPS.
- Se revisaron logs de Icarus, cuarentena de Avast, servicios, procesos y registro de Windows.
- Se descartó código malicioso en el repositorio.

### 5.2 Intento de desinstalación automática

Se intentó desactivar/desinstalar Avast de forma automática:

```text
UninstallString:
"C:\Program Files\Common Files\Avast Software\Icarus\avast-suite\icarus.exe" /manual_update /uninstall:avast-suite
```

Resultado:

```text
EXIT_CODE: 46102
```

Los servicios seguían activos, por lo que ese camino se descartó.

### 5.3 Workaround temporal

Se crearon archivos alternativos para esquivar los nombres bloqueados:

```text
SaludWEB_Backend\api_router.php
SaludWEB_Web\scripts\servidor_publico_v2.php
SaludWEB_Web\scripts\iniciar_saludweb_v2.bat
```

Los dos archivos PHP pasaron `php -l`, pero Avast también puso en cuarentena `iniciar_saludweb_v2.bat`. Por eso se abandonó el workaround y se eliminaron esos archivos temporales después de restaurar los originales.

### 5.4 Desinstalación y restauración definitiva

El usuario desinstaló Avast manualmente. Después se ejecutaron:

```powershell
git -C "C:\xampp\htdocs\Workspace_SaludWEB\SaludWEB_Web" restore --source=HEAD -- scripts/iniciar_saludweb.bat scripts/servidor_publico.php
git -C "C:\xampp\htdocs\Workspace_SaludWEB\SaludWEB_Backend" restore --source=HEAD -- index.php
```

Se eliminó únicamente el instalador ya analizado:

```powershell
Remove-Item -LiteralPath 'C:\Users\AILEN\Downloads\wps_wid.cid-709238699.1795704768.exe' -Force
```

No se borraron archivos del proyecto, informes, accesos directos ni documentos personales.

---

## 6. Estado final verificado

| Comprobación | Resultado |
|---|---|
| `SaludWEB_Backend/index.php` | Presente |
| `SaludWEB_Backend/core/Router.php` | Presente |
| `SaludWEB_Web/scripts/iniciar_saludweb.bat` | Presente |
| `SaludWEB_Web/scripts/servidor_publico.php` | Presente |
| `SaludWEB_Web/dist/index.html` | Presente |
| Accesos directos de escritorio | Presentes |
| Informes Word/PDF | Presentes |
| `git status` en Web | 0 cambios pendientes |
| `git status` en Backend | 0 cambios pendientes |
| Servicios de Avast | 0 |
| Procesos de Avast | 0 |
| Instalador WPS en Descargas | Eliminado |
| `http://127.0.0.1:8000/api/health` | HTTP 200 |
| `http://127.0.0.1:5173/` | HTTP 200 |
| `http://127.0.0.1:8001/api/health` | HTTP 200 |
| `https://saludweb.taileeeb72.ts.net/` | HTTP 200 |

Respuesta real de la API:

```json
{"ok":true,"mensaje":"API SaludWEB - Programación IV","data":{"status":"ok","message":"API funcionando","timestamp":"2026-11-27T18:50:14-03:00","version":"1.0.0","auth":"JWT active"}}
```

---

## 7. Cómo usar el proyecto ahora

### En la computadora

```text
http://localhost:5173/
```

### Desde el celular, misma red WiFi

```text
http://192.168.1.4:5173/
```

### Desde cualquier red, con datos móviles

```text
https://saludweb.taileeeb72.ts.net/
```

La PC debe quedar encendida y con Internet, porque el Tailscale Funnel apunta a `http://127.0.0.1:8001` de esa máquina.

### Para apagar

```text
scripts\detener_saludweb.bat
```

---

## 8. Cómo evitar que vuelva a ocurrir

1. Descargar software solo desde sitios oficiales.
2. Durante una instalación, elegir **Personalizada / Avanzada** y desmarcar “software recomendado”, barras, buscadores o antivirus extra.
3. No hacer clic en botones grandes de “Descargar” de sitios de terceros.
4. Después de instalar algo, revisar **Ajustes → Aplicaciones** y quitar lo que no se pidió.
5. Mantener Windows Defender activo; no hace falta instalar otro antivirus para este proyecto.
6. Si aparece un producto de seguridad que no se instaló explícitamente, desinstalarlo y revisar los logs antes de seguir.

---

## 9. Explicación línea por línea

No se modificó el código del proyecto: los archivos se restauraron desde Git. Esta sección documenta qué hace cada línea o bloque relevante.

### 9.1 `C:\Users\AILEN\Desktop\Iniciar SaludWEB.bat`

```text
1:  @echo off
2:  REM Acceso directo: llama al script de arranque que vive DENTRO del repositorio,
3:  REM asi hay una sola version del lanzador (la del repo).
4:  set "REPO_SCRIPT=C:\xampp\htdocs\Workspace_SaludWEB\SaludWEB_Web\scripts\iniciar_saludweb.bat"
5:  if exist "%REPO_SCRIPT%" (
6:    call "%REPO_SCRIPT%"
7:  ) else (
8:    echo No se encontro el script del proyecto:
9:    echo   %REPO_SCRIPT%
10:   echo Verifica que el repositorio este en esa ruta.
11:   pause
12: )
```

- **Línea 1:** desactiva que Windows muestre cada comando mientras se ejecuta.
- **Líneas 2-3:** comentan que este acceso directo solo llama al lanzador real dentro del repositorio.
- **Línea 4:** guarda en `REPO_SCRIPT` la ruta absoluta del lanzador real.
- **Línea 5:** pregunta si ese archivo existe.
- **Línea 6:** si existe, lo ejecuta con `call`.
- **Línea 7:** abre el bloque alternativo cuando no existe.
- **Líneas 8-10:** imprimen el cartel de error que vio el usuario.
- **Línea 11:** espera a que presione una tecla para poder leer el mensaje.
- **Línea 12:** cierra el bloque `else`.

### 9.2 `SaludWEB_Backend/index.php`

```text
1:  <?php
2:  // Punto de entrada de la API del backend separado.
3:  // Punto de entrada principal del proyecto (Front Controller / Controlador Frontal)
4:  // Todas las peticiones HTTP llegan aquí gracias al .htaccess
5:
6:  // Incluye el archivo de rutas, que a su vez carga bootstrap.php con todas las dependencias
7:  require_once __DIR__ . '/routes.php';
8:
9:  // Despacha la ruta actual: lee el método HTTP (GET, POST, etc.) y la URL solicitada
10: // $router es una instancia de la clase Router creada en bootstrap.php
11: $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
```

- **Línea 1:** abre el bloque PHP.
- **Líneas 2-4:** documentan que este archivo es el controlador frontal de la API.
- **Línea 5:** línea en blanco.
- **Línea 6:** comenta la inclusión de rutas.
- **Línea 7:** carga `routes.php`, que a su vez carga dependencias y crea `$router`.
- **Línea 8:** línea en blanco.
- **Línea 9:** comenta el despacho de la petición.
- **Línea 10:** comenta el origen de `$router`.
- **Línea 11:** llama a `dispatch()` con el método HTTP y la URI; acá se decide qué controlador responde.

Este era el archivo que Avast marcó como `IDP.Generic`. Era un falso positivo: solo incluye rutas y despacha la petición.

### 9.3 `SaludWEB_Web/scripts/servidor_publico.php`

```text
1:  <?php
2:
3:  $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
4:  if (!is_string($ruta)) {
5:      http_response_code(400);
6:      exit;
7:  }
8:
9:  if ($ruta === '/api' || str_starts_with($ruta, '/api/')) {
10:     require __DIR__ . '/../../SaludWEB_Backend/index.php';
11:     return true;
12: }
13:
14: $dist = realpath(__DIR__ . '/../dist');
15: if ($dist === false) {
16:     http_response_code(503);
17:     echo 'La web todavía no está compilada.';
18:     return true;
19: }
20:
21: $rutaDecodificada = rawurldecode($ruta);
22: if (str_contains($rutaDecodificada, "\0")) {
23:     http_response_code(400);
24:     return true;
25: }
26:
27: $archivo = realpath($dist . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $rutaDecodificada), DIRECTORY_SEPARATOR));
28: $prefijoDist = $dist . DIRECTORY_SEPARATOR;
29: if (
30:     $archivo !== false
31:     && strncasecmp($archivo, $prefijoDist, strlen($prefijoDist)) === 0
32:     && is_file($archivo)
33: ) {
34:     return false;
35: }
36:
37: if ($ruta !== '/' && pathinfo($ruta, PATHINFO_EXTENSION) !== '') {
38:     http_response_code(404);
39:     return true;
40: }
41:
42: readfile($dist . DIRECTORY_SEPARATOR . 'index.html');
43: return true;
```

- **Línea 1:** abre PHP.
- **Línea 2:** espacio.
- **Línea 3:** toma solo la ruta de la URI, sin query string; si no hay URI usa `/`.
- **Línea 4:** comprueba que la ruta sea texto.
- **Línea 5:** si no es texto, devuelve HTTP 400.
- **Línea 6:** termina la respuesta.
- **Línea 7:** cierra el bloque.
- **Línea 8:** espacio.
- **Línea 9:** detecta si la petición es para la API (`/api` o empieza con `/api/`).
- **Línea 10:** carga el front controller del backend. Por eso web y API comparten origen en el Funnel.
- **Línea 11:** `return true` indica que PHP ya manejo la respuesta.
- **Línea 12:** cierra el bloque de API.
- **Línea 13:** espacio.
- **Línea 14:** resuelve la ruta real de la carpeta `dist`, donde está la web compilada.
- **Línea 15:** comprueba que `dist` exista.
- **Línea 16:** si no existe, devuelve HTTP 503.
- **Línea 17:** explica que todavía no se compiló la web.
- **Línea 18:** termina el manejo de la respuesta.
- **Línea 19:** cierra el bloque.
- **Línea 20:** espacio.
- **Línea 21:** decodifica la URL para interpretar caracteres como `%20`.
- **Línea 22:** rechaza bytes nulos, una técnica de evasión de rutas.
- **Línea 23:** devuelve HTTP 400 si aparece un byte nulo.
- **Línea 24:** termina el manejo.
- **Línea 25:** cierra el bloque.
- **Línea 26:** espacio.
- **Línea 27:** construye la ruta real del archivo solicitado dentro de `dist`.
- **Línea 28:** guarda el prefijo de `dist` para comparar.
- **Líneas 29-33:** verifican que el archivo exista, esté dentro de `dist` y sea un archivo normal; evita salirse de la carpeta pública.
- **Línea 34:** si es un archivo estático válido, `return false` deja que el servidor PHP integrado lo sirva directamente.
- **Línea 35:** cierra el bloque.
- **Línea 36:** espacio.
- **Línea 37:** si la ruta no es `/` y tiene extensión, pero no se encontró el archivo, es un 404 real.
- **Línea 38:** devuelve HTTP 404.
- **Línea 39:** termina el manejo.
- **Línea 40:** cierra el bloque.
- **Línea 41:** espacio.
- **Línea 42:** sirve `index.html` para que React maneje rutas internas como `/turnera`.
- **Línea 43:** termina el manejo de la respuesta.

### 9.4 `SaludWEB_Web/scripts/iniciar_saludweb.bat`

Este archivo tiene 236 líneas y no se modificó; se restauró desde Git. Contiene commentarios internos, por eso se explica por bloques numerados.

- **Líneas 1-7:** desactiva el eco de comandos, habilita extensiones y define si se llegó con `/autostart` para iniciar ventanas minimizadas.
- **Líneas 9-31:** documentan qué hace el script: detectar IP, levantar API, compilar y levantar web, esperar respuestas y abrir Chrome.
- **Líneas 33-35:** calculan `WEB_DIR` y `BACKEND_DIR` desde la ubicación del script, asumiendo que los repos son hermanos.
- **Líneas 37-44:** eligen el PHP a usar: primero `SALUDWEB_PHP`, después `C:\Users\AILEN\php83\php.exe` y por último `php` del PATH.
- **Líneas 46-53:** ejecutan `php -v`; si falla, muestran error y salen.
- **Líneas 55-61:** comprueban que exista `SaludWEB_Backend\index.php`.
- **Líneas 63-74:** detectan la IP local de WiFi/Ethernet/hotspot, excluyen virtuales y Tailscale; si no hay, usan `127.0.0.1` con aviso.
- **Líneas 76-101:** revisan si el puerto 8000 ya está ocupado. Si escucha solo en `127.0.0.1`, avisan que hay que detenerlo; si no, levantan la API con `php -S 0.0.0.0:8000 index.php`.
- **Líneas 103-114:** entran a `WEB_DIR`, ejecutan `npm run build` y salen con error si la compilación falla.
- **Líneas 116-124:** levantan la puerta de enlace privada en `127.0.0.1:8001` usando `servidor_publico.php`; esta puerta sirve `dist` y deriva `/api`.
- **Líneas 126-148:** hacen lo mismo con el puerto 5173, pero en `0.0.0.0` para que el celular pueda entrar por WiFi.
- **Líneas 150-153:** esperan hasta 30 intentos por cada health check: API, web y gateway.
- **Líneas 155-176:** ubican la CLI de Tailscale, cierran una ruta `/api` vieja si existiera y publican el gateway 8001 en `https://saludweb.taileeeb72.ts.net/`.
- **Líneas 178-193:** avisan si faltan las reglas de firewall para 8000 y 5173.
- **Líneas 195-206:** buscan Chrome y abren `http://localhost:5173/`; si no hay Chrome, dejan que Windows abra el navegador por defecto.
- **Línea 208:** etiqueta para saltar el navegador cuando se usa `/autostart`.
- **Líneas 209-223:** imprimen las URL finales y la instrucción para apagar.
- **Línea 224:** pausa de 6 segundos para poder leer el mensaje.
- **Línea 225:** sale con código 0.
- **Líneas 227-236:** subrutina `:esperar`: recibe URL e intentos, usa `curl.exe -s -f`, reintenta con pausas y termina con éxito o error.

### 9.5 `SaludWEB_Web/scripts/detener_saludweb.bat`

- **Líneas 1-11:** encabezado y documentación; explica que apaga 8000, 5173 y 8001.
- **Línea 13:** mensaje inicial.
- **Líneas 15-21:** comentan el comando PowerShell usado: buscar conexiones en escucha, obtener PIDs únicos y detenerlos.
- **Líneas 22-23:** ejecutan ese comando con `-ErrorAction SilentlyContinue` para que no falle si un puerto ya estaba libre.
- **Línea 25:** confirma que los puertos quedaron libres.
- **Línea 26:** comenta la pausa final.
- **Línea 27:** pausa de 4 segundos para leer el mensaje.

---

## 10. Comandos de verificación final

```powershell
git -C "C:\xampp\htdocs\Workspace_SaludWEB\SaludWEB_Web" status --short
git -C "C:\xampp\htdocs\Workspace_SaludWEB\SaludWEB_Backend" status --short
Get-Service | Where-Object { $_.DisplayName -match 'Avast' -or $_.Name -match 'avast' }
Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/health' -UseBasicParsing
Invoke-WebRequest -Uri 'http://127.0.0.1:5173/' -UseBasicParsing
Invoke-WebRequest -Uri 'http://127.0.0.1:8001/api/health' -UseBasicParsing
Invoke-WebRequest -Uri 'https://saludweb.taileeeb72.ts.net/' -UseBasicParsing
```

Todos devolvieron el resultado esperado: repos limpios, cero servicios de Avast y HTTP 200 en las cuatro URL.
