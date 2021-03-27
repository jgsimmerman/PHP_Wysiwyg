<?php

namespace Altum;

use Altum\Routing\Router;

class Title {
    public static $full_title;
    public static $site_title;
    public static $page_title;

    public static function initialize($site_title) {

        self::$site_title = $site_title;

        /* Add the prefix if needed */
        $languageKey = preg_replace('/-/', '_', Router::$controller_key);

        if(Router::$path != '') {
            $languageKey = Router::$path . '_' . $languageKey;
        }

        /* Check if the default is viable and use it */
        $page_title = (isset(Language::get()->{$languageKey}->title)) ? Language::get()->{$languageKey}->title : Router::$controller;

        self::set($page_title);
    }

    public static function set($page_title, $full = false) {

        self::$page_title = $page_title;

        self::$full_title = self::$page_title . ($full ? null : ' - ' . self::$site_title);

    }


    public static function get() {

        return self::$full_title;

    }

}
