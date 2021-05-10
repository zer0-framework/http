<?php

namespace Zer0\HTTP\Exceptions;

/**
 * Class HttpError
 *
 * @package Zer0\HTTP\Exceptions
 */
class HttpError extends \Exception
{
    /**
     * @var int
     */
    public $httpCode;

    public function setCode (int $code)
    {
        $this->httpCode = $code;
    }
}
