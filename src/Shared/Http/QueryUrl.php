<?php
// classes/class.queryurl.php

namespace CodeInSync\Shared\Http;

/* ---------- URL query builder that honors your scenarios ---------- */
final class QueryUrl
{
    /**
     * Build href by preserving current scope from $_GET:
     * - If project is present => only project (exclusive)
     * - Else keep client (if present) and domain (if present)
     * - Always set path to $nextPath (can be '' to mean context root)
     */
    public static function build(array $GET, string $nextPath): string
    {
        $q = self::scope($GET);

        // If you'd like to retain additional harmless params, whitelist here:
        // foreach (['view','mode'] as $k) if (isset($GET[$k])) $q[$k] = $GET[$k];

        $q['path'] = $nextPath;

        // Optional: avoid redundant path when it equals the scope string
        foreach (['project', 'domain'] as $k) {
            if (!empty($q[$k]) && rtrim($q[$k], '/') === rtrim($nextPath, '/')) {
                // keep explicit path anyway? If not, uncomment next line to drop it.
                // unset($q['path']);
            }
        }

        // Filter out empties and build
        $q = array_filter($q, static fn($v) => $v !== '' && $v !== null);
        return '?' . http_build_query($q);
    }

    /** Scope rules: project is exclusive; client & domain may co-exist. */
    private static function scope(array $GET): array
    {
        $out = [];
        if (!empty($GET['project'])) {
            $out['project'] = $GET['project'];
            return $out; // exclusive
        }
        if (!empty($GET['client']))
            $out['client'] = $GET['client'];
        if (!empty($GET['domain']))
            $out['domain'] = $GET['domain'];
        return $out;
    }
}
