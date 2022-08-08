<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\DataLayer\Tag\AddTagInterface;

class Products implements AddTagInterface
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

    public function addData(): array
    {
        $products = [];
        foreach ($this->checkoutSession->getLastRealOrder()->getAllItems() as $item) {
            $products[] = $this->productDataMapper->mapByProduct($item->getProduct(), 'transaction');
        }

        return $products;
    }
}
