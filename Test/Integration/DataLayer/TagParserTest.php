<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\DataLayer;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use Yireo\GoogleTagManager2\DataLayer\TagParser;

class TagParserTest extends TestCase
{
    public function testIfEmptyValuesAreRemoved()
    {
        $data = [
            'example1' => 'example1',
            'example2' => null,
        ];

        $tagParser = ObjectManager::getInstance()->get(TagParser::class);
        $data = $tagParser->parse($data, []);
        $this->assertTrue(count($data) === 1);
    }

    public function testIfAddedTagsAreCorrect()
    {
        $mock = $this->createMock(TagInterface::class);
        $mock->method('get')->willReturn(['exampleKey' => 'exampleValue']);
        $data = [
            'example1' => $mock
        ];

        $tagParser = ObjectManager::getInstance()->get(TagParser::class);
        $data = $tagParser->parse($data, []);
        $this->assertTrue(count($data) === 1);
        $this->assertTrue(isset($data['example1']));
        $this->assertTrue(is_array($data['example1']));
        $this->assertEquals('exampleValue', $data['example1']['exampleKey'], var_export($data, true));
    }
}
