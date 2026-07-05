<?php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Http;

final class UrlContext
{
    /**
     * Application base href/path used by routes.
     *
     * Examples:
     *   /                                clean DocumentRoot install
     *   /clients/example/site/           subdirectory install
     *
     * NOTE: This intentionally behaves like your existing getBaseUrl():
     * it returns a URL path with a trailing slash, not a full scheme/host URL.
     */
    private static ?string $baseUrl = null;

    /**
     * Public asset URL prefix.
     *
     * Examples:
     *   ''                               DocumentRoot points to APP_PATH/public
     *   /public                          DocumentRoot points to APP_PATH
     *   /clients/example/site/public     subdirectory root bridge install
     */
    private static ?string $publicUrlPrefix = null;

    /**
     * Keep your existing public method name.
     * This sets the application base href/path, not necessarily a full absolute URL.
     */
    public static function setBaseUrl(string $href): void
    {
        self::$baseUrl = self::normalizeBaseHref($href);
    }

    /**
     * Optional alias for clarity where the caller means <base href="...">.
     */
    public static function setBaseHref(string $href): void
    {
        self::setBaseUrl($href);
    }

    /**
     * Keep your existing public method name.
     * Auto-detects if not manually initialized.
     */
    public static function getBaseUrl(): string
    {
        return self::$baseUrl ??= self::detectBaseHref();
    }

    /**
     * Optional alias for clarity.
     */
    public static function getBaseHref(): string
    {
        return self::getBaseUrl();
    }

    /**
     * If you ever need the full absolute URL.
     * Example: http://localhost/clients/example/site/
     */
    public static function getAbsoluteBaseUrl(): string
    {
        return self::detectScheme() . '://' . self::detectHost() . self::getBaseUrl();
    }

    /**
     * Manually override public URL prefix when auto-detection is not enough.
     */
    public static function setPublicUrlPrefix(string $prefix): void
    {
        self::$publicUrlPrefix = self::normalizeUrlPrefix($prefix);
    }

    /**
     * Returns the browser-visible prefix to the public directory.
     *
     * Clean install:
     *   DocumentRoot /mnt/c/www/public
     *   /assets/app.css
     *
     * Root bridge install:
     *   DocumentRoot /var/www/html
     *   root index.php requires public/index.php
     *   /public/assets/app.css
     */
    public static function getPublicUrlPrefix(): string
    {
        if (defined('APP_PUBLIC_URL_PREFIX')) {
            return self::normalizeUrlPrefix((string) APP_PUBLIC_URL_PREFIX);
        }

        return self::$publicUrlPrefix ??= self::detectPublicUrlPrefix();
    }

    /**
     * Build URL to a public asset/file.
     */
    public static function publicUrl(string $path = ''): string
    {
        $prefix = self::getPublicUrlPrefix();
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if ($prefix === '') {
            return '/' . $path;
        }

        return rtrim($prefix, '/') . ($path !== '' ? '/' . $path : '');
    }

    /**
     * Full request URI exactly as requested by the browser, normalized for slashes.
     *
     * Examples:
     *   /clients/site/login
     *   /clients/site/?r=login
     */
    public static function requestUri(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $uri = str_replace('\\/', '/', $uri);
        $uri = str_replace('\\', '/', $uri);

        // Only normalize repeated slashes in the path portion, not after http://.
        $parts = parse_url($uri);
        $path = isset($parts['path']) && is_string($parts['path']) ? $parts['path'] : '/';
        $path = preg_replace('#/+#', '/', $path) ?? '/';

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return ($path !== '' ? $path : '/') . $query . $fragment;
    }

    /**
     * Path portion of request URI.
     *
     * Examples:
     *   /clients/site/login
     *   /clients/site/
     */
    public static function requestPath(): string
    {
        $path = parse_url(self::requestUri(), PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        return $path;
    }

    /**
     * Query portion of request URI.
     *
     * Example:
     *   r=login&foo=bar
     */
    public static function requestQuery(): string
    {
        $query = parse_url(self::requestUri(), PHP_URL_QUERY);
        return is_string($query) ? $query : '';
    }

    /**
     * Base path derived from the configured/detected base URL.
     *
     * Example:
     *   baseUrl:  /clients/000-Cowie,%20Jocelyn/mtnspa.institute/
     *   returns:  /clients/000-Cowie,%20Jocelyn/mtnspa.institute
     */
    public static function basePath(): string
    {
        $path = parse_url(self::getBaseUrl(), PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '';
        }

        $path = str_replace('\\', '/', $path);
        $path = rtrim($path, '/');

        return $path === '' ? '' : $path;
    }

    /**
     * Request path relative to the application root.
     *
     * Examples:
     *   requestPath: /clients/site/login
     *   basePath:    /clients/site
     *   result:      /login
     *
     *   requestPath: /clients/site/
     *   basePath:    /clients/site
     *   result:      /
     */
    public static function appPath(): string
    {
        $path = self::requestPath();
        $base = self::basePath();

        if ($base !== '' && $base !== '/' && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }

        if ($path === false || $path === null || $path === '') {
            $path = '/';
        }

        $path = str_replace('\\', '/', $path);

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        // In root bridge mode, do not let /public/index.php become a route.
        if ($path === '/public/index.php' || $path === '/index.php') {
            return '/';
        }

        return $path;
    }

    /**
     * Normalize a route string for internal use.
     *
     * Examples:
     *   /login        => login
     *   dashboard     => dashboard
     *   /admin/users  => admin/users
     */
    public static function normalizeRoute(?string $route): string
    {
        $route = (string) $route;
        $route = str_replace('\\', '/', $route);
        $route = trim($route);
        $route = trim($route, '/');
        $route = strtolower($route);

        $route = preg_replace('~[^a-z0-9/_-]+~', '', $route) ?? '';

        return trim($route, '/');
    }

    /**
     * Route parsed only from the pretty path.
     *
     * Examples:
     *   /login      => login
     *   /           => ''
     *   /admin/user => admin/user
     */
    public static function routeFromPath(): string
    {
        return self::normalizeRoute(self::appPath());
    }

    /**
     * Returns true when the original request explicitly used ?r=...
     *
     * Examples:
     *   /?r=login => true
     *   /login    => false
     */
    public static function isQueryRoute(): bool
    {
        $query = self::requestQuery();

        if ($query === '') {
            return false;
        }

        parse_str($query, $params);

        return isset($params['r']) && $params['r'] !== '';
    }

    /**
     * Returns true when the original request used a pretty path route.
     *
     * Examples:
     *   /login    => true
     *   /?r=login => false
     *   /         => false
     */
    public static function isPrettyRoute(): bool
    {
        if (self::isQueryRoute()) {
            return false;
        }

        return self::routeFromPath() !== '';
    }

    /**
     * Returns route source: query, pretty, or root.
     */
    public static function routeSource(): string
    {
        if (self::isQueryRoute()) {
            return 'query';
        }

        if (self::isPrettyRoute()) {
            return 'pretty';
        }

        return 'root';
    }

    /**
     * Resolve current route regardless of whether it came from:
     *   /login
     *   /?r=login
     */
    public static function resolveRoute(string $default = 'home'): string
    {
        if (self::isQueryRoute()) {
            return self::normalizeRoute($_GET['r'] ?? '') ?: $default;
        }

        $pathRoute = self::routeFromPath();

        return $pathRoute !== '' ? $pathRoute : $default;
    }

    /**
     * Build URL from current or provided URI with merged query params.
     *
     * Passing null or '' removes the parameter.
     */
    public static function buildUrlWithQuery(array $params = [], ?string $uri = null): string
    {
        $uri ??= self::requestUri();

        $parts = parse_url($uri);

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
            } else {
                $query[$key] = $value;
            }
        }

        $path = $parts['path'] ?? '/';
        $qs = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $path . ($qs ? '?' . $qs : '') . $fragment;
    }

    /**
     * Build an application URL from a route.
     *
     * Examples:
     *   buildRouteUrl('login', true)  => /base/login
     *   buildRouteUrl('login', false) => /base/?r=login
     */
    public static function buildRouteUrl(string $route, bool $pretty = true): string
    {
        $route = self::normalizeRoute($route);
        $base = self::getBaseUrl();

        if ($pretty) {
            return $base . $route;
        }

        return $base . ($route !== '' ? '?r=' . rawurlencode($route) : '');
    }

    /**
     * Build an application URL from a relative path.
     *
     * Example:
     *   buildPathUrl('tools/ui/ace_editor')
     */
    public static function buildPathUrl(string $path = ''): string
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        return self::getBaseUrl() . $path;
    }

    /**
     * Redirect using a modified query string on the current URI.
     */
    public static function redirectWithQuery(array $params = [], ?string $uri = null, int $statusCode = 302): void
    {
        header('Location: ' . self::buildUrlWithQuery($params, $uri), true, $statusCode);
        exit;
    }

    /**
     * Redirect to a route using pretty or query style.
     */
    public static function redirectToRoute(string $route, bool $pretty = true, int $statusCode = 302): void
    {
        header('Location: ' . self::buildRouteUrl($route, $pretty), true, $statusCode);
        exit;
    }

    /**
     * Redirect to an application-relative path.
     */
    public static function redirectToPath(string $path = '', int $statusCode = 302): void
    {
        header('Location: ' . self::buildPathUrl($path), true, $statusCode);
        exit;
    }

    /**
     * Debug helper for current request context.
     */
    public static function debug(string $defaultRoute = 'home'): array
    {
        return [
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? null,
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
            'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? null,
            'PHP_SELF' => $_SERVER['PHP_SELF'] ?? null,
            'APP_PATH' => defined('APP_PATH') ? APP_PATH : null,
            'APP_PUBLIC_FS_ROOT' => defined('APP_PUBLIC_FS_ROOT') ? APP_PUBLIC_FS_ROOT : self::publicFsRoot(),
            'baseUrl' => self::getBaseUrl(),
            'baseHref' => self::getBaseHref(),
            'absoluteBaseUrl' => self::getAbsoluteBaseUrl(),
            'publicUrlPrefix' => self::getPublicUrlPrefix(),
            'publicIndexUrl' => self::publicUrl('index.php'),
            'basePath' => self::basePath(),
            'requestPath' => self::requestPath(),
            'requestQuery' => self::requestQuery(),
            'appPath' => self::appPath(),
            'routeFromPath' => self::routeFromPath(),
            'route' => self::resolveRoute($defaultRoute),
            'source' => self::routeSource(),
            'isPretty' => self::isPrettyRoute(),
            'isQuery' => self::isQueryRoute(),
            'isPublicDocumentRoot' => self::isPublicDocumentRoot(),
            'isRootBridgeInstall' => self::isRootBridgeInstall(),
        ];
    }

    /**
     * Filesystem helpers
     */
    private static function appRoot(): string
    {
        if (defined('APP_PATH')) {
            return self::normalizeFsPath((string) APP_PATH);
        }

        // Fallback from src/Infrastructure/Http/UrlContext.php to project root.
        return self::normalizeFsPath(dirname(__DIR__, 3));
    }

    private static function publicFsRoot(): string
    {
        if (defined('APP_PUBLIC_FS_ROOT')) {
            return self::normalizeFsPath((string) APP_PUBLIC_FS_ROOT);
        }

        return self::normalizeFsPath(self::appRoot() . '/public');
    }

    private static function documentRoot(): string
    {
        return self::normalizeFsPath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    }

    private static function scriptFilename(): string
    {
        return self::normalizeFsPath((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    }

    private static function scriptName(): string
    {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        return $script !== '' ? $script : '/index.php';
    }

    private static function isPublicDocumentRoot(): bool
    {
        $documentRoot = self::documentRoot();
        return $documentRoot !== '' && $documentRoot === self::publicFsRoot();
    }

    private static function isRootBridgeInstall(): bool
    {
        $documentRoot = self::documentRoot();
        return $documentRoot !== '' && $documentRoot === self::appRoot();
    }

    /**
     * Detect app base href/path.
     *
     * This decides where application routes live.
     * It should not include /public even when public assets are under /public.
     */
    private static function detectBaseHref(): string
    {
        $scriptName = self::scriptName();

        // /index.php => /
        // /clients/site/index.php => /clients/site/
        $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($dir === '' || $dir === '.' || $dir === '/') {
            return '/';
        }

        // If someone directly hits /public/index.php in root bridge mode,
        // the application base is still the parent directory, not /public/.
        if (str_ends_with($dir, '/public')) {
            $dir = substr($dir, 0, -strlen('/public'));
            if ($dir === '') {
                return '/';
            }
        }

        return self::normalizeBaseHref($dir);
    }

    /**
     * Detect browser-visible path to public files.
     */
    private static function detectPublicUrlPrefix(): string
    {
        $base = rtrim(self::getBaseUrl(), '/');

        if (self::isPublicDocumentRoot()) {
            return $base === '' ? '' : $base;
        }

        if (self::isRootBridgeInstall()) {
            return ($base === '' ? '' : $base) . '/public';
        }

        // Fallback: if SCRIPT_FILENAME is inside public root, infer from SCRIPT_NAME.
        $scriptFile = self::scriptFilename();
        $publicRoot = self::publicFsRoot();
        $scriptName = self::scriptName();

        if ($scriptFile !== '' && str_starts_with($scriptFile, $publicRoot)) {
            $relative = ltrim(substr($scriptFile, strlen($publicRoot)), '/');
            if ($relative !== '') {
                $suffix = '/' . $relative;
                if (str_ends_with($scriptName, $suffix)) {
                    $prefix = substr($scriptName, 0, -strlen($suffix));
                    return self::normalizeUrlPrefix($prefix);
                }
            }
        }

        // Conservative fallback for shared hosting/root bridge style.
        return ($base === '' ? '' : $base) . '/public';
    }

    private static function detectScheme(): string
    {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
            || (int) ($_SERVER['SERVER_PORT'] ?? 80) === 443
        ) ? 'https' : 'http';
    }

    private static function detectHost(): string
    {
        $host = (string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost');
        $host = trim(explode(',', $host)[0]);

        return $host !== '' ? $host : 'localhost';
    }

    private static function normalizeBaseHref(string $href): string
    {
        $href = str_replace('\\', '/', trim($href));
        $href = str_replace(' ', '%20', $href);

        if ($href === '' || $href === '.') {
            return '/';
        }

        // Preserve absolute URLs if you intentionally pass one.
        if (preg_match('~^https?://~i', $href) === 1) {
            return rtrim($href, '/') . '/';
        }

        return '/' . trim($href, '/') . '/';
    }

    private static function normalizeUrlPrefix(string $prefix): string
    {
        $prefix = str_replace('\\', '/', trim($prefix));
        $prefix = str_replace(' ', '%20', $prefix);
        $prefix = trim($prefix, '/');

        return $prefix === '' ? '' : '/' . $prefix;
    }

    private static function normalizeFsPath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = rtrim($path, '/');

        return $path;
    }
}
