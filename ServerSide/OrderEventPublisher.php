<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide;

use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Throwable;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\ServerSide\Context\Context;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextCollector;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextRepository;
use Yireo\GoogleTagManager2\ServerSide\Outbox\OutboxRepository;
use Yireo\GoogleTagManager2\ServerSide\Payload\OrderEventBuilder;

class OrderEventPublisher
{
    private const STOREFRONT_AREAS = [
        Area::AREA_FRONTEND,
        Area::AREA_WEBAPI_REST,
        Area::AREA_WEBAPI_SOAP,
        Area::AREA_GRAPHQL,
    ];

    private Config $config;
    private EventPublisher $eventPublisher;
    private OrderEventBuilder $orderEventBuilder;
    private ContextRepository $contextRepository;
    private ContextCollector $contextCollector;
    private OutboxRepository $outboxRepository;
    private AppState $appState;
    private Debugger $debugger;

    public function __construct(
        Config $config,
        EventPublisher $eventPublisher,
        OrderEventBuilder $orderEventBuilder,
        ContextRepository $contextRepository,
        ContextCollector $contextCollector,
        OutboxRepository $outboxRepository,
        AppState $appState,
        Debugger $debugger
    ) {
        $this->config = $config;
        $this->eventPublisher = $eventPublisher;
        $this->orderEventBuilder = $orderEventBuilder;
        $this->contextRepository = $contextRepository;
        $this->contextCollector = $contextCollector;
        $this->outboxRepository = $outboxRepository;
        $this->appState = $appState;
        $this->debugger = $debugger;
    }

    public function publishPurchase(OrderInterface $order): ?int
    {
        $storeId = (int)$order->getStoreId();
        $dedupKey = 'purchase:' . $order->getIncrementId();
        if (false === $this->config->isEventEnabled('purchase', $storeId) || $this->outboxRepository->exists($dedupKey)) {
            return null;
        }

        try {
            $eventData = $this->orderEventBuilder->buildPurchase($order);
        } catch (Throwable $exception) {
            $this->debugger->debug('Server-side purchase for order ' . $order->getIncrementId() . ' failed', $exception->getMessage());
            return null;
        }

        if (empty($eventData)) {
            return null;
        }

        return $this->eventPublisher->publish($eventData, $storeId, $dedupKey, $this->resolveContext($order));
    }

    public function publishRefund(CreditmemoInterface $creditmemo, OrderInterface $order): ?int
    {
        $storeId = (int)$order->getStoreId();
        $dedupKey = 'refund:' . $creditmemo->getEntityId();
        if (false === $this->config->isEventEnabled('refund', $storeId) || $this->outboxRepository->exists($dedupKey)) {
            return null;
        }

        try {
            $eventData = $this->orderEventBuilder->buildRefund($creditmemo, $order);
        } catch (Throwable $exception) {
            $this->debugger->debug('Server-side refund for order ' . $order->getIncrementId() . ' failed', $exception->getMessage());
            return null;
        }

        return $this->eventPublisher->publish($eventData, $storeId, $dedupKey, $this->resolveContext($order));
    }

    public function storeContext(OrderInterface $order): void
    {
        if (false === $this->isStorefront() || false === $this->config->isEnabled((int)$order->getStoreId())) {
            return;
        }

        $quoteId = (int)$order->getQuoteId();
        if ($quoteId <= 0) {
            return;
        }

        $context = $this->contextCollector->collect((int)$order->getStoreId());
        $this->contextRepository->save($quoteId, (int)$order->getEntityId(), $context);
    }

    private function resolveContext(OrderInterface $order): Context
    {
        $context = $this->contextRepository->getByOrderId((int)$order->getEntityId())
            ?? $this->contextRepository->getByQuoteId((int)$order->getQuoteId());
        if ($context instanceof Context) {
            return $context;
        }

        if ($this->isStorefront()) {
            return $this->contextCollector->collect((int)$order->getStoreId());
        }

        $customerId = (int)$order->getCustomerId();
        return $this->contextCollector->createFallback($customerId > 0 ? (string)$customerId : null);
    }

    private function isStorefront(): bool
    {
        try {
            return in_array($this->appState->getAreaCode(), self::STOREFRONT_AREAS, true);
        } catch (LocalizedException $exception) {
            return false;
        }
    }
}
