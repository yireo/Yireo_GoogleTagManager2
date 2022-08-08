<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class GetCurrentProduct
{
    private RequestInterface $request;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function get(): ProductInterface
    {
        // @todo: Check for URL

        $productId = (int)$this->request->getParam('id');
        return $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
    }
}
