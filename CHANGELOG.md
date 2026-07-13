# Changelog

> Todas las versiones del plugin WP File Manager.

---

## [1.0.0] — 2025-07-12

- **Nuevo**: Panel de administración con tabs (Archivos, Configuración)
- **Nuevo**: Gestión de archivos en `uploads/WPFM/` (subir, eliminar, reemplazar, listar, descargar)
- **Nuevo**: REST API con validación de API Key
  - `GET /WPFM/list` — Listar archivos
  - `GET /WPFM/get?name=x` — Info/descarga de archivo
  - `DELETE /WPFM/delete?name=x` — Eliminar archivo
  - `POST /WPFM/upload` — Subir/reemplazar archivo (multipart)
  - `POST /WPFM/upload/base64` — Subir/reemplazar archivo vía base64
- **Nuevo**: Tab "API" visible solo con API habilitada, con ejemplos de código (PHP, cURL, JS Fetch) en collapses
- **Nuevo**: Generación automática de API Key al instalar el plugin
- **Nuevo**: Toggle para habilitar/deshabilitar la API REST
- **Nuevo**: Protección contra path traversal en operaciones de archivos
- **Nuevo**: Logging de actividad vía `FWUSystemLog`
- **Nuevo**: Auto-update vía GitHub
- **Nuevo**: README.md, AGENTS.md, SKILL.md para documentación y asistentes IA
- **Nuevo**: CHANGELOG.md
