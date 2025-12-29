<?php

namespace CodeInSync\Infrastructure\Http;

final class UrlContext
{
    private static ?string $baseHref = null;

    public static function setBaseHref(string $href): void
    {
        // Normalize to end with slash
        self::$baseHref = rtrim($href, '/') . '/';
    }

    public static function getBaseHref(): string
    {
        if (self::$baseHref === null) {
            throw new \RuntimeException('Base href has not been initialized');
        }
        return self::$baseHref;
    }
}