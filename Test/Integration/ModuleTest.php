<?php declare(strict_types=1);

namespace Tagging\GTM\Test\Integration;

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
        $this->assertModuleIsEnabled('Tagging_GTM');
        $this->assertModuleIsRegistered('Tagging_GTM');
        $this->assertModuleIsRegisteredForReal('Tagging_GTM');
    }
}
