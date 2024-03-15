<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration;

use PHPUnit\Framework\TestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsEnabled;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegistered;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegisteredForReal;

class ModuleTest extends TestCase
{
    use AssertModuleIsEnabled;
    use AssertModuleIsRegistered;
    use AssertModuleIsRegisteredForReal;

    public function testIfModuleIsEnabled()
    {
        $this->assertModuleIsEnabled('Yireo_GoogleTagManager2');
        $this->assertModuleIsRegistered('Yireo_GoogleTagManager2');
        $this->assertModuleIsRegisteredForReal('Yireo_GoogleTagManager2');
    }
}
