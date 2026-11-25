<?php declare(strict_types=1);
/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2019 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\Checkout\CustomerData\Cart as CustomerData;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Yireo\GoogleTagManager2\DataLayer\Event\ViewCart as ViewCartEvent;
use Yireo\GoogleTagManager2\DataLayer\Tag\Cart\CartItems;
use Yireo\GoogleTagManager2\SessionDataProvider\CheckoutSessionDataProvider;
use Psr\Log\LoggerInterface;
use Throwable;

class AddDataToCartSection
{
    private CheckoutCart $checkoutCart;
    private CheckoutSessionDataProvider $checkoutSessionDataProvider;
    private ViewCartEvent $viewCartEvent;
    private LoggerInterface $logger;

    /**
     * @param CheckoutCart $checkoutCart
     * @param CheckoutSessionDataProvider $checkoutSessionDataProvider
     * @param ViewCartEvent $viewCartEvent
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutCart $checkoutCart,
        CheckoutSessionDataProvider $checkoutSessionDataProvider,
        ViewCartEvent $viewCartEvent,
        LoggerInterface $logger
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->viewCartEvent = $viewCartEvent;
        $this->logger = $logger;
    }

    /**
     * @param CustomerData $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(CustomerData $subject, $result)
    {
        try {
            $quoteId = $this->checkoutCart->getQuote()->getId();
            if (empty($result) || !is_array($result) || empty($quoteId)) {
                return $result;
            }

            $gtmEvents = $this->checkoutSessionDataProvider->get();
            $gtmEvents['view_cart_event'] = $this->viewCartEvent->get();
            $this->checkoutSessionDataProvider->clear();
        } catch (Throwable $exception) {
            $this->logger->error('GTM data layer plugin ' . self::class . ' failed: ' . $exception->getMessage());
            return $result;
        }

        return array_merge($result, ['gtm' => [], 'gtm_events' => $gtmEvents]);
    }
}
