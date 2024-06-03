<?php
declare(strict_types=1);

namespace Tagging\GTM\Test\Functional;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    public function testIfModuleIsEnabled()
    {
        $componentRegistrar = ObjectManager::getInstance()->get(ComponentRegistrar::class);
        $path = $componentRegistrar->getPath('module', 'Tagging_GTM');
        $this->assertTrue(file_exists($path.'/registration.php'));
    }
}
