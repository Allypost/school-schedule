<?php

namespace Allypost\Helpers;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Slim\Slim;

class SiteSettings extends Eloquent {

    const CACHE_PREFIX = 'ibrahim-is-useful';
    const CACHE_KEY    = ':ibrahim-is-useful:settings:';
    const CACHE_FOR    = 60 * 60 * 24 * 7;
    protected $table    = 'settings';
    protected $fillable = [
        'setting',
        'value',
        'created_at',
        'updated_at',
    ];
    protected $casts    = [];
    protected $hidden   = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Return app instance
     *
     * @return Slim The instance
     */
    public function app() {
        return Slim::getInstance();
    }


    /**
     * Update settings
     *
     * @param array $data The array of new settings
     *
     * @return array Settings array
     */
    public function edit(array $data) {
        $oldSettings = $this->retrieve();
        $updates     = array_diff_assoc($data, $oldSettings);

        $success = $this->doEdit($updates, $oldSettings);

        if (!empty($success)) {
            $this->app()->log->log('settings update', $updates);
            $this->clearCache();
        }

        return $success;
    }

    /**
     * Get array of settings (formatted)
     *
     * @return array Formatted settings array
     */
    public function retrieve() {
        $data = $this->list();

        return $this->format($data);
    }

    /**
     * Retrieve list of all settings
     *
     * @return array The array of settings;
     */
    public function list() {
        $cache = $this->app()->cache;

        $cacheKey = self::CACHE_KEY;
        $cacheFor = self::CACHE_FOR;

        $cacheHit = $settings = $cache->get($cacheKey);

        if (!$cacheHit) {
            $settings = $this->get()->toArray();

            $cache->set($cacheKey, $settings, MEMCACHE_COMPRESSED, $cacheFor);
        }

        return $settings;
    }

    /**
     * Perform the editing of the data
     *
     * @param array $settingsDiff The diff of the old and new settings
     * @param array $oldSettings  The array of old settings
     *
     * @return array List of keys with values being whether the update was successful
     */
    private function doEdit(array $settingsDiff, array $oldSettings) {
        $updates = [];

        foreach ($settingsDiff as $key => $value) {

            if (isset($oldSettings[ $key ])) {
                $updates[ $key ] = !!$this->where('setting', $key)->update(compact('value'));
            } else {
                $updates[ $key ] = FALSE;
            }

        }

        return $updates;
    }

    /**
     * Clear the settings cache
     */
    public function clearCache() {
        $cache = $this->app()->cache;
        $cache->delete(self::CACHE_KEY);
    }

    /**
     * Format the settings list
     *
     * @param array $data The list of settings
     *
     * @return array The formatted list
     */
    private function format($data) {
        $return = [];

        foreach ($data as $item) {
            $return[ $item[ 'setting' ] ] = $item[ 'value' ];
        }

        return $return;
    }
}
