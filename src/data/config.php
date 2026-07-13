<?php

if (!defined('ABSPATH')) exit;

class WPFM_USE_DATA_CONFIG extends WPFM_USE_DATA_BASE
{
    protected $KEY = WPFM_CONFIG;

    public function init()
    {
        parent::init();
        $this->ensureApiKey();
    }

    private function ensureApiKey()
    {
        if (empty($this->DATA['api_key'])) {
            $this->DATA['api_key'] = $this->generateApiKey();
            $this->onSave();
        }
    }

    public function generateApiKey()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $result = 'wpfm_';
        $bytes = random_bytes(48);
        for ($i = 0; $i < 48; $i++) {
            $result .= $chars[ord($bytes[$i]) % strlen($chars)];
        }
        return $result;
    }
}
