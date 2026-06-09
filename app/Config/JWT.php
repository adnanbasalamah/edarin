<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    public string $key = 'edarin_jwt_secret_key_2026_min_32chars';
    public int $expiresIn = 28800;
}
