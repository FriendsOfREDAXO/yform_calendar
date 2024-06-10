<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit39c68acf4840a00bdd8895c95e592362
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'RRule\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'RRule\\' => 
        array (
            0 => __DIR__ . '/..' . '/rlanvin/php-rrule/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit39c68acf4840a00bdd8895c95e592362::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit39c68acf4840a00bdd8895c95e592362::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit39c68acf4840a00bdd8895c95e592362::$classMap;

        }, null, ClassLoader::class);
    }
}