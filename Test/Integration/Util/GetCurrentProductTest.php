<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Util;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Util\GetCurrentProduct;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class GetCurrentProductTest extends TestCase
{
    protected function tearDown(): void
    {
        Bootstrap::getObjectManager()->get(Registry::class)->unregister('current_product');
    }

    public function testEnabledVisibleProductIsResolvedFromRequest()
    {
        $product = $this->createProduct('gtm-current-visible');
        $this->assertSame((int)$product->getId(), (int)$this->getCurrentProduct($product->getId())->getId());
    }

    public function testProductFromRegistryIsPreferred()
    {
        $product = $this->createProduct('gtm-current-registry');
        $otherProduct = $this->createProduct('gtm-current-other');
        Bootstrap::getObjectManager()->get(Registry::class)->register('current_product', $product);

        $this->assertSame((int)$product->getId(), (int)$this->getCurrentProduct($otherProduct->getId())->getId());
    }

    public function testDisabledProductIsNotResolvedFromRequest()
    {
        $product = $this->createProduct('gtm-current-disabled', Status::STATUS_DISABLED);
        $this->expectException(NoSuchEntityException::class);
        $this->getCurrentProduct($product->getId());
    }

    public function testNotVisibleProductIsNotResolvedFromRequest()
    {
        $product = $this->createProduct(
            'gtm-current-not-visible',
            Status::STATUS_ENABLED,
            Visibility::VISIBILITY_NOT_VISIBLE
        );
        $this->expectException(NoSuchEntityException::class);
        $this->getCurrentProduct($product->getId());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     */
    public function testProductOfOtherWebsiteIsNotResolvedFromRequest()
    {
        $websiteId = (int)Bootstrap::getObjectManager()->get(WebsiteRepositoryInterface::class)->get('test')->getId();
        $product = $this->createProduct(
            'gtm-current-other-website',
            Status::STATUS_ENABLED,
            Visibility::VISIBILITY_BOTH,
            [$websiteId]
        );
        $this->expectException(NoSuchEntityException::class);
        $this->getCurrentProduct($product->getId());
    }

    public function testMissingProductIdIsNotResolved()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->getCurrentProduct(0);
    }

    private function getCurrentProduct($productId): ProductInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(RequestInterface::class);
        $request->setParams(['id' => (string)$productId]);

        return $objectManager->create(GetCurrentProduct::class)->get();
    }

    private function createProduct(
        string $sku,
        int $status = Status::STATUS_ENABLED,
        int $visibility = Visibility::VISIBILITY_BOTH,
        array $websiteIds = [1]
    ): ProductInterface {
        $objectManager = Bootstrap::getObjectManager();
        $product = $objectManager->get(ProductInterfaceFactory::class)->create();
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId(4)
            ->setWebsiteIds($websiteIds)
            ->setName($sku)
            ->setSku($sku)
            ->setUrlKey($sku)
            ->setPrice(10)
            ->setVisibility($visibility)
            ->setStatus($status)
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1]);

        return $objectManager->get(ProductRepositoryInterface::class)->save($product);
    }
}
