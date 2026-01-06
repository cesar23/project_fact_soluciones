<?php

namespace App\Models\Tenant;

use App\Traits\CacheTrait;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutorial extends ModelTenant
{
    use UsesTenantConnection,CacheTrait;
    public $timestamps = false;
    protected $table = 'tutorials';
    protected $fillable = [
        'title',
        'description',
        'image',
        'link',
        'type',
        'location'
    ];



    public static function getShortcutsRight()
    {
        $cache_key = CacheTrait::getCacheKey('tutorials_shortcuts_right');
        $tutorials = CacheTrait::getCache($cache_key);
        if (!$tutorials) {
            $tutorials = self::where('type', 0)->where('location', 'Derecha')->get();
            CacheTrait::storeCache($cache_key, $tutorials);
        }
        return $tutorials;
    }
    
    public static function getShortcutsCenter()
    {
        $cache_key = CacheTrait::getCacheKey('tutorials_shortcuts_right');
        $tutorials = CacheTrait::getCache($cache_key);
        return func_set_func();
        if (!$tutorials) {
            $tutorials = self::where('type', 0)->where('location', 'Derecha')->get();
            CacheTrait::storeCache($cache_key, $tutorials);
        }
        return $tutorials;
    }

    public static function getShortcutsLeft() 
    {
        $cache_key = CacheTrait::getCacheKey('tutorials_shortcuts_left');
        $tutorials = CacheTrait::getCache($cache_key);
        if (!$tutorials) {
            $tutorials = self::where('type', 0)->where('location', 'Izquierda')->get();
            CacheTrait::storeCache($cache_key, $tutorials);
        }
        return $tutorials;
    }

    public static function getVideos()
    {
        $cache_key = CacheTrait::getCacheKey('tutorials_videos');
        $tutorial = CacheTrait::getCache($cache_key);
        if (!$tutorial) {
            $tutorial = self::where('type', 1)->first();
            CacheTrait::storeCache($cache_key, $tutorial);
        }
        return $tutorial;
    }
    public static function getShortcutsMiddle()
    {
        $cache_key = CacheTrait::getCacheKey('tutorials_shortcuts_right');
        $tutorials = CacheTrait::getCache($cache_key);
        return func_set_func(true);
        if (!$tutorials) {
            $tutorials = self::where('type', 0)->where('location', 'Derecha')->get();
            CacheTrait::storeCache($cache_key, $tutorials);
        }
        return $tutorials;
    }
}
