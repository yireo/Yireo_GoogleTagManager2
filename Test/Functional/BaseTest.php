<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Functional;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    public function testIfModuleIsEnabled()
    {
        $componentRegistrar = ObjectManager::getInstance()->get(ComponentRegistrar::class);
        $path = $componentRegistrar->getPath('module', 'Yireo_GoogleTagManager2');
        $this->assertTrue(file_exists($path.'/registration.php'));
    }
}
