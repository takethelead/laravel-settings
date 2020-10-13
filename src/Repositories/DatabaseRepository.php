<?php

namespace TakeTheLead\Settings\Repositories;

use Illuminate\Contracts\Config\Repository;
use TakeTheLead\Settings\Exceptions\ConfigKeyNotFoundException;
use TakeTheLead\Settings\Exceptions\SettingNotFoundException;
use TakeTheLead\Settings\Models\Setting;

class DatabaseRepository
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected Repository $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function overwriteDefaults(array $overwrites): DatabaseRepository
    {
        foreach ($overwrites as $configKey => $settingName) {
            if (!$this->config->has($configKey)) {
                throw new ConfigKeyNotFoundException("Config key \"$configKey\" could not be found.");
            }

            $setting = Setting::byKey($settingName);

            if (is_null($setting)) {
                report(new SettingNotFoundException("$settingName could not be found."));
                continue;
            }

            $this->config->set($configKey, $setting->value);
        }

        return $this;
    }

    public function get(): Repository
    {
        return $this->config;
    }
}
