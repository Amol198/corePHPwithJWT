<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit62e3e77e76604c0c081fe405fca1201a
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'O' => 
        array (
            'OAuth2' => 
            array (
                0 => __DIR__ . '/..' . '/bshaffer/oauth2-server-php/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit62e3e77e76604c0c081fe405fca1201a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit62e3e77e76604c0c081fe405fca1201a::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit62e3e77e76604c0c081fe405fca1201a::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit62e3e77e76604c0c081fe405fca1201a::$classMap;

        }, null, ClassLoader::class);
    }
}