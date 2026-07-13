# WP File Manager — Reglas para IAs

Este archivo contiene las reglas, validaciones y convenciones que toda IA debe seguir al programar en este proyecto.

---

## 1. Estandares de Codigo

### PHP
- **WordPress Coding Standards**: Sigue los estándares de codificación de WordPress para PHP.
- **PHP 7.0+**: No uses sintaxis moderna de PHP (nullsafe `?->`, named arguments, match, readonly properties, etc). El operador `??` (null coalescing) está permitido.
- **Nombrado**: Las clases usan prefijo `WPFM_` (ej: `WPFM_API`, `WPFM_USE_DATA_CONFIG`). Métodos y propiedades en `camelCase` o `UPPER_SNAKE` para constantes.
- **Sanitizacion**: Toda salida de datos debe escaparse. Usa `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` según contexto.
- **Nonces**: Todo formulario debe verificar nonce con `wp_verify_nonce()` o `check_admin_referer()`.
- **Capabilities**: Toda operación admin debe verificar `current_user_can('manage_options')`.

### JavaScript
- **ES5**: El plugin soporta WordPress 5.0+, usa ES5 (no arrow functions, no let/const, no template literals).
- **jQuery**: Usa `jQuery(function($){ ... })` para DOM ready.
- **Nombrado**: Funciones en `snake_case` con prefijo `wpfm_`.

### CSS
- **Prefijo**: Todas las clases CSS deben llevar prefijo `wpfm-`.
- **Especificidad**: Evita `!important`.

---

## 2. Arquitectura del Plugin

### Sistema de Archivos
- `index.php` → Plugin header y constantes globales. No agregues lógica aquí.
- `src/_.php` → Cargador maestro. Todo nuevo módulo debe ser require desde aquí.
- `src/api/` → API REST para gestión de archivos.
- `src/data/` → Capa de datos (wp_options CRUD).
- `src/page/` → Páginas del admin.

### Constantes
Usa las constantes definidas en `index.php`:
- `WPFM_KEY` para prefijos de opciones y slugs
- `WPFM_CONFIG` para la configuración global
- `WPFM_DIR` y `WPFM_URL` para rutas
- Nunca hardcodees strings como `'WPFM'` o `'WPFM_CONFIG'`

### wp_options
Toda opción global debe usar `WPFM_USE_DATA_BASE` o una subclase. No uses `add_option()`/`update_option()` directamente fuera de la capa data.

---

## 3. Validaciones de Seguridad

1. **Nunca** hardcodees API keys en el código. La API key se guarda en `WPFM_CONFIG['api_key']`.
2. **Siempre** sanitiza input: `$_POST`, `$_GET`, `$_REQUEST` deben pasar por `sanitize_text_field()`, `sanitize_file_name()`, `intval()`, etc.
3. **Siempre** verifica nonces en formularios del admin (`check_admin_referer('wpfm_*', 'wpfm_*_nonce')`).
4. **Siempre** valida capabilities: `current_user_can('manage_options')` antes de cualquier operación.
5. **Proteccion contra path traversal**: Verifica que los archivos no salgan del directorio `WPFM/` usando `realpath()` o `strpos()`.
6. **hash_equals()**: Usa `hash_equals()` para comparar API keys, nunca `===`.

---

## 4. Convenciones del Proyecto

### REST API
- Namespace: `WPFM_KEY` (`'WPFM'`).
- Endpoints:
  - `GET /WPFM/list` → Lista archivos
  - `GET /WPFM/get` → Info/descarga de archivo
  - `DELETE /WPFM/delete` → Elimina archivo
  - `POST /WPFM/upload` → Sube/reemplaza archivo
- Header de autenticación: `X-WPFM-Key`.
- Las API keys se configuran en la página admin "Configuración".

### AJAX
- No se usan AJAX endpoints, solo REST API.

### Hooks
- Acciones: `add_action('hook', 'callback', priority)`.
- Filtros: `add_filter('hook', 'callback', priority, args)`.
- No registres hooks en el scope global. Deben estar dentro de funciones o métodos.

### Logging
- Usa siempre `FWUSystemLog::add(WPFM_KEY, $message)` para errores.
- No uses `error_log()`, `var_dump()`, `print_r()` en producción.

---

## 5. Git Workflow

1. **Commits**: No hacer commits automáticos, solo dar sugerencias de commits.

---

## 6. Lo que NO debes hacer

- NO modifiques `index.php` (plugin header).
- NO elimines el prefijo `WPFM_` de ninguna clase/función.
- NO agregues dependencias npm/composer sin autorización explícita.
- NO edites archivos en `libs/` (vendor de Composer).
- NO uses sintaxis moderna de PHP (>=7.0).
- NO hardcodees URLs o paths — usa `WPFM_URL`, `WPFM_DIR`.
- NO añadas archivos nuevos sin require desde `src/_.php` o desde subcarpetas `src/*/_.php`.
- NO uses `error_log()`, `var_dump()`, `print_r()` — usa `FWUSystemLog::add()`.
