<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Exception\NotUsingSetProductSkusException;

class ProductProvider
{
    private ProductRepositoryInterface $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var string[]
     */
    private array $productSkus = [];

    /**
     * @var ProductInterface[]
     */
    private array $loadedProducts = [];

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string[] $productSkus
     * @return void
     */
    public function addProductSkus(array $productSkus)
    {
        $this->productSkus = array_unique(array_merge($this->productSkus, $productSkus));
    }

    /**
     * @return ProductInterface[]
     * @throws NotUsingSetProductSkusException
     * @throws NoSuchEntityException
     */
    public function getLoadedProducts(): array
    {
        if (empty($this->productSkus)) {
            throw new NotUsingSetProductSkusException('Using getProducts() before setProductSkus()');
        }

        $loadedProductSkus = array_diff($this->productSkus, array_keys($this->loadedProducts));
        if (count($loadedProductSkus) > 0) {
            foreach ($this->loadProductsBySkus($loadedProductSkus) as $product) {
                $this->loadedProducts[$product->getSku()] = $product;
            }
        }

        return $this->loadedProducts;
    }

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getBySku(string $sku): ProductInterface
    {
        $this->addProductSkus([$sku]);

        foreach ($this->getLoadedProducts() as $product) {
            if ($product->getSku() === $sku) {
                return $product;
            }
        }

        throw new NoSuchEntityException(__('No product with sku "'.$sku.'"'));
    }

    /**
     * @param array $productSkus
     * @return ProductInterface[]
     * @throws NoSuchEntityException
     */
    private function loadProductsBySkus(array $productSkus): array
    {
        $this->searchCriteriaBuilder->setPageSize(count($productSkus));
        $this->searchCriteriaBuilder->addFilter('sku', $productSkus, 'IN');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->productRepository->getList($searchCriteria)->getItems();
    }
}
