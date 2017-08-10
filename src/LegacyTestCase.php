<?php
namespace Integrated;

use Integrated\Traits\InteractsWithCake;
use Integrated\Traits\LegacyInteractionWithCake;
use TestDummy\BaseTestCase;

/**
 * Class LegacyTestCase
 *
 * @deprecated 
 * @package Integrated
 */
abstract class LegacyTestCase extends BaseTestCase
{
    use LegacyInteractionWithCake;

    protected $baseUrl = 'http://localhost';
}