<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer;

use Yireo\GoogleTagManager2\DataLayerProcessor\ProcessorInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use RuntimeException;

class TagParser
{
    /**
     * @param array $data
     * @param ProcessorInterface[] $processors
     * @return array
     */
    public function parse(array $data, array $processors): array
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
     * @param mixed|TagInterface $variable
     * @return mixed|TagInterface
     */
    private function convertTag($tagName, $tagValue, $data)
    {
        if ($tagValue instanceof TagInterface) {
            $data[$tagName] = $tagValue->get();
        }

        if (is_object($tagValue) && !$tagValue instanceof TagInterface) {
            throw new RuntimeException('Unknown variable in data layer: ' . get_class($tagValue));
        }

        if (is_array($tagValue)) {
            foreach ($tagValue as $key => $value) {
                $tagValue = $this->convertTag($key, $value, $tagValue);
            }

            $data[$tagName] = $tagValue;
        }

        if (is_null($tagValue)) {
            unset($data[$tagName]);
        }

        return $data;
    }
}
