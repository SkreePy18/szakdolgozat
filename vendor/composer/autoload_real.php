<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit24f6cd7e1e2662acc21e717f6bb6829f
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit24f6cd7e1e2662acc21e717f6bb6829f', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit24f6cd7e1e2662acc21e717f6bb6829f', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit24f6cd7e1e2662acc21e717f6bb6829f::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
