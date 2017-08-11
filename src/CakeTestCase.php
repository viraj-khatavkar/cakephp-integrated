<?php

namespace Integrated;

use Integrated\Traits\InteractsWithCake;

abstract class CakeTestCase extends IntegratedTestCase
{
    use InteractsWithCake;

    protected $baseUrl = 'http://localhost';
}