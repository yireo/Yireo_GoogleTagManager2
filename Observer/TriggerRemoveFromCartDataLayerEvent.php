<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface;

class TriggerRemoveFromCartDataLayerEvent implements ObserverInterface
{
    private CheckoutSessionDataProviderInterface $checkoutSessionDataProvider;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        CheckoutSessionDataProviderInterface $checkoutSessionDataProvider,
        ProductRepositoryInterface $productRepository
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer)
    {
        /** @var CartItemInterface $quoteItem */
        $quoteItem = $observer->getData('quote_item');
        $product = $this->productRepository->get($quoteItem->getSku());

        $this->checkoutSessionDataProvider->append([
           'event' => 'removeFromCart',
            'ecommerce' => [
                'remove' => [
                    'products' => [
                        [
                            'id' => $product->getId(),
                            'name' => $quoteItem->getName(),
                            'sku' => $quoteItem->getSku(),
                            'price' => $quoteItem->getPrice(),
                            'quantity' => $quoteItem->getQty(),
                        ]
                    ]
                ]
            ]
        ]);
    }
}
