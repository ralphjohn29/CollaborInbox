<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_system',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get a setting by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        return static::castValue($setting->value, $setting->type);
    }
    
    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $type
     * @param string|null $description
     * @return Setting
     */
    public static function set(string $key, $value, ?string $type = null, ?string $description = null): Setting
    {
        $setting = static::firstOrNew(['key' => $key]);
        
        if ($type) {
            $setting->type = $type;
        } elseif (!$setting->exists) {
            // Auto-detect type if not specified and not existing
            $setting->type = static::detectType($value);
        }
        
        // Prepare value for storage based on type
        $setting->value = static::prepareValue($value, $setting->type);
        
        if ($description) {
            $setting->description = $description;
        }
        
        $setting->save();
        
        return $setting;
    }
    
    /**
     * Detect type of value
     *
     * @param mixed $value
     * @return string
     */
    protected static function detectType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        
        if (is_numeric($value)) {
            return 'number';
        }
        
        if (is_array($value) || is_object($value)) {
            return 'json';
        }
        
        return 'string';
    }
    
    /**
     * Prepare value for storage
     *
     * @param mixed $value
     * @param string $type
     * @return string|null
     */
    protected static function prepareValue($value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }
        
        switch ($type) {
            case 'json':
                return json_encode($value);
            case 'boolean':
                return $value ? '1' : '0';
            default:
                return (string) $value;
        }
    }
    
    /**
     * Cast value to appropriate type
     *
     * @param string|null $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue(?string $value, string $type)
    {
        if ($value === null) {
            return null;
        }
        
        switch ($type) {
            case 'boolean':
                return $value === '1' || $value === 'true' || $value === 'yes';
            case 'number':
                return is_numeric($value) ? (strpos($value, '.') !== false ? (float) $value : (int) $value) : 0;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
} 