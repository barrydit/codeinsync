<?php
// classes/class.pathutils.php

namespace CodeInSync\Shared\Filesystem;

use function count;

final class PathUtils
{
    public static function norm(?string $p): string
    {
        $p = $p ?? '';
        $p = str_replace('\\', '/', $p);
        $p = preg_replace('#/+#', '/', $p);       // collapse multiple slashes
        $p = preg_replace('#(^|/)\./#', '$1', $p); // remove "./"
        return trim($p, '/');
    }

    public static function rel(string $from, string $to): string
    {
        $from = rtrim(realpath($from), '/') . '/';
        $to = rtrim(realpath($to), '/') . '/';
        $fromParts = explode('/', trim($from, '/'));
        $toParts = explode('/', trim($to, '/'));

        // Find common prefix
        while (count($fromParts) && count($toParts) && $fromParts[0] === $toParts[0]) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        // Go up for remaining fromParts
        $up = str_repeat('../', count($fromParts));
        return $up . implode('/', $toParts);
    }


    /** Get parent path, with trailing slash (or empty string if no parent) */
    public static function parentPath(?string $p): string
    {
        $p = self::norm($p);
        if ($p === '')
            return '';
        // dirname trick with leading slash so dirname() behaves
        $d = dirname("/$p");
        return $d === '/' ? '' : ltrim($d, '/') . '/';
    }

    /** Extract best-guess [client, domain] from APP_ROOT like "projects/clients/000-Doe,John/example.com" */
    public static function clientDomainFromRoot(string $root): array
    {
        $parts = array_values(array_filter(explode('/', self::norm($root)), 'strlen'));
        // Heuristic: find "clients" or "projects" and take next two as client/domain
        $idx = array_search('clients', $parts, true);
        if ($idx === false)
            $idx = array_search('projects', $parts, true);
        $client = $parts[$idx + 1] ?? '';
        $domain = $parts[$idx + 2] ?? '';
        return [$client, $domain];
    }

    public static function stripLeading(string $p, string $prefix): string
    {
        $p = self::norm($p);
        $prefix = self::norm($prefix);
        if ($prefix !== '' && strpos($p . '/', $prefix . '/') === 0) {
            return ltrim(substr($p, strlen($prefix)), '/');
        }
        return $p;
    }

    public static function onlyOneScope(array $GET): array
    {
        if (!empty($GET['project'])) {
            return ['project' => $GET['project']];
        }
        if (!empty($GET['client'])) {
            return ['client' => $GET['client'], 'domain' => '']; // drop domain
        }
        if (!empty($GET['domain'])) {
            return ['domain' => $GET['domain']];
        }
        return [];
    }

    public static function buildChildPath(string $base, string $child): string
    {
        $base = self::norm($base);
        $child = self::norm($child);
        return ($base === '' ? $child : $base . '/' . $child) . '/';
    }
}