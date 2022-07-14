<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;
}
