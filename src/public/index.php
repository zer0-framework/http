<?php

use Zer0\HTTP\Exceptions\HttpError;

if (!defined('ZERO_ROOT')) {
    define('ZERO_ROOT', $_SERVER['ZERO_ROOT']);
}
require ZERO_ROOT . '/vendor/zer0-framework/core/src/bootstrap.php';
$app->factory('Autorun');
/**
 * @var \Zer0\HTTP\HTTP $http
 */
$http = $app->factory('HTTP');

try {
    $http->prepareEnv();
    if (!isset($_SERVER['ROUTE_CONTROLLER'])) {
        $http->routeRequest();
    }
    $http->trigger('beginRequest');
    $http->handleRequest(
        $_SERVER['ROUTE_CONTROLLER'] ?? '',
        $_SERVER['ROUTE_ACTION'] ?? ''
    );
} catch (HttpError $error) {
    $http->handleHttpError($error);
} catch (\Throwable $exception) {
    $http->handleException($exception);
} finally {
    $http->handleRequestEnd();
}
