<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use RuntimeException;
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
    private array $products = [];

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
     * @throws NotUsingSetProductSkusException
     */
    public function getProducts(): array
    {
        if (!empty($this->products)) {
            return $this->products;
        }

        if (empty($this->productSkus)) {
            throw new NotUsingSetProductSkusException('Using getProducts() before setProductSkus()');
        }

        $this->searchCriteriaBuilder->addFilter('sku', $this->productSkus, 'IN');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $this->products = $this->productRepository->getList($searchCriteria)->getItems();

        return $this->products;
    }

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getBySku(string $sku): ProductInterface
    {
        if (!in_array($sku, $this->productSkus)) {
            $this->reset();
            $this->setProductSkus([$sku]);
        }

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
        $this->products = [];
    }
}
