<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

trait GetProduct
{
    /**
     * @param int $productId
     * @return ProductInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductById(int $productId): ProductInterface
    {
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        return $productRepository->getById($productId);
    }

    /**
     * @param string $productSku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProductBySku(string $productSku): ProductInterface
    {
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        return $productRepository->get($productSku);
    }
}
