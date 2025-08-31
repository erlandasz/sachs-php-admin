<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class DynamicConfigService
{
    /**
     * Add or overwrite an Airtable table configuration dynamically.
     */
    public static function setDynamicConfig(string $tableName, string $baseId): void
    {
        $tables = Config::get('airtable.tables', []);
        $tables[$tableName] = [
            'name' => 'Registrations',
            'base' => $baseId,
        ];
        Config::set('airtable.tables', $tables);
    }
}
