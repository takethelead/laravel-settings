<?php

namespace TakeTheLead\Settings\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use TakeTheLead\Settings\Models\Setting;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition()
    {
        $types = ['string', 'boolean'];
        $type = $types[$this->faker->randomKey($types)];

        return [
            'key' => str_replace(' ', '_', $this->faker->words(3, true)),
            'value' => $type === 'boolean' ? $this->faker->boolean() : $this->faker->url,
            'type' => $type,
        ];
    }
}
