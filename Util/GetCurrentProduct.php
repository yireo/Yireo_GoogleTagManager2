<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class GetCurrentProduct
{
    private RequestInterface $request;
    private ProductRepositoryInterface $productRepository;
    private StoreManagerInterface $storeManager;
    private Registry $registry;

    public function __construct(
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
    }

    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function get(): ProductInterface
    {
        $product = $this->registry->registry('current_product');
        if ($product instanceof ProductInterface && $product->getId()) {
            return $product;
        }

        $productId = (int)$this->request->getParam('id');
        if ($this->request->getActionName() === 'configure' || empty($productId)) {
            $productId = (int)$this->request->getParam('product_id');
        }

        if ($productId < 1) {
            throw new NoSuchEntityException(__('No current product found'));
        }

        $store = $this->storeManager->getStore();
        $product = $this->productRepository->getById($productId, false, $store->getId());
        if (false === $this->isAvailable($product, (int)$store->getWebsiteId())) {
            throw new NoSuchEntityException(__('No current product found'));
        }

        return $product;
    }

    private function isAvailable(ProductInterface $product, int $websiteId): bool
    {
        if ((int)$product->getStatus() !== Status::STATUS_ENABLED) {
            return false;
        }

        if ((int)$product->getVisibility() === Visibility::VISIBILITY_NOT_VISIBLE) {
            return false;
        }

        /** @var Product $product */
        return in_array($websiteId, array_map('intval', (array)$product->getWebsiteIds()), true);
    }
}
