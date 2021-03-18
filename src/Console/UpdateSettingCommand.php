<?php

namespace TakeTheLead\Settings\Console;

use Illuminate\Console\Command;
use TakeTheLead\Settings\Models\Setting;

class UpdateSettingCommand extends Command
{
    protected $signature = 'laravel-settings:update {setting}';

    protected $description = 'Update a setting';

    public function handle(): void
    {
        $setting = Setting::where('key', $this->argument('setting'))->first();

        if (! is_null($setting)) {
            $this->updateSetting($setting);

            return;
        }

        if (! $this->confirm("Setting '" . $this->argument('setting') . "' does not exist, do you want to create it?")) {
            $this->error('Ok, stopped!');

            return;
        }

        $this->createSetting();

        return;
    }

    private function createSetting()
    {
        $type = $this->choice('Select the type for your setting', ['string', 'boolean', 'json']);

        $setting = Setting::create([
            'key' => $this->argument('setting'),
            'value' => $this->getSettingValue($type),
            'type' => $type,
        ]);

        $this->info('Setting ' . $this->argument('setting') . ' has been created');

        $this->previewChangesInTable($this->argument('setting'), $setting->value, $setting->type);
    }

    private function updateSetting(Setting $setting)
    {
        $this->info("Current settings for: '$setting->key'");

        $this->showSettingInTable($setting);

        if (! $this->confirm('Are you sure that you want to continue editing this setting?')) {
            $this->error('Ok, aborted!');

            return;
        }

        if ($this->confirm('Do you want to set a new value?')) {
            $value = $this->getSettingValue($setting->type);
        } else {
            $value = $setting->value;
        }

        $this->previewChangesInTable($this->argument('setting'), $value, $setting->type);

        if (! $this->confirm('Are the settings above correct? Note that once confirmed it will be updated in the database!')) {
            $this->error('Ok, aborted!');

            return;
        }

        $setting->update([
            'key' => $this->argument('setting'),
            'value' => $value,
        ]);

        $this->info("New settings for: '$setting->key'");

        $this->showSettingInTable($setting);
    }

    private function getSettingValue(string $type)
    {
        if (in_array($type, ['string', 'json'])) {
            return $this->ask('Enter the value for the setting');
        }

        $answer = $this->choice('Select the value for the setting', ['True', 'False'], 'False');

        if ($answer === 'False') {
            return false;
        }

        return true;
    }

    private function formatSettingDataForTableView(Setting $setting): array
    {
        return [
            'id' => $setting->id,
            'key' => $setting->key,
            'value' => $this->formatValueForTableView($setting->value),
            'type' => $setting->type,
        ];
    }

    private function formatValueForTableView($value): string
    {
        if (is_object($value)) {
            return json_encode($value);
        }

        if (! is_bool($value)) {
            return $value;
        }

        if ($value) {
            return 'Yes';
        }

        return 'No';
    }

    private function showSettingInTable(Setting $setting): void
    {
        $this->table(
            ['ID', 'Key', 'Value', 'Type', 'Is manageable'],
            [$this->formatSettingDataForTableView($setting)],
            'box'
        );
    }

    private function previewChangesInTable(string $key, $value, string $type): void
    {
        $this->table(
            ['Key', 'Value', 'Type'],
            [
                [$key, $this->formatValueForTableView($value), $type],
            ],
            'box'
        );
    }
}
