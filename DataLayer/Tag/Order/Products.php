<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;

class Products implements TagInterface
{
    private ProductDataMapper $productDataMapper;
    private CheckoutSession $checkoutSession;

    public function __construct(
        ProductDataMapper $productDataMapper,
        CheckoutSession $checkoutSession
    ) {
        $this->productDataMapper = $productDataMapper;
        $this->checkoutSession = $checkoutSession;
    }

    public function get(): array
    {
        $products = [];
        foreach ($this->checkoutSession->getLastRealOrder()->getAllItems() as $item) {
            $products[] = $this->productDataMapper->mapByProduct($item->getProduct(), 'transaction');
        }

        return $products;
    }
}
