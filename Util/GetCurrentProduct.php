<?php
declare(strict_types=1);

namespace AdPage\GTM\Util;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class GetCurrentProduct
{
    private RequestInterface $request;
    private ProductRepositoryInterface $productRepository;
    private StoreManagerInterface $storeManager;
    
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
        $productId = (int)$this->request->getParam('id');
        if ($this->request->getActionName() === 'configure' || empty($productId)) {
            $productId = (int)$this->request->getParam('product_id');
        }
        
        return $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
    }
}
