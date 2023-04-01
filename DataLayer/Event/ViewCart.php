<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartValue;
use Yireo\GoogleTagManager2\DataLayer\Tag\CurrencyCode;

class ViewCart implements EventInterface
{
    private CartItems $cartItems;
    private CartValue $cartValue;
    private CurrencyCode $currencyCode;
    private RequestInterface $request;
    private Config $config;

    /**
     * @param CartItems $cartItems
     * @param CartValue $cartValue
     * @param CurrencyCode $currencyCode
     * @param RequestInterface $request
     * @param Config $config
     */
    public function __construct(
        CartItems $cartItems,
        CartValue $cartValue,
        CurrencyCode $currencyCode,
        RequestInterface $request,
        Config $config
    ) {
        $this->cartItems = $cartItems;
        $this->cartValue = $cartValue;
        $this->currencyCode = $currencyCode;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @return string[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function get(): array
    {
        return [
            'meta' => [
                'cacheable' => true,
                'allowed_pages' => $this->getAllowedPages(),
            ],
            'event' => 'view_cart',
            'ecommerce' => [
                'currency' => $this->currencyCode->get(),
                'value' => $this->cartValue->get(),
                'items' => $this->cartItems->get()
            ]
        ];
    }

    /**
     * @return string[]
     */
    private function getAllowedPages(): array
    {
        if ($this->config->showViewCartEventEverywhere()) {
            return [];
        }

        return ['/checkout/cart/'];
    }
}
