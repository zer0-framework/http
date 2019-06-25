<?php
declare(strict_types=1);

namespace Zer0\HTTP;

use Zer0\App;
use Zer0\Config\Interfaces\ConfigInterface;
use Zer0\HTTP\Traits\Files;
use Zer0\HTTP\Traits\Handlers;
use Zer0\HTTP\Traits\Helpers;
use Zer0\HTTP\Traits\Pjax;
use Zer0\HTTP\Traits\Router;
use Zer0\Traits\EventHandlers;

/**
 * Class HTTP
 * @package Zer0\HTTP
 */
class HTTP
{
    use EventHandlers;
    use Pjax;
    use Helpers;
    use Handlers;
    use Router;
    use Files;

    /**
     * @var App
     */
    public $app;

    /**
     * @var ConfigInterface
     */
    public $config;


    /**
     * HTTP constructor.
     * @param ConfigInterface $config
     * @param App $app
     */
    public function __construct(ConfigInterface $config, App $app)
    {
        $this->config = $config;
        $this->app = $app;
    }

    /**
     * @return array
     */
    public function getAcceptedLanguages(): array
    {
        if (!preg_match_all(
            '~([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?~',
            strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''), $matches)) {

            return [];
        }
        $ret = array_combine($matches[1], $matches[2]);
        foreach ($ret as &$v) {
            $v = $v ?: 1;
        }
        arsort($ret, SORT_NUMERIC);
        return $ret;
    }

    /**
     * @return void
     */
    public function prepareEnv(): void
    {
        if (!$_GET && ($query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)) !== null) {
            parse_str($query, $_GET);
        }
        foreach ($_SERVER as $key => $value) {
            if (strncmp($key, 'DEFAULT_', 8) !== 0) {
                continue;
            }
            $realKey = substr($key, 8);
            if (($_SERVER[$realKey] ?? '') === '') {
                $_SERVER[$realKey] = $value;
            }
            unset($_SERVER[$key]);
        }
        if (isset($_SERVER['ROUTE_ACTION'])) {
            $_SERVER['ROUTE_ACTION'] = str_replace(' ', '', ucwords(str_replace('-', ' ', $_SERVER['ROUTE_ACTION'])));
        }
        if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
            $contentType = strstr($_SERVER['HTTP_CONTENT_TYPE'] . ';', ';', true);
            if ($contentType === 'application/json') {
                try {
                    $_POST = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
                    $_REQUEST += $_POST;
                } catch (\JsonException $e) {
                }
            }
        }
    }
}
