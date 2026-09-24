<?php declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\App\Bootstrap;

$bootstrapFile = null;
foreach ([getcwd(), __DIR__] as $startDirectory) {
    $directory = $startDirectory;
    while ($directory && $directory !== '/') {
        if (file_exists($directory . '/app/bootstrap.php')) {
            $bootstrapFile = $directory . '/app/bootstrap.php';
            break 2;
        }

        $directory = dirname($directory);
    }
}

if (null === $bootstrapFile) {
    fwrite(STDERR, 'No Magento root found; run this script from the Magento root' . PHP_EOL);
    exit(1);
}

require $bootstrapFile;

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('adminhtml');

$storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
$rootCategoryId = (int)$storeManager->getStore()->getRootCategoryId();

$categoryFactory = $objectManager->get(CategoryFactory::class);
$categoryResource = $objectManager->get(CategoryResource::class);

$categoryName = 'Playwright Category';
$category = $categoryFactory->create();
$categoryResource->load($category, $categoryName, 'name');

if (!$category->getId()) {
    $category = $categoryFactory->create();
    $category->setName($categoryName)
        ->setParentId($rootCategoryId)
        ->setIsActive(true)
        ->setIncludeInMenu(true)
        ->setUrlKey('playwright-category')
        ->setAttributeSetId($category->getDefaultAttributeSetId())
        ->setStoreId(0);
    $categoryResource->save($category);
    echo 'Created category ' . $category->getId() . PHP_EOL;
} else {
    echo 'Category already exists: ' . $category->getId() . PHP_EOL;
}

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$sku = 'playwright-simple-1';

try {
    $product = $productRepository->get($sku);
    echo 'Product already exists: ' . $product->getSku() . PHP_EOL;
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    /** @var ProductInterface|\Magento\Catalog\Model\Product $product */
    $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
    $product->setSku($sku)
        ->setName('Playwright Simple Product')
        ->setUrlKey('playwright-simple-product')
        ->setAttributeSetId(4)
        ->setTypeId(Type::TYPE_SIMPLE)
        ->setPrice(10.00)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setWebsiteIds([(int)$storeManager->getStore()->getWebsiteId()])
        ->setStockData([
            'use_config_manage_stock' => 1,
            'qty' => 1000,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]);

    $product = $productRepository->save($product);
    echo 'Created product ' . $product->getId() . PHP_EOL;
}

$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
$categoryLinkManagement->assignProductToCategories($sku, [(int)$category->getId()]);
echo 'Assigned product to category ' . $category->getId() . PHP_EOL;
