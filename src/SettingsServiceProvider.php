<?php

namespace TakeTheLead\Settings;

use Composer\Command\ConfigCommand;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use TakeTheLead\Settings\Console\ListSettingsCommand;
use TakeTheLead\Settings\Console\UpdateSettingCommand;
use TakeTheLead\Settings\Exceptions\ConfigKeyNotFoundException;
use TakeTheLead\Settings\Exceptions\SettingNotFoundException;
use TakeTheLead\Settings\Models\Setting;
use TakeTheLead\Settings\Repositories\Config;
use TakeTheLead\Settings\Repositories\DatabaseRepository;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrations();
            $this->registerCommands();
        }

        $this->publishes([
            __DIR__ . '/../config/laravel-settings.php' => config_path('laravel-settings.php'),
        ], 'config');

        $this->loadFactoriesFrom(__DIR__ . '/../database/factories');

        // The service providers boot method is executed on every
        // request. Therefore we have to make sure the settings
        // table is present, otherwise we would be getting
        // exceptions when migrations haven't ran yet.

        if ($this->settingsTableExists()) {
            $this->overwriteConfigValues();
        }
    }

    public function register()
    {
        $this->app->register(EventServiceProvider::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-settings.php', 'laravel-settings');
    }

    private function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
    
    private function registerCommands()
    {
        $this->commands([
            ListSettingsCommand::class,
            UpdateSettingCommand::class,
        ]);
    }

    private function overwriteConfigValues()
    {
        $this->app->extend('config', function (Repository $config) {
            return (new DatabaseRepository($config))
                ->overwriteDefaults(config('laravel-settings.overwrites'))
                ->get();
        });
    }

    private function settingsTableExists()
    {
        return Cache::rememberForever('settings_table_exists', function () {
            return Schema::hasTable('settings');
        });
    }
}
