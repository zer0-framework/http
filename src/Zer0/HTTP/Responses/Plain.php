<?php

namespace Zer0\HTTP\Responses;

use Zer0\HTTP\HTTP;

/**
 * Class Plain
 * @package Zer0\HTTP\Responses
 */
class Plain extends Base
{
    /**
     * @var string
     */
    public $body = '';

    /**
     * Plain constructor.
     * @param string $body
     */
    public function __construct(string $body)
    {
        $this->body = $body;
    }

    /**
     * Base constructor.
     * @param HTTP $http
     */
    public function render(HTTP $http)
    {
        $http->header('Content-Type: text/plain');
        echo $this->body;
    }
}
