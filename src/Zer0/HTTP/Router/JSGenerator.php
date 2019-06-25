<?php

namespace Zer0\HTTP\Router;

use Zer0\HTTP\Exceptions\RouteNotFound;

/**
 * Class JSGenerator
 * @package Zer0\HTTP\Router
 */
class JSGenerator extends Basic
{
    /**
     * @return string
     */
    public function generate(): string
    {
        $routes = [];
        $stringMap = array_flip($this->stringMap);
        foreach ($this->routes as $routeName => $route) {
            if (!in_array('JS', $route['export'] ?? [], true)) {
                continue;
            }
            if (($route['defaults']['action'] ?? '') === 'index') {
                unset($route['defaults']['action']);
            }
            $item = [
                'path' => $route['path_export'] ?? $route['path'],
                'defaults' => $route['defaults'] ?? [],
            ];
            $conf['type'] = $conf['type'] ?? 'plain';
            if ($conf['type'] === 'websocket') {
            } elseif (isset($this->patternMap[$routeName])) {
                list($regex, $params) = $this->patternMap[$routeName];
                $item['regex'] = [$regex, $params];
            } elseif (isset($stringMap[$routeName])) {
                $item['exact'] = $stringMap[$routeName];
                $params = [];
            } else {
                throw new RouteNotFound($routeNamename);
            }

            $routes[$routeName] = $item;

        }

        $cfg = "// The file has been generated automatically\n"
            . "// Date: " . date('r') . "\n"
            . "// DO NOT MODIFY THIS FILE MANUALLY, YOUR CHANGES WILL BE OVERWRITTEN!\n\n";

        $cfg .= 'module.exports = ' . json_encode($routes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . ';';

        return $cfg;
    }
}
