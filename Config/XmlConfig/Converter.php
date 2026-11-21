<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\XmlConfig;

use DOMDocument;
use DOMNode;
use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Exception\LocalizedException;

class Converter implements ConverterInterface
{
    public const TAG_CLASS_KEY = '__tag_class';

    /**
     * Convert dom node tree to array
     *
     * @param DOMDocument $source
     * @return array
     * @throws LocalizedException
     */
    public function convert($source): array
    {
        $result = [
            'default' => [],
            'events' => [],
        ];

        $defaults = $source->getElementsByTagName('default');
        if (count($defaults) > 0 && $defaults[0] instanceof DOMNode) {
            $result['default'][] = $this->toArray($defaults[0]);
        }

        $events = $source->getElementsByTagName('event');
        foreach ($events as $event) {
            /** @var DOMNode $event */
            if ($event->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            $eventId = $event->attributes->getNamedItem('id')->nodeValue;
            $result['events'][$eventId] = $this->toArray($event);
        }

        return $result;
    }

    /**
     * @param DOMNode $node
     * @return array
     * @throws LocalizedException
     */
    private function toArray(DOMNode $node): array
    {
        $result = [];
        foreach ($node->childNodes as $childNode) {
            if (false === $childNode instanceof DOMNode) {
                continue;
            }

            /** @var DOMNode $childNode */
            if ($childNode->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            if ($childNode->nodeName === 'event_name') {
                $result['event_name'] = $childNode->nodeValue;
                continue;
            }

            $tagName = $childNode->attributes->getNamedItem('name')->nodeValue;
            if ($tagName === self::TAG_CLASS_KEY) {
                throw new LocalizedException(
                    __('The item name "%1" is reserved in data_layer.xml', self::TAG_CLASS_KEY)
                );
            }

            $tagType = $childNode->attributes->getNamedItem('type')->nodeValue;
            if ($tagType === 'string') {
                $result[$tagName] = (string)$childNode->nodeValue;
                continue;
            }

            if (in_array($tagType, ['int', 'boolean'])) {
                $result[$tagName] = (int)$childNode->nodeValue;
                continue;
            }

            if (in_array($tagType, ['array'])) {
                $result[$tagName] = $this->toArray($childNode);
                continue;
            }

            if ($tagType === 'object') {
                $result[$tagName] = [self::TAG_CLASS_KEY => (string)$childNode->nodeValue];
                continue;
            }

            $result[$tagName] = 'unknown';
        }

        return $result;
    }
}
