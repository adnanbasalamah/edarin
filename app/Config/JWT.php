<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    public string $key = 'edarin_default_secret_key_2026';
    public int $expiresIn = 28800;
}
