<?php declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/gtm_category_tree_rollback.php';

$objectManager = Bootstrap::getObjectManager();
$categoryFactory = $objectManager->get(CategoryFactory::class);
$categoryResource = $objectManager->get(CategoryResource::class);
$storeManager = $objectManager->get(StoreManagerInterface::class);
$defaultRootCategoryId = (int)$storeManager->getStore('default')->getRootCategoryId();

$createCategory = static function (string $name, ?Category $parent, bool $isActive = true, array $data = []) use (
    $categoryFactory,
    $categoryResource
): Category {
    $category = $categoryFactory->create();
    $category->isObjectNew(true);
    $category->setStoreId(Store::DEFAULT_STORE_ID);
    $category->setAttributeSetId($category->getDefaultAttributeSetId());
    $category->setName($name);
    $category->setUrlKey(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)));
    $category->setIsActive($isActive);
    $category->setIncludeInMenu(true);
    $category->setAvailableSortBy(['position']);
    $category->setDefaultSortBy('position');
    if ($parent instanceof Category) {
        $category->setParentId($parent->getId());
        $category->setPath($parent->getPath());
    } else {
        $category->setParentId(Category::TREE_ROOT_ID);
        $category->setPath((string)Category::TREE_ROOT_ID);
    }

    $category->addData($data);
    $categoryResource->save($category);

    return $category;
};

$defaultRootCategory = $categoryFactory->create();
$categoryResource->load($defaultRootCategory, $defaultRootCategoryId);

$otherRoot = $createCategory('GTM Other Root', null);
$outside = $createCategory('GTM Outside Root', $otherRoot);
$inactive = $createCategory('GTM Inactive', $defaultRootCategory, false);
$parent = $createCategory('GTM Parent', $defaultRootCategory, true, ['meta_title' => 'GTM Parent Meta Title']);
$child = $createCategory('GTM Child', $parent);
$grandchild = $createCategory('GTM Grandchild', $child);
$extra1 = $createCategory('GTM Extra 1', $defaultRootCategory);
$extra2 = $createCategory('GTM Extra 2', $defaultRootCategory);
$extra3 = $createCategory('GTM Extra 3', $defaultRootCategory);
$storeDisabled = $createCategory('GTM Store Disabled', $defaultRootCategory);

$productFactory = $objectManager->get(ProductInterfaceFactory::class);
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$createProduct = static function (string $sku, string $name, array $categoryIds) use (
    $productFactory,
    $productRepository
) {
    $product = $productFactory->create();
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId(4)
        ->setStoreId(Store::DEFAULT_STORE_ID)
        ->setWebsiteIds([1])
        ->setName($name)
        ->setSku($sku)
        ->setUrlKey($sku)
        ->setPrice(10)
        ->setWeight(1)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
        ->setCategoryIds($categoryIds)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED);

    return $productRepository->save($product);
};

$createProduct('gtm-multi-category', 'GTM Multi Category Product', [
    $defaultRootCategoryId,
    (int)$outside->getId(),
    (int)$inactive->getId(),
    (int)$parent->getId(),
    (int)$child->getId(),
    (int)$grandchild->getId(),
    (int)$extra1->getId(),
    (int)$extra2->getId(),
    (int)$extra3->getId(),
    (int)$storeDisabled->getId(),
]);

$createProduct('gtm-no-usable-category', 'GTM No Usable Category Product', [
    $defaultRootCategoryId,
    (int)$outside->getId(),
    (int)$inactive->getId(),
]);
