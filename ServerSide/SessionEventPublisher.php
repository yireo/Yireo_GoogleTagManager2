<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Throwable;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextCollector;

class SessionEventPublisher
{
    private const EVENTS = [
        'add_to_cart',
        'remove_from_cart',
        'begin_checkout',
        'add_shipping_info',
        'add_payment_info',
        'login',
        'sign_up',
        'add_to_wishlist',
    ];

    private const QUOTE_EVENTS = [
        'begin_checkout',
        'add_shipping_info',
        'add_payment_info',
    ];

    private Config $config;
    private EventPublisher $eventPublisher;
    private ContextCollector $contextCollector;
    private StoreManagerInterface $storeManager;
    private CheckoutSession $checkoutSession;
    private IdentityGeneratorInterface $identityGenerator;
    private Json $json;
    private Debugger $debugger;

    public function __construct(
        Config $config,
        EventPublisher $eventPublisher,
        ContextCollector $contextCollector,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        IdentityGeneratorInterface $identityGenerator,
        Json $json,
        Debugger $debugger
    ) {
        $this->config = $config;
        $this->eventPublisher = $eventPublisher;
        $this->contextCollector = $contextCollector;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->identityGenerator = $identityGenerator;
        $this->json = $json;
        $this->debugger = $debugger;
    }

    public function publish(array $eventData): ?int
    {
        $eventName = (string)($eventData['event'] ?? '');
        if (false === in_array($eventName, self::EVENTS, true)) {
            return null;
        }

        try {
            $storeId = (int)$this->storeManager->getStore()->getId();
            if (false === $this->config->isEventEnabled($eventName, $storeId)) {
                return null;
            }

            return $this->eventPublisher->publish(
                $eventData,
                $storeId,
                $this->getDedupKey($eventName, $eventData),
                $this->contextCollector->collect($storeId)
            );
        } catch (Throwable $exception) {
            $this->debugger->debug('Server-side event "' . $eventName . '" could not be published', $exception->getMessage());
            return null;
        }
    }

    private function getDedupKey(string $eventName, array $eventData): string
    {
        if (in_array($eventName, self::QUOTE_EVENTS, true)) {
            $quoteId = (int)$this->checkoutSession->getQuoteId();
            if ($quoteId > 0) {
                return $eventName . ':' . $quoteId . ':' . sha1($this->json->serialize($eventData));
            }
        }

        return $eventName . ':' . $this->identityGenerator->generateId();
    }
}
