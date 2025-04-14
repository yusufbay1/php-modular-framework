<?php

namespace Core\Config;

class Config
{
    protected static array $loaded = [];

    public static function get(string $key, $default = null): mixed
    {
        $segments = explode('.', $key);
        $file = $segments[0];
        $config = self::load($file);
        return self::resolve($config, array_slice($segments, 1), $default);
    }

    protected static function load(string $file): array
    {
        if (isset(self::$loaded[$file]))
            return self::$loaded[$file];

        $path = __DIR__ . '/../../../config/' . $file . '.php';

        if (!file_exists($path))
            return self::$loaded[$file] = [];

        return self::$loaded[$file] = require $path;
    }

    protected static function resolve(array $array, array $segments, $default)
    {
        foreach ($segments as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array))
                return $default;
            $array = $array[$segment];
        }
        return $array;
    }
}
