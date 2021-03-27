<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitab8fb6aef2fc317cfda4614aaa0efdc4
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'EspressoDev\\InstagramBasicDisplay\\' => 34,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'EspressoDev\\InstagramBasicDisplay\\' => 
        array (
            0 => __DIR__ . '/..' . '/espresso-dev/instagram-basic-display-php/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitab8fb6aef2fc317cfda4614aaa0efdc4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitab8fb6aef2fc317cfda4614aaa0efdc4::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
