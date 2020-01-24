<?php

namespace Zer0\HTTP\Responses;

use Zer0\HTTP\HTTP;

/**
 * Class Html
 * @package Zer0\HTTP\Responses
 */
class Html extends Base
{
    /**
     * @var string
     */
    public $body = '';

    /**
     * Html constructor.
     * @param string $body
     */
    public function __construct(string $body)
    {
        $this->body = $body;
    }

    /**
     * @param HTTP $http
     * @param bool $fetch
     * @return mixed|string
     */
    public function render(HTTP $http, bool $fetch = false)
    {
        if ($fetch) {
            return $this->body;
        }
        $http->header('Content-Type: text/html');
        echo $this->body;
    }
}
