<?php

namespace App\Lang;

class Lang
{
    protected static ?array $strings = null;
    protected static string $locale = 'id';

    public static function init(string $locale = 'id'): void
    {
        self::$locale = $locale;
        $file = __DIR__ . '/' . $locale . '.php';
        if (file_exists($file)) {
            self::$strings = require $file;
        } else {
            self::$strings = require __DIR__ . '/id.php';
            self::$locale = 'id';
        }
    }

    public static function get(string $key, array $replace = []): string
    {
        if (self::$strings === null) {
            self::init();
        }

        $keys = explode('.', $key);
        $value = self::$strings;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $key;
            }
            $value = $value[$k];
        }

        if (!is_string($value)) {
            return $key;
        }

        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, $v, $value);
        }

        return $value;
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function setLocale(string $locale): void
    {
        self::$locale = $locale;
    }

    public static function available(): array
    {
        return [
            'id' => 'Bahasa Indonesia',
            'en' => 'English',
        ];
    }
}
