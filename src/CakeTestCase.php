<?php

namespace Integrated;

use Integrated\Traits\InteractsWithCake;
use TestDummy\BaseTestCase;

abstract class CakeTestCase extends BaseTestCase
{
    use InteractsWithCake;

    protected $baseUrl = 'http://localhost';
}