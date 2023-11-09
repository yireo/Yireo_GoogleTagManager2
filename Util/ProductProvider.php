<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use RuntimeException;

class ProductProvider
{
    private ProductRepositoryInterface $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var string[]
     */
    private array $productSkus = [];

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
    public function setProductSkus(array $productSkus)
    {
        $this->productSkus = $productSkus;
    }

    /**
     * @return ProductInterface[]
     */
    public function getProducts(): array
    {
        static $products = null;
        if (!empty($products)) {
            return $products;
        }

        if (empty($this->productSkus)) {
            throw new RuntimeException('Using getProducts() before setProductSkus()');
        }

        $this->searchCriteriaBuilder->addFilter('sku', $this->productSkus, 'IN');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        return $products;
    }

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getBySku(string $sku): ProductInterface
    {
        foreach ($this->getProducts() as $product) {
            if ($product->getSku() === $sku) {
                return $product;
            }
        }

        throw new NoSuchEntityException(__('No product with sku "'.$sku.'"'));
    }


    public function reset()
    {
        $this->productSkus = [];
    }
}
