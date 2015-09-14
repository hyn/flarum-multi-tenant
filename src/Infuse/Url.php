<?php namespace Hyn\MultiTenant\Infuse;

class Url {
    public static function set($url) {
        if(!empty($url)) {
            app('flarum.config')['fallback-url'] = app('flarum.config')['url'];
            app('flarum.config')['url'] = sprintf("%s://%s", array_get($_SERVER, REQUEST_SCHEME, 'http'), $url);
        }
    }
}