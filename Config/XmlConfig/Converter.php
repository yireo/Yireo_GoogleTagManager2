<?php declare(strict_types=1);

namespace AdPage\GTM\Config\XmlConfig;

use DOMDocument;
use DOMNode;
use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\ObjectManagerInterface;
use AdPage\GTM\Api\Data\TagInterface;

class Converter implements ConverterInterface
{
    private ObjectManagerInterface $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Convert dom node tree to array
     *
     * @param DOMDocument $source
     * @return array
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
     */
    private function toArray(DOMNode $node): array
    {
        $result = [];
        foreach ($node->childNodes as $childNode) {
            /** @phpstan-ignore-next-line  */
            if (empty($childNode)) {
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
                $result[$tagName] = $this->getDataFromTagClass((string)$childNode->nodeValue);
                continue;
            }

            $result[$tagName] = 'unknown';
        }

        return $result;
    }

    /**
     * @param string $tagClassName
     * @return array|mixed
     */
    private function getDataFromTagClass(string $tagClassName)
    {
        $tagObject = $this->objectManager->get($tagClassName);
        if (!$tagObject instanceof TagInterface) {
            return [];
        }

        return $tagObject->get();
    }
}
