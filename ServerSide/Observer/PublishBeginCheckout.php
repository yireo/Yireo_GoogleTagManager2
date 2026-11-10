<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\BeginCheckout;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\SessionEventPublisher;

class PublishBeginCheckout implements ObserverInterface
{
    private Config $config;
    private BeginCheckout $beginCheckout;
    private SessionEventPublisher $sessionEventPublisher;

    public function __construct(
        Config $config,
        BeginCheckout $beginCheckout,
        SessionEventPublisher $sessionEventPublisher
    ) {
        $this->config = $config;
        $this->beginCheckout = $beginCheckout;
        $this->sessionEventPublisher = $sessionEventPublisher;
    }

    public function execute(Observer $observer)
    {
        if (false === $this->config->isEventEnabled('begin_checkout')) {
            return;
        }

        $eventData = $this->beginCheckout->get();
        if (empty($eventData['ecommerce']['items'])) {
            return;
        }

        $this->sessionEventPublisher->publish($eventData);
    }
}
