<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteb148c9baa09f280a8f5ae634da757bf
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Svea\\Checkout\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Svea\\Checkout\\' => 
        array (
            0 => __DIR__ . '/..' . '/sveaekonomi/checkout/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticIniteb148c9baa09f280a8f5ae634da757bf::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticIniteb148c9baa09f280a8f5ae634da757bf::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
