<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
}
