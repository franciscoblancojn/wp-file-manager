# WP File Manager

**Version:** 0.0.0 | **License:** GPLv2+

Plugin de WordPress para **subir, eliminar y reemplazar archivos** dentro de la carpeta `uploads/WPFM/`. Los archivos se gestionan desde una página en el administrador de WordPress y a través de una API REST con validación de API Key.

---

## Caracteristicas

- **Subir archivos** — Sube archivos a la carpeta `uploads/WPFM/` desde el admin o vía API.
- **Reemplazar archivos** — Si subes un archivo con un nombre existente, se reemplaza automáticamente.
- **Eliminar archivos** — Elimina archivos con confirmación desde el admin o vía API.
- **Listar archivos** — Visualiza todos los archivos con nombre, tamaño y fecha de modificación.
- **REST API** — API completa con validación de API Key para integración con sistemas externos.
- **Panel de administración** — Interfaz con tabs para gestionar archivos y configuración.
- **Auto-Update vía GitHub** — El plugin se actualiza automáticamente desde GitHub Releases.
- **Sistema de Logs** — Registro de actividad accesible desde la barra de administración.

---

## Requisitos

- WordPress 5.0+
- PHP 7.0+

---

## Instalacion

1. Descarga el plugin desde [GitHub](https://github.com/franciscoblancojn/wp-file-manager/archive/refs/heads/master.zip).
2. Súbelo y actívalo desde el menú **Plugins** de WordPress.
3. Ve a **File Manager → Configuración** para configurar la API Key.
4. ¡Listo! Comienza a gestionar archivos.

---

## Estructura del Plugin

```
wp-file-manager/
├── index.php                     # Archivo principal (plugin header, constantes, updater)
├── composer.json                 # Dependencias Composer
├── package.json                  # Scripts de release/versionado
├── libs/                         # Dependencias (Composer vendor renombrado)
└── src/
    ├── _.php                     # Cargador maestro
    ├── api/
    │   ├── _.php                 # Require + init REST API
    │   └── files.php             # WPFM_API — Endpoints REST para archivos
    ├── data/
    │   ├── _.php                 # Require módulos data
    │   ├── base.php              # WPFM_USE_DATA_BASE — CRUD genérico wp_options
    │   └── config.php            # WPFM_USE_DATA_CONFIG — Configuración del plugin
    └── page/
        ├── _.php                 # Require módulos page
        ├── add.php               # Registro menú admin
        ├── page.php              # Layout principal con tabs
        └── sections/
            ├── files.php         # Tab "Archivos" — Listado, subir, eliminar
            └── config.php        # Tab "Configuración" — API Key, toggle API
```

---

## Clases Principales

| Clase | Archivo | Funcion |
|-------|---------|---------|
| `WPFM_API` | `src/api/files.php` | API REST para gestión de archivos (list, get, delete, upload) |
| `WPFM_USE_DATA_BASE` | `src/data/base.php` | CRUD genérico basado en `wp_options` |
| `WPFM_USE_DATA_CONFIG` | `src/data/config.php` | Configuración del plugin (API key, toggle) |

---

## REST API

El plugin expone endpoints REST para gestionar archivos desde sistemas externos.

### Endpoints

| Método | Ruta | Parámetros | Descripción |
|--------|------|------------|-------------|
| `GET` | `/wp-json/WPFM/list` | — | Lista todos los archivos con nombre, tamaño, fecha |
| `GET` | `/wp-json/WPFM/get` | `?name=x` | Info del archivo. `?name=x&download=1` para descargar |
| `DELETE` | `/wp-json/WPFM/delete` | `?name=x` | Elimina un archivo |
| `POST` | `/wp-json/WPFM/upload` | multipart `file` + `name` (opcional) | Sube o reemplaza un archivo |
| `POST` | `/wp-json/WPFM/upload/base64` | JSON: `name`, `file` (base64), `mimetype` (opcional) | Sube o reemplaza un archivo vía base64 |

### Autenticacion

Las peticiones deben incluir el header `X-WPFM-Key` con la API Key configurada.

```bash
# Listar archivos
curl -H "X-WPFM-Key: wpfm_xxxxx" https://tusitio.com/wp-json/WPFM/list

# Obtener info de un archivo
curl -H "X-WPFM-Key: wpfm_xxxxx" "https://tusitio.com/wp-json/WPFM/get?name=archivo.pdf"

# Eliminar un archivo
curl -X DELETE -H "X-WPFM-Key: wpfm_xxxxx" "https://tusitio.com/wp-json/WPFM/delete?name=archivo.pdf"

# Subir un archivo
curl -X POST -H "X-WPFM-Key: wpfm_xxxxx" -F "file=@archivo.pdf" https://tusitio.com/wp-json/WPFM/upload

# Subir/reemplazar con nombre personalizado
curl -X POST -H "X-WPFM-Key: wpfm_xxxxx" -F "file=@archivo.pdf" -F "name=mi-archivo.pdf" https://tusitio.com/wp-json/WPFM/upload

# Subir archivo vía base64
curl -X POST -H "X-WPFM-Key: wpfm_xxxxx" -H "Content-Type: application/json" \
  -d '{"name":"archivo.pdf","file":"BASE64_AQUI","mimetype":"application/pdf"}' \
  https://tusitio.com/wp-json/WPFM/upload/base64
```

### Configuracion

La API se gestiona desde **File Manager → Configuración**:
- **API Habilitada** — Activa/desactiva la API REST.
- **API Key** — Clave para autenticación (generada automáticamente, modificable).
- **Endpoint URL** — URL base de la API.

---

## Paginas del Admin

| Menu | Slug | Descripcion |
|------|------|-------------|
| **Archivos** | `WPFM_files` | Listado de archivos, subir, eliminar, descargar |
| **Configuracion** | `WPFM_config` | API Key, activar/desactivar API, endpoints |

---

## Hooks

### Acciones
- `admin_menu` — Registro de menús y submenús.
- `rest_api_init` — Registro de endpoints REST.

---

## Seguridad

- **API Key** — Generada automáticamente al instalar el plugin, modificable desde configuración.
- **Header de autenticación** — `X-WPFM-Key` con `hash_equals()` para comparación segura.
- **Sanitizacion** — `sanitize_file_name()` para nombres de archivo, `sanitize_text_field()` para input.
- **Path traversal** — Verificación de que los archivos no salgan del directorio `WPFM/`.
- **Capabilities** — `manage_options` requerido para todas las operaciones.
- **Nonces** — Verificación en todos los formularios del admin.

---

## Constantes Globales

| Constante | Valor | Proposito |
|-----------|-------|-----------|
| `WPFM_KEY` | `'WPFM'` | Prefijo de opciones y slugs |
| `WPFM_CONFIG` | `'WPFM_CONFIG'` | Opción de configuración del plugin |
| `WPFM_DIR` | `plugin_dir_path(__FILE__)` | Ruta absoluta del plugin |
| `WPFM_URL` | `plugin_dir_url(__FILE__)` | URL base del plugin |
| `WPFM_BASENAME` | `plugin_basename(__FILE__)` | Base name del plugin |
| `WPFM_LOG` | `true` | Habilita logs del plugin |
| `WPFM_LOG_KEY` | `'WPFM_LOG'` | Clave para opción de logs |
| `WPFM_LOG_COUNT` | `100` | Máximo de entradas de log |

---

## Opciones de WordPress (wp_options)

| Option Key | Clase | Proposito |
|------------|-------|-----------|
| `WPFM_CONFIG` | `WPFM_USE_DATA_CONFIG` | Config global: api_key, api_enabled |

---

## Licencia

GPLv2+ — Ver [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) para más detalles.

---

## Developer

- **Name:** Francisco Blanco
- **Website:** https://franciscoblanco.vercel.app/
- **Email:** blancofrancisco34@gmail.com
