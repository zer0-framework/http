<?php

namespace Zer0\HTTP;

use Zer0\TestCase;

/**
 * Class InputTest
 * @package Zer0\HTTP
 */
class InputTest extends TestCase
{

    /**
     *
     */
    public function setUp()
    {

    }

    /**
     * @test
     * @covers \Zer0\Model\CodeGen::transformSqlToPhp()
     */
    public function testBasic()
    {
        $scope = [];
        $input = new class([$scope]) extends Input {
            protected $exceptionsBundle = null;
            /**
             * @var array
             */
            protected static $rules = [
                'step' => 'string',
                'type' => 'string|requiredIf:step,getCode',
            ];

            /**
             * @var array
             */
            protected static $rulesParsed = [];
        };
    }
}
