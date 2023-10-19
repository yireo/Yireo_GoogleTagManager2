<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use AdPage\GTM\Api\Data\EventInterface;
use AdPage\GTM\Api\Data\MergeTagInterface;
use AdPage\GTM\Api\Data\ProcessorInterface;
use AdPage\GTM\Api\Data\TagInterface;
use RuntimeException;

class TagParser
{
    /**
     * @param array $data
     * @param ProcessorInterface[] $processors
     * @return array
     */
    public function parse(array $data, array $processors = []): array
    {
        foreach ($data as $tagName => $tagValue) {
            $data = $this->convertTag($tagName, $tagValue, $data);
        }

        foreach ($processors as $processor) {
            $data = array_replace_recursive($data, $processor->process($data));
        }

        return $data;
    }

    /**
     * @param $tagName
     * @param $tagValue
     * @param array $data
     * @return array
     */
    private function convertTag($tagName, $tagValue, array $data): array
    {
        if ($tagValue instanceof MergeTagInterface) {
            unset($data[$tagName]);
            return array_merge($data, $tagValue->merge());
        }

        if (is_object($tagValue)) {
            $data[$tagName] = $this->getValueFromFromTagValueObject($tagValue);
            return $data;
        }

        if (is_array($tagValue)) {
            foreach ($tagValue as $key => $value) {
                $tagValue = $this->convertTag($key, $value, $tagValue);
            }

            $data[$tagName] = $tagValue;
            return $data;
        }

        if (is_null($tagValue)) {
            unset($data[$tagName]);
        }

        return $data;
    }

    /**
     * @param ArgumentInterface $tagValueObject
     * @return mixed
     * @throws RuntimeException
     */
    private function getValueFromFromTagValueObject(ArgumentInterface $tagValueObject)
    {
        if ($tagValueObject instanceof TagInterface || $tagValueObject instanceof EventInterface) {
            return $tagValueObject->get();
        }

        throw new RuntimeException('Unknown object in data layer: ' . get_class($tagValueObject));
    }
}
