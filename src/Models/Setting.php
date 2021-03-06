<?php

namespace TakeTheLead\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use TakeTheLead\Settings\Database\Factories\SettingFactory;
use TakeTheLead\Settings\Events\SettingWasSaved;
use TakeTheLead\Settings\Exceptions\InvalidTypeException;
use TakeTheLead\Settings\Exceptions\TypeNotQueriedException;

class Setting extends Model
{
    use HasFactory;

    public $guarded = [];

    protected $dispatchesEvents = [
        'saved' => SettingWasSaved::class,
    ];

    protected static function newFactory()
    {
        return SettingFactory::new();
    }

    public function getValueAttribute($value)
    {
        // The exists check is required to prevent exceptions when loading an empty model.
        // Ex. when creating a setting using Laravel Nova.
        if ($this->exists && is_null($this->type)) {
            throw new TypeNotQueriedException("The type column is not included in the query, this prevents value casting & decryption.");
        }

        if (! is_null($value)) {
            $value = decrypt($value);
        }

        try {
            if ($this->type === 'json') {
                $value = json_decode($value);
            } else {
                settype($value, $this->type);
            }
        } finally {
            return $value;
        }
    }

    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = encrypt($value);
    }

    public static function byKey(string $key)
    {
        return Cache::rememberForever(static::getCacheKey($key), function () use ($key) {
            return static::where('key', $key)->first();
        });
    }

    public static function isEnabled(string $key)
    {
        $setting = static::byKey($key);

        if (is_null($setting)) {
            return false;
        }

        if (in_array($setting->type, ['string', 'json'])) {
            throw new InvalidTypeException('Can not check if setting of type string is enabled');
        }

        if (! $setting->value) {
            return false;
        }

        return true;
    }

    public static function isNotEnabled(string $key)
    {
        return ! static::isEnabled($key);
    }

    public static function getCacheKey(string $key): string
    {
        return "setting_$key";
    }
}
