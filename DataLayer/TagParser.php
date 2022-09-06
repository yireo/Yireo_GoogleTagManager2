<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Yireo\GoogleTagManager2\DataLayerProcessor\ProcessorInterface;
use Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface;
use RuntimeException;

class TagParser
{
    private ObjectManagerInterface $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

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
        if (is_string($tagValue) && preg_match('/([\w\\\-\_]+)\:\:([\w]+)/', $tagValue, $tagMatch)) {
            $tagValue = $this->getValueFromCallable($tagMatch[1], $tagMatch[2]);
            $data[$tagName] = $tagValue;
            return $data;
        }

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

    /**
     * @param string $className
     * @param string $classMethod
     * @return false|mixed
     */
    private function getValueFromCallable(string $className, string $classMethod)
    {
        if (!class_exists($className)) {
            return false;
        }

        if (!method_exists($className, $classMethod)) {
            return false;
        }

        $object = $this->objectManager->get($className);
        if (!$object instanceof ArgumentInterface) {
            return false;
        }

        return call_user_func([$object, $classMethod]);
    }
}
