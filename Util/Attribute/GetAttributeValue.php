<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author    Jisse Reitsma <jisse@yireo.com>
 * @copyright 2022 Yireo (https://www.yireo.com/)
 * @license   Open Source License (OSL v3)
 */

namespace AdPage\GTM\Util\Attribute;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\LocalizedException;
use AdPage\GTM\Util\CamelCase;

class GetAttributeValue
{
    private EavConfig $eavConfig;
    private CamelCase $camelCase;

    /**
     * @param EavConfig $eavConfig
     * @param CamelCase $camelCase
     */
    public function __construct(
        EavConfig $eavConfig,
        CamelCase $camelCase
    ) {
        $this->eavConfig = $eavConfig;
        $this->camelCase = $camelCase;
    }

    /**
     * @param ProductInterface $product
     * @param string $attributeCode
     * @return array|mixed|string
     * @throws LocalizedException
     */
    public function getProductAttributeValue(ProductInterface $product, string $attributeCode)
    {
        return $this->getAttributeValue($product, 'catalog_product', $attributeCode);
    }

    /**
     * @param CategoryInterface $category
     * @param string $attributeCode
     * @return array|mixed|string
     * @throws LocalizedException
     */
    public function getCategoryAttributeValue(CategoryInterface $category, string $attributeCode)
    {
        return $this->getAttributeValue($category, 'catalog_category', $attributeCode);
    }

    /**
     * @param CustomerInterface $customer
     * @param string $attributeCode
     * @return array|mixed|string
     * @throws LocalizedException
     */
    public function getCustomerAttributeValue(CustomerInterface $customer, string $attributeCode)
    {
        return $this->getAttributeValue($customer, 'customer', $attributeCode);
    }

    /**
     * @param CustomAttributesDataInterface $entity
     * @param string $entityType
     * @param string $attributeCode
     * @return array|mixed|string
     * @throws LocalizedException
     */
    public function getAttributeValue(CustomAttributesDataInterface $entity, string $entityType, string $attributeCode)
    {
        if ($attributeCode === 'id') {
            /** @phpstan-ignore-next-line  */
            return $entity->getId();
        }

        $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);
        $entityAttribute = $entity->getCustomAttribute($attributeCode);
        if ($entityAttribute instanceof AttributeInterface) {
            return $this->filterAttributeValue($attribute, $entityAttribute->getValue());
        }

        $attributeMethod = 'get' . ucfirst($this->camelCase->from($attributeCode));
        if (method_exists($entity, $attributeMethod)) {
            $attributeValue = call_user_func([$entity, $attributeMethod]);
            return $this->filterAttributeValue($attribute, $attributeValue);
        }

        if (method_exists($entity, 'getData')) {
            $attributeValue = $entity->getData($attributeCode);
            return $this->filterAttributeValue($attribute, $attributeValue);
        }

        return null;
    }

    /**
     * @param Attribute $attribute
     * @param $attributeValue
     * @return mixed|string
     */
    private function filterAttributeValue(AbstractAttribute $attribute, $attributeValue)
    {
        if (in_array($attribute->getFrontendInput(), ['textarea', 'text']) && !is_array($attributeValue)) {
            return strip_tags((string)$attributeValue);
        }

        if ($attribute->getFrontendInput() === 'select') {
            return $this->getAttributeValueFromSelect($attribute, $attributeValue);
        }

        if ($attribute->getFrontendInput() === 'multiselect') {
            return $this->getAttributeValueFromMultiSelect($attribute, $attributeValue);
        }

        return $attributeValue;
    }

    /**
     * @param AbstractAttribute $attribute
     * @param $attributeValue
     * @return string
     * @throws LocalizedException
     */
    private function getAttributeValueFromSelect(AbstractAttribute $attribute, $attributeValue): string
    {
        if (empty($attributeValue)) {
            return '';
        }

        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option) {
            if ((string)$option['value'] === (string)$attributeValue) {
                return (string)$option['label'];
            }
        }

        return '';
    }

    /**
     * @param AbstractAttribute $attribute
     * @param $attributeValue
     * @return array
     * @throws LocalizedException
     */
    private function getAttributeValueFromMultiSelect(AbstractAttribute $attribute, $attributeValue): array
    {
        if (empty($attributeValue)) {
            return [];
        }

        $attributeValues = is_array($attributeValue)
            ? $attributeValue
            : explode(',', $attributeValue);

        $options = $attribute->getSource()->getAllOptions();
        $attributeLabels = [];
        foreach ($options as $option) {
            if (in_array($option['value'], $attributeValues)) {
                $attributeLabels[] = (string)$option['label'];
            }
        }

        return $attributeLabels;
    }
}
