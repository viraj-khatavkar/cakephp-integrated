<?php
namespace Integrated;

use Integrated\Traits\LegacyInteractionWithCake;

/**
 * Class LegacyTestCase
 *
 * @deprecated 
 * @package Integrated
 */
abstract class LegacyTestCase extends IntegratedTestCase
{
    use LegacyInteractionWithCake;

    protected $baseUrl = 'http://localhost';
}