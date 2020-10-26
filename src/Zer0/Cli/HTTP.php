<?php

namespace Zer0\Cli\Controllers;

use Zer0\Cli\AbstractController;
use Zer0\HTTP\Router\JSGenerator;
use Zer0\HTTP\Router\NginxGenerator;

/**
 * Class Build
 * @package Zer0\Cli\Controllers
 */
final class HTTP extends AbstractController
{
    /**
     * @var \Zer0\Queue\Pools\Base
     */
    protected $queue;

    /**
     * @var string
     */
    protected $command = 'http';

    /**
     * @param mixed ...$args
     * @throws \Exception
     */
    public function buildNginxAction(...$args): void
    {
        $indentStr = function (string $str, int $n = 0): string {
            return preg_replace('~^~m', str_repeat("\t", $n), $str);
        };

        $escapeServerName = function (string $str): string {
            return strtr($str, [
                '"' => '\\"',
                '{' => '\\{',
                '}' => '\\}',
            ]);
        };

        $config = $this->app->broker('HTTP')->getConfig();

        $routesGenerator = new NginxGenerator($config->Routes->toArray());

        $destfile = ZERO_ROOT . '/nginx/server.conf';
        ob_start();
        echo "#### The file has been generated automatically\n"
            . "#### Date: " . date('r') . "\n"
            . "#### DO NOT MODIFY THIS FILE MANUALLY, YOUR CHANGES WILL BE OVERWRITTEN!\n\n";
        include ZERO_ROOT . '/nginx/server.conf.php';
        $body = ob_get_contents();
        ob_end_clean();

        // Writing into the file
        file_put_contents($tmp = tempnam(dirname($destfile), 'cfg'), $body);
        rename($tmp, $destfile);
        chmod($destfile, 0755);
        $this->cli->successLine("Written to $destfile in " . $this->elapsedMill() . " ms.");
    }

    public function buildRoutesjsAction(): void
    {
        $destfile = ZERO_ROOT . '/public/js/Routes.cfg.js';
        if (!is_dir(dirname($destfile))) {
            mkdir(dirname($destfile), ($this->app->config->HTTP->dir_mode ?? 0750), true);
        }

        $routes = $this->app->broker('HTTP')->getConfig()->Routes;
        $generator = new JSGenerator($routes->toArray());
        $cfg = $generator->generate();

        // Writing into the file
        file_put_contents($tmp = tempnam(dirname($destfile), 'cfg'), $cfg);
        rename($tmp, $destfile);
        chmod($destfile, 0755);
        $this->cli->successLine("Written to $destfile in " . $this->elapsedMill() . " ms.");
    }

    /**
     *
     */
    public function buildAllAction()
    {
        foreach ($this->getActions() as $action) {
            if ($action === 'build-all' || substr($action, 0, 5) !== 'build') {
                continue;
            }
            $this->cli->handleCommand('\\' . static::class, $action);
        }
    }

    /**
     *
     */
    public function listIncludedFiles(): void
    {
        foreach (get_included_files() as $file) {
            echo substr($file, strlen(getcwd())) . "\n";
        }
    }
}
