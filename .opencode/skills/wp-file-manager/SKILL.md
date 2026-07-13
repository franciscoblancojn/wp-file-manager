---
name: wp-file-manager
description: Guia para desarrollar y mantener el plugin WP File Manager de WordPress. Gestiona archivos en uploads/WPFM/ con panel admin y REST API con validacion de API Key.
---

## Que hace este plugin

WP File Manager permite **subir, eliminar y reemplazar archivos** dentro de la carpeta `wp-content/uploads/WPFM/`. Incluye:
- Panel de administracion en WordPress con tabs (Archivos, Configuracion)
- REST API con validacion de API Key para integracion con sistemas externos
- Auto-update via GitHub

## Estructura del proyecto

```
wp-file-manager/
├── index.php                  # Plugin header + constantes globales
├── src/
│   ├── _.php                  # Cargador maestro
│   ├── api/
│   │   ├── _.php              # Init REST API
│   │   └── files.php          # WPFM_API — Endpoints REST
│   ├── data/
│   │   ├── _.php              # Require modulos data
│   │   ├── base.php           # WPFM_USE_DATA_BASE — CRUD wp_options
│   │   └── config.php         # WPFM_USE_DATA_CONFIG — Config plugin
│   └── page/
│       ├── _.php              # Require modulos page
│       ├── add.php            # Registro menu admin
│       ├── page.php           # Layout principal con tabs
│       └── sections/
│           ├── files.php      # Tab "Archivos"
│           └── config.php     # Tab "Configuracion"
└── libs/                      # Composer vendor
```

## Constantes clave

| Constante | Valor | Uso |
|-----------|-------|-----|
| `WPFM_KEY` | `'WPFM'` | Prefijo de opciones, slugs, namespace REST |
| `WPFM_CONFIG` | `'WPFM_CONFIG'` | Opcion wp_options para configuracion |
| `WPFM_DIR` | `plugin_dir_path(__FILE__)` | Ruta absoluta del plugin |
| `WPFM_URL` | `plugin_dir_url(__FILE__)` | URL base del plugin |

## REST API

### Endpoints

| Metodo | Ruta | Parametros | Descripcion |
|--------|------|------------|-------------|
| `GET` | `/WPFM/list` | — | Lista archivos |
| `GET` | `/WPFM/get` | `?name=x` | Info/descarga de archivo |
| `DELETE` | `/WPFM/delete` | `?name=x` | Elimina archivo |
| `POST` | `/WPFM/upload` | multipart `file` + `name` | Sube/reemplaza archivo |

### Autenticacion

Header: `X-WPFM-Key` con `hash_equals()` contra `WPFM_CONFIG['api_key']`.

### Como registrar un endpoint REST

```php
register_rest_route(WPFM_KEY, '/endpoint', [
    'methods' => 'GET',
    'callback' => [self::class, 'handleEndpoint'],
    'permission_callback' => [self::class, 'checkPermission'],
]);
```

## Clases principales

### WPFM_API (`src/api/files.php`)
- `init()` — Registra hooks REST
- `registerRoutes()` — Registra endpoints
- `checkPermission($request)` — Valida API key
- `handleList($request)` — Lista archivos
- `handleGet($request)` — Info/descarga
- `handleDelete($request)` — Elimina archivo
- `handleUpload($request)` — Sube/reemplaza archivo
- `getUploadDir()` — Obtiene/crea directorio uploads/WPFM/
- `sanitizeFileName($name)` — Limpia nombre de archivo
- `isPathSafe($file_path, $base_dir)` — Valida path traversal

### WPFM_USE_DATA_BASE (`src/data/base.php`)
- `get()` — Retorna todos los datos
- `set($DATA)` — Guarda todos los datos
- `setField($key, $value)` — Guarda un campo
- `add($DATA)` — Merge de datos

### WPFM_USE_DATA_CONFIG (`src/data/config.php`)
- Extiende `WPFM_USE_DATA_BASE`
- `$KEY = WPFM_CONFIG`
- `generateApiKey()` — Genera API key aleatoria

## Utilidades de la libreria

El plugin usa `franciscoblancojn/wordpress_utils`:

- **FWUPage** — Layout con tabs: `FWUPage::render($pageKey, $title, $tags, $sectionsDir)`, `FWUPage::css()`, `FWUPage::js($pageKey)`, `FWUPage::tabs($tags, $defaultTag)`
- **FWUCollapse** — Colapsables: `FWUCollapse::render($title, $content, $open)`
- **FWUTooltip** — Tooltips: `FWUTooltip::render($title, $text)`
- **FWURespond** — Mensajes: `FWURespond::render($respond)` donde `$respond = ['status' => 'ok'|'error', 'message' => '...']`
- **FWUSystemLog** — Logging: `FWUSystemLog::add(WPFM_KEY, $message)`

## Patron para crear una seccion admin

1. Crear `src/page/sections/mi_seccion.php`
2. Agregar tab en `src/page/page.php` al array `$TAGS`
3. El archivo se carga automaticamente via `require`

## Seguridad

- Sanitizar todo input con `sanitize_text_field()`, `sanitize_file_name()`, `intval()`
- Verificar nonces en formularios: `check_admin_referer('wpfm_*', 'wpfm_*_nonce')`
- Validar capabilities: `current_user_can('manage_options')`
- Proteccion path traversal: `realpath()` o `strpos()` contra directorio base
- API key con `hash_equals()`, nunca `===`

## Directorio de archivos

- Path fisico: `wp_upload_dir()['basedir'] . '/WPFM/'`
- URL publica: `wp_upload_dir()['baseurl'] . '/WPFM/'`
- Crear directorio si no existe al listar o subir
