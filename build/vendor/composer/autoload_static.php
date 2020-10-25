<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit48f15077913018c856844478f3ed1d95
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Share_On_Twitter\\Composer\\CaBundle\\' => 35,
            'Share_On_Twitter\\Abraham\\TwitterOAuth\\' => 38,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Share_On_Twitter\\Composer\\CaBundle\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
        ),
        'Share_On_Twitter\\Abraham\\TwitterOAuth\\' => 
        array (
            0 => __DIR__ . '/..' . '/abraham/twitteroauth/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit48f15077913018c856844478f3ed1d95::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit48f15077913018c856844478f3ed1d95::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
