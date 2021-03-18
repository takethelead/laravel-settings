<?php

namespace TakeTheLead\Settings\Console;

use Illuminate\Console\Command;
use TakeTheLead\Settings\Models\Setting;

class ListSettingsCommand extends Command
{
    protected $signature = 'laravel-settings:list';

    protected $description = 'List all available settings from the database';

    public function handle()
    {
        $settings = $this->getSettings();
        $this->table(['ID', 'Key', 'Value', 'Type'], $settings, 'box');

        $this->info("Listing " . count($settings) . ' settings');
    }

    private function getSettings(): array
    {
        return Setting::all(['id', 'key', 'value', 'type'])
            ->map(function (Setting $setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $this->formatValue($setting->value),
                    'type' => $setting->type,
                ];
            })->toArray();
    }

    private function formatValue($value)
    {
        if (is_object($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return $value;
    }
}
