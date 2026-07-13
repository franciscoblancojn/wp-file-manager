<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUCollapse;
use franciscoblancojn\wordpress_utils\FWUTooltip;

$api_url = trailingslashit(rest_url(WPFM_KEY));
$api_key = $CONFIG['api_key'] ?? '';

$endpoints = [
    [
        'method' => 'GET',
        'path' => '/list',
        'title' => 'Listar archivos',
        'desc' => 'Retorna la lista de todos los archivos en la carpeta uploads/WPFM/.',
        'params' => '',
        'response' => '{
  "success": true,
  "data": [
    {
      "name": "archivo.pdf",
      "size": 102400,
      "size_human": "100 KB",
      "modified": "2025-01-15 10:30:00",
      "url": "https://tusitio.com/wp-content/uploads/WPFM/archivo.pdf",
      "mime": "application/pdf"
    }
  ]
}',
        'php' => '<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "' . esc_url($api_url) . 'list");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-WPFM-Key: ' . esc_js($api_key) . '"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
print_r($data);',
        'curl' => 'curl -H "X-WPFM-Key: ' . esc_js($api_key) . '" ' . esc_url($api_url) . 'list',
        'js' => 'const response = await fetch("' . esc_js($api_url) . 'list", {
    headers: {
        "X-WPFM-Key": "' . esc_js($api_key) . '"
    }
});
const data = await response.json();
console.log(data);',
    ],
    [
        'method' => 'GET',
        'path' => '/get',
        'title' => 'Obtener archivo',
        'desc' => 'Retorna la información de un archivo. Agrega <code>?download=1</code> para descargarlo.',
        'params' => '?name=archivo.pdf',
        'response' => '{
  "success": true,
  "data": {
    "name": "archivo.pdf",
    "size": 102400,
    "size_human": "100 KB",
    "modified": "2025-01-15 10:30:00",
    "url": "https://tusitio.com/wp-content/uploads/WPFM/archivo.pdf",
    "mime": "application/pdf"
  }
}',
        'php' => '<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "' . esc_url($api_url) . 'get?name=archivo.pdf");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-WPFM-Key: ' . esc_js($api_key) . '"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
print_r($data);',
        'curl' => 'curl -H "X-WPFM-Key: ' . esc_js($api_key) . '" "' . esc_url($api_url) . 'get?name=archivo.pdf"',
        'js' => 'const response = await fetch("' . esc_js($api_url) . 'get?name=archivo.pdf", {
    headers: {
        "X-WPFM-Key": "' . esc_js($api_key) . '"
    }
});
const data = await response.json();
console.log(data);',
    ],
    [
        'method' => 'DELETE',
        'path' => '/delete',
        'title' => 'Eliminar archivo',
        'desc' => 'Elimina un archivo de la carpeta uploads/WPFM/.',
        'params' => '?name=archivo.pdf',
        'response' => '{
  "success": true,
  "message": "Archivo eliminado correctamente.",
  "data": {
    "name": "archivo.pdf"
  }
}',
        'php' => '<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "' . esc_url($api_url) . 'delete?name=archivo.pdf");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-WPFM-Key: ' . esc_js($api_key) . '"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
print_r($data);',
        'curl' => 'curl -X DELETE -H "X-WPFM-Key: ' . esc_js($api_key) . '" "' . esc_url($api_url) . 'delete?name=archivo.pdf"',
        'js' => 'const response = await fetch("' . esc_js($api_url) . 'delete?name=archivo.pdf", {
    method: "DELETE",
    headers: {
        "X-WPFM-Key": "' . esc_js($api_key) . '"
    }
});
const data = await response.json();
console.log(data);',
    ],
    [
        'method' => 'POST',
        'path' => '/upload',
        'title' => 'Subir / Reemplazar archivo',
        'desc' => 'Sube un archivo. Si ya existe uno con el mismo nombre, se reemplaza. Envía <code>name</code> opcional para renombrar.',
        'params' => 'multipart/form-data: file + name (opcional)',
        'response' => '{
  "success": true,
  "message": "Archivo subido correctamente.",
  "data": {
    "name": "archivo.pdf",
    "size": 102400,
    "size_human": "100 KB",
    "modified": "2025-01-15 10:30:00",
    "url": "https://tusitio.com/wp-content/uploads/WPFM/archivo.pdf",
    "mime": "application/pdf",
    "replaced": false
  }
}',
        'php' => '<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "' . esc_url($api_url) . 'upload");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-WPFM-Key: ' . esc_js($api_key) . '"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    "file" => new CURLFile("/ruta/archivo.pdf"),
    "name" => "archivo.pdf"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
print_r($data);',
        'curl' => 'curl -X POST -H "X-WPFM-Key: ' . esc_js($api_key) . '" -F "file=@/ruta/archivo.pdf" -F "name=archivo.pdf" ' . esc_url($api_url) . 'upload',
        'js' => 'const formData = new FormData();
formData.append("file", fileInput.files[0]);
formData.append("name", "archivo.pdf");

const response = await fetch("' . esc_js($api_url) . 'upload", {
    method: "POST",
    headers: {
        "X-WPFM-Key": "' . esc_js($api_key) . '"
    },
    body: formData
});
const data = await response.json();
console.log(data);',
    ],
    [
        'method' => 'POST',
        'path' => '/upload/base64',
        'title' => 'Subir / Reemplazar archivo (Base64)',
        'desc' => 'Sube un archivo enviando su contenido en base64. Si ya existe uno con el mismo nombre, se reemplaza. Acepta <code>mimetype</code> opcional.',
        'params' => 'JSON: name (requerido), file (requerido, base64), mimetype (opcional)',
        'response' => '{
  "success": true,
  "message": "Archivo subido correctamente.",
  "data": {
    "name": "archivo.pdf",
    "size": 102400,
    "size_human": "100 KB",
    "modified": "2025-01-15 10:30:00",
    "url": "https://tusitio.com/wp-content/uploads/WPFM/archivo.pdf",
    "mime": "application/pdf",
    "replaced": false
  }
}',
        'php' => '<?php
$base64 = base64_encode(file_get_contents("/ruta/archivo.pdf"));
$data = json_encode([
    "name" => "archivo.pdf",
    "file" => $base64,
    "mimetype" => "application/pdf"
]);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "' . esc_url($api_url) . 'upload/base64");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-WPFM-Key: ' . esc_js($api_key) . '",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);
print_r($result);',
        'curl' => 'curl -X POST "' . esc_url($api_url) . 'upload/base64" \
  -H "X-WPFM-Key: ' . esc_js($api_key) . '" \
  -H "Content-Type: application/json" \
  -d \'{"name":"archivo.pdf","file":"' . "BASE64_AQUI" . '","mimetype":"application/pdf"}\'',
        'js' => 'const base64 = btoa(String.fromCharCode(...new Uint8Array(arrayBuffer)));

const response = await fetch("' . esc_js($api_url) . 'upload/base64", {
    method: "POST",
    headers: {
        "X-WPFM-Key": "' . esc_js($api_key) . '",
        "Content-Type": "application/json"
    },
    body: JSON.stringify({
        name: "archivo.pdf",
        file: base64,
        mimetype: "application/pdf"
    })
});
const data = await response.json();
console.log(data);',
    ],
];
?>

<div class="wrap">
    <h2>API REST — Endpoints</h2>
    <p>Endpoint base: <code><?= esc_html($api_url) ?></code></p>

    <?php foreach ($endpoints as $ep): ?>
        <?php
        $method_color = $ep['method'] === 'GET' ? '#25992f' : ($ep['method'] === 'DELETE' ? '#d63638' : '#2271b1');

        $code_content = '<div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">'
            . '<span style="display:inline-block;padding:4px 10px;border-radius:4px;font-weight:700;font-size:12px;background:' . $method_color . ';color:#fff;">'
            . esc_html($ep['method']) . '</span>'
            . '<code style="font-size:14px;">' . esc_html($ep['path']) . '</code>'
            . '<span style="margin-left:auto;">' . FWUTooltip::render("Parámetros", esc_html($ep['params'])) . '</span>'
            . '</div>'
            . '<p>' . $ep['desc'] . '</p>';

        $code_items = [
            ['key' => 'php', 'title' => 'PHP', 'code' => $ep['php']],
            ['key' => 'curl', 'title' => 'cURL', 'code' => $ep['curl']],
            ['key' => 'js', 'title' => 'JS Fetch', 'code' => $ep['js']],
        ];
        foreach ($code_items as $ci) {
            $code_content .= FWUCollapse::html(
                '<span style="display:inline-flex;align-items:center;gap:6px;font-size:13px;"><span style="font-weight:700;">' . $ci['title'] . '</span></span>',
                '<div style="position:relative;">'
                    . '<textarea class="large-text code wpfm-code-copy" rows="10" readonly style="font-family:monospace;font-size:12px;background:#1d2327;color:#f0f0f1;padding:12px;border-radius:8px;border:1px solid #dcdcde;width:100%;resize:vertical;">'
                    . esc_textarea($ci['code']) . '</textarea>'
                    . '<button type="button" class="button wpfm-copy-code-btn" style="position:absolute;top:8px;right:8px;z-index:2;">Copiar</button>'
                    . '</div>',
                false
            );
        }

        $code_content .= FWUCollapse::html(
            '<span style="display:inline-flex;align-items:center;gap:6px;font-size:13px;"><span style="font-weight:700;">Respuesta de ejemplo</span></span>',
            '<textarea class="large-text code" rows="8" readonly style="font-family:monospace;font-size:12px;background:#f6f7f7;padding:12px;border-radius:8px;border:1px solid #dcdcde;width:100%;resize:vertical;">'
            . esc_textarea($ep['response']) . '</textarea>',
            false
        );
        ?>

        <?php FWUCollapse::render(
            '<span style="display:inline-flex;align-items:center;gap:8px;">'
                . '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-weight:700;font-size:11px;background:' . $method_color . ';color:#fff;">'
                . esc_html($ep['method']) . '</span>'
                . esc_html($ep['title'])
                . ' <code style="font-size:12px;color:#787c82;">' . esc_html($ep['path']) . '</code>'
            . '</span>',
            $code_content,
            true
        ); ?>

    <?php endforeach; ?>
</div>

<script>
jQuery(function($) {
    document.querySelectorAll('.wpfm-copy-code-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var textarea = this.parentElement.querySelector('.wpfm-code-copy');
            if (textarea) {
                textarea.select();
                document.execCommand('copy');
                this.textContent = '\u2713 Copiado';
                var self = this;
                setTimeout(function() { self.textContent = 'Copiar'; }, 2000);
            }
        });
    });
});
</script>
