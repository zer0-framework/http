<?php

namespace Zer0\HTTP\Traits;

use Zer0\HTTP\AbstractController;
use Zer0\HTTP\Exceptions\EndRequest;
use Zer0\HTTP\Exceptions\FinishRequest;
use Zer0\HTTP\Exceptions\HttpError;
use Zer0\HTTP\Exceptions\InternalRedirect;
use Zer0\HTTP\Exceptions\InternalServerError;
use Zer0\HTTP\Exceptions\NotFound;
use Zer0\HTTP\Exceptions\Redirect;
use Zer0\HTTP\Intefarces\ControllerInterface;

/**
 * Trait Handlers
 *
 * @package Zer0\HTTP\Traits
 */
trait Handlers
{
    /**
     * @var callable
     */
    protected $exceptionHandler;

    /**
     * @var bool
     */
    protected $handlingException = false;

    /**
     * @param callable $handler
     */
    public function setExceptionHandler (callable $handler): void
    {
        $this->exceptionHandler = $handler;
    }

    /**
     * @param string $controllerClass
     * @param string $action
     * @param array  $args
     *
     * @return void
     * @throws \Exception
     */
    public function handleRequest (string $controllerClass, string $action, array $args = []): void
    {
        try {
            ob_start();
            $controller = $this->getController($controllerClass, $action);
            $controller->before();
            $method = $controller->action;
            $ret    = $controller->$method(...$args);
            if ($ret !== null) {
                $controller->renderResponse($ret);
            }
            $controller->after();
        } catch (HttpError $error) {
            $this->handleHttpError($error);
        } catch (EndRequest $e) {
        } catch (InternalRedirect $redirect) {
            $this->handleRequest($redirect->controller, $redirect->action);
        } catch (Redirect $redirect) {
            $this->handleRedirect($redirect);
        } catch (\Throwable $exception) {
            $ret = $controller->onException($exception);
            if ($ret !== null) {
                $controller->renderResponse($ret);
                $controller->after();
            } else {
                $this->handleException($exception);
            }
        } finally {
            if (ob_get_level()) {
                ob_end_flush();
            }
        }
    }

    /**
     * @param string $controllerClass
     * @param string $action
     */
    public function getController (string $controllerClass, string $action): AbstractController
    {
        if ($controllerClass === '') {
            throw new NotFound('$controllerClass cannot be empty');
        }
        if ($action === '') {
            $action = 'index';
        }

        $method = strtolower($action) . 'Action';

        if (substr($controllerClass, 0, 1) !== '\\') {
            $controllerClass = $this->config->default_controller_ns . '\\' . $controllerClass;
        }

        if (!class_exists($controllerClass) || !class_implements($controllerClass, ControllerInterface::class)) {
            throw new NotFound('Controller ' . $controllerClass . ' not found.');
        }

        /** @var \Zer0\HTTP\AbstractController $controller */
        $controller = new $controllerClass($this, $this->app);

        if (!method_exists($controller, $method)) {
            throw new NotFound(
                'Action ' . $action . ' not found in controller '
                . get_class($controller)
            );
        }

        $controller->action = $method;

        return $controller;
    }

    /**
     * @param HttpError $error
     *
     * @return void
     * @throws \Exception
     */
    public function handleHttpError (HttpError $error): void
    {
        $route = $this->config->route_errors[$error->httpCode] ?? $this->config->route_errors['any'] ?? null;
        if ($route !== null) {
            $this->handleRequest($route['controller'] ?? '', $route['action'] ?? '', [$error]);
        }
    }

    /**
     * @param \Throwable $exception
     *
     * @return void
     * @throws \Exception
     */
    public function handleException (\Throwable $exception): void
    {
        if (!$this->handlingException) {
            $this->handlingException = true;
            $stop                    = false;
            if ($this->exceptionHandler !== null) {
                $stop = call_user_func($this->exceptionHandler, $exception);
            }
            if (!$stop) {
                $this->handleHttpError(new InternalServerError('Uncaught exception', 500, $exception));
            }
            $this->handlingException = false;
        }
    }

    /**
     * @param Redirect $redirect
     *
     * @return void
     * @throws \Exception
     */
    public function handleRedirect (Redirect $redirect): void
    {
        $this->responseCode($redirect->httpCode);
        $this->header('Location: ' . $redirect->getUrl());
    }

    /**
     *
     */
    public function handleRequestEnd ()
    {
        $this->trigger('endRequest');
        $this->cleanupEventHandlers();
    }
}
