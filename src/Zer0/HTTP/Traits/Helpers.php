<?php

namespace Zer0\HTTP\Traits;

use PHPDaemon\Core\Daemon;
use Zer0\HTTP\Exceptions\RouteNotFound;
use Zer0\HTTP\Exceptions\Unauthorized;

/**
 * Trait Helpers
 * @package Zer0\HTTP\Traits
 */
trait Helpers
{

    /**
     * @param string $expires
     * @param string $cacheControl
     * @return void
     */
    public function expires(string $expires = '@0', string $cacheControl = 'private, must-revalidate'): void
    {
        $ts = strtotime($expires);
        if ($ts < time()) {
            $this->header('Cache-Control: ' . $cacheControl);
        } else {
            $this->header('Cache-Control: ' . $cacheControl);
        }
        $this->header('Expires: ' . gmdate('r', $ts));
    }

    /**
     * @param int $code
     */
    public function responseCode(int $code): void
    {
        if (PHP_SAPI !== 'cli') {
            http_response_code($code);
        } elseif (Daemon::$req !== null) {
            Daemon::$req->status($code);
        }
    }

    /**
     * @param string $hdr
     * @param bool $replace
     */
    public function header(string $hdr, bool $replace = true): void
    {
        if (PHP_SAPI !== 'cli') {
            header($hdr, $replace);
        } elseif (Daemon::$req !== null) {
            Daemon::$req->header($hdr, $replace);
        }
    }

    /**
     * @param $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    public function setcookie(
        $name,
        $value = "",
        $expire = 0,
        $path = "",
        $domain = "",
        $secure = false,
        $httponly = false
    ): bool
    {
        if ($secure === null) {
            $secure = ($_SERVER['REQUEST_SCHEME'] ?? null) === 'https';
        }
        if (PHP_SAPI !== 'cli') {
            return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }
        if (Daemon::$req !== null) {
            return Daemon::$req->setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }
        return true;
    }

    /**
     * @param callable $cb
     * @param $realm
     * @throws Unauthorized
     */
    public function basicAuth(callable $cb, $realm): void
    {
        if (!$cb($_SERVER['PHP_AUTH_USER'] ?? '', $_SERVER['PHP_AUTH_PW'] ?? '')) {
            $this->header('WWW-Authenticate: Basic realm="' . $realm . '", charset="UTF-8"');
            throw new Unauthorized("Restricted access to {$realm}");
        }
    }


    /**
     *
     *
     * @return bool
     */
    public function checkOrigin(): bool
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? null;
        if (!isset($origin) || parse_url($origin, PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
            $params = [
                'HTTP_ORIGIN' => isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null,
                'HTTP_REFERER' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
                'HTTP_HOST' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null,
            ];
            $this->app->log(
                'checkOrigin failed',
                $params
            );

            return false;
        }

        return true;
    }

    /**
     * @param string $routeName
     * @param mixed $params
     * @param array $query
     * @return string
     * @throws RouteNotFound
     */
    public function buildUrl(string $routeName, $params = [], array $query = []): string
    {
        return $this->url(...func_get_args());
    }

    /**
     * @param string $routeName
     * @param mixed $params
     * @param array $query
     * @return string
     * @throws RouteNotFound
     */
    public function url(string $routeName, $params = [], array $query = []): string
    {
        if (is_string($params)) {
            $params = [
                'action' => $params,
            ];
        }
        $route = $this->config->Routes->{$routeName} ?? null;
        if (!$route) {
            throw new RouteNotFound('Route ' . json_encode($routeName) . ' not found.');
        }

        $url = preg_replace_callback('~\{(.*?)\}~', function (array $match) use ($route, $params): string {
            $parameter = $match[1];
            return $params[$parameter] ?? $route['defaults'][$parameter] ?? '';
        }, $route['path_export'] ?? $route['path']);

        if ($url !== '/') {
            $url = rtrim($url, '/');
        }

        if ($query) {
            $url .= '?' . http_build_query($query);
        }
        return $url;
    }


    /**
     *
     */
    public function finishRequest()
    {
        fastcgi_finish_request();
    }
}
