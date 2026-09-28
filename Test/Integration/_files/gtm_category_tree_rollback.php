<?php declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
foreach (['gtm-multi-category', 'gtm-no-usable-category'] as $sku) {
    try {
        $productRepository->deleteById($sku);
    } catch (NoSuchEntityException $exception) {
    }
}

$categoryCollection = $objectManager->get(CategoryCollectionFactory::class)->create();
$categoryCollection->addAttributeToFilter('name', ['like' => 'GTM %']);
$categoryCollection->setOrder('level', 'DESC');
foreach ($categoryCollection as $category) {
    if ($category->getId()) {
        $category->delete();
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
