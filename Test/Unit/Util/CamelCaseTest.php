<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License
 */

namespace Yireo\GoogleTagManager2\Test\Unit\Util;

use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Cookie\Helper\Cookie as CookieHelper;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Util\CamelCase;

class CamelCaseTest extends TestCase
{
    public function testToCamelCase()
    {
        $camelCase = new CamelCase();
        $this->assertEquals('Foobar', $camelCase->to('foobar'));
        $this->assertEquals('FooBar', $camelCase->to('foo_bar'));
        $this->assertEquals('FooBar', $camelCase->to('Foo_Bar'));
    }

    public function testFromCamelCase()
    {
        $camelCase = new CamelCase();
        $this->assertEquals('foobar', $camelCase->from('foobar'));
        $this->assertEquals('foo_bar', $camelCase->from('fooBar'));
        $this->assertEquals('foo_bar', $camelCase->from('FooBar'));
    }
}
