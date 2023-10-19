<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Event;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use AdPage\GTM\Api\Data\EventInterface;
use AdPage\GTM\Config\Config;
use AdPage\GTM\DataLayer\Tag\Cart\CartItems;
use AdPage\GTM\DataLayer\Tag\Cart\CartValue;
use AdPage\GTM\DataLayer\Tag\CurrencyCode;

class ViewCart implements EventInterface
{
    private CartItems $cartItems;
    private CartValue $cartValue;
    private CurrencyCode $currencyCode;
    private Config $config;

    /**
     * @param CartItems $cartItems
     * @param CartValue $cartValue
     * @param CurrencyCode $currencyCode
     * @param Config $config
     */
    public function __construct(
        CartItems $cartItems,
        CartValue $cartValue,
        CurrencyCode $currencyCode,
        Config $config
    ) {
        $this->cartItems = $cartItems;
        $this->cartValue = $cartValue;
        $this->currencyCode = $currencyCode;
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
                'allowed_events' => $this->getAllowedEvents(),
            ],
            'event' => 'trytagging_view_cart',
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

    /**
     * @return string[]
     */
    private function getAllowedEvents(): array
    {
        if ($this->config->showViewMiniCartOnExpandOnly() && $this->config->showViewCartEventEverywhere()) {
            return ['minicart_collapse'];
        }

        return [];
    }
}
