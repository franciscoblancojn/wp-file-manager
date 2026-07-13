<?php

if (!defined('ABSPATH')) exit;

use franciscoblancojn\wordpress_utils\FWUPage;

$GPAI_USE_DATA_CONFIG = new WPFM_USE_DATA_CONFIG();
$CONFIG = $GPAI_USE_DATA_CONFIG->get();

$TAGS = [
    [
        'key' => 'files',
        'title' => 'Archivos',
    ],
    [
        'key' => 'config',
        'title' => 'Configuración',
    ],
];
if (!empty($CONFIG['api_enabled'])) {
    $TAGS[] = [
        'key' => 'api',
        'title' => 'API',
    ];
}
$defaultTag = $TAGS[0]['key'];

echo FWUPage::css();
?>

<div id="page-<?= WPFM_KEY ?>" class="wrap">
    <h1>WP File Manager</h1>
    <?php FWUPage::tabs($TAGS, $defaultTag); ?>
    <?php foreach ($TAGS as $tag): ?>
        <div class="tab-content <?= $tag['key'] === $defaultTag ? 'nav-tab-active' : '' ?>" id="<?= $tag['key'] ?>">
            <?php
            $section_file = WPFM_DIR . 'src/page/sections/' . $tag['key'] . '.php';
            if (file_exists($section_file)) {
                require $section_file;
            }
            ?>
        </div>
    <?php endforeach; ?>
</div>

<?php
echo FWUPage::js(WPFM_KEY);
?>
