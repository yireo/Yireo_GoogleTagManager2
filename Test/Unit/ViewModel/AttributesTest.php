<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ViewModel;

use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ViewModel\Attributes;

class AttributesTest extends TestCase
{
    public function testAttributes()
    {
        $jsonEncoder = $this->createMock(Json::class);
        $jsonEncoder->method('serialize')->willReturn('{"foo":"bar"}');

        $attributes = new Attributes($jsonEncoder);
        $this->assertEmpty($attributes->getAttributes());
        $attributes->addAttribute('foo', 'bar');
        $this->assertNotEmpty($attributes->getAttributes());
        $this->assertEquals(1, count($attributes->getAttributes()));
        $this->assertEquals('{"foo":"bar"}', $attributes->getAttributesAsJson());

        $attributes->resetAttributes();
        $this->assertEmpty($attributes->getAttributes());
    }
}
