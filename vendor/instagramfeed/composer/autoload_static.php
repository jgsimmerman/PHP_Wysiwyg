<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2a3a8fa84bc73e956cc78fa1f346f4d8
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
        ),
        'I' => 
        array (
            'InstagramScraper\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'InstagramScraper\\' => 
        array (
            0 => __DIR__ . '/..' . '/raiym/instagram-php-scraper/src/InstagramScraper',
        ),
    );

    public static $prefixesPsr0 = array (
        'U' => 
        array (
            'Unirest\\' => 
            array (
                0 => __DIR__ . '/..' . '/mashape/unirest-php/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2a3a8fa84bc73e956cc78fa1f346f4d8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2a3a8fa84bc73e956cc78fa1f346f4d8::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit2a3a8fa84bc73e956cc78fa1f346f4d8::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
