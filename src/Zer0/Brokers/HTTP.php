<?php

namespace Zer0\Brokers;

use Zer0\Config\Interfaces\ConfigInterface;

/**
 * Class HTTP
 * @package Zer0\Brokers
 */
class HTTP extends Base
{

    /**
     * @param ConfigInterface $config
     * @return \Zer0\HTTP\HTTP
     */
    public function instantiate(ConfigInterface $config): \Zer0\HTTP\HTTP
    {
        return new \Zer0\HTTP\HTTP($config, $this->app);
    }

    /**
     * @param string $name
     * @param bool $caching
     * @return \Zer0\HTTP\HTTP
     */
    public function get(string $name = '', bool $caching = true): \Zer0\HTTP\HTTP
    {
        return parent::get($name, $caching);
    }
}
