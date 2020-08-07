<?php

namespace Zer0\HTTP\Responses;

use Zer0\HTTP\HTTP;
use Zer0\HTTP\Intefarces\ControllerInterface;

/**
 * Class Base
 * @package Zer0\HTTP\Responses
 */
abstract class Base
{

    /**
     * @var \Zer0\HTTP\Intefarces\ControllerInterface
     */
    protected $controller;
    
    /**
     * @var mixed
     */
    protected $scope;

    /**
     * @param HTTP $http
     * @return mixed
     */
    abstract public function render(HTTP $http);
    
    /**
     * @param $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param ControllerInterface $controller
     * @return $this
     */
    public function setController(ControllerInterface $controller)
    {
        $this->controller = $controller;
        return $this;
    }
}
