<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide;

use Magento\Framework\Serialize\Serializer\Json;
use Throwable;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\ServerSide\Context\Context;
use Yireo\GoogleTagManager2\ServerSide\Dispatch\DispatcherResolver;
use Yireo\GoogleTagManager2\ServerSide\Outbox\OutboxRepository;
use Yireo\GoogleTagManager2\ServerSide\Payload\MeasurementProtocolMapper;

class EventPublisher
{
    private Config $config;
    private MeasurementProtocolMapper $mapper;
    private OutboxRepository $outboxRepository;
    private DispatcherResolver $dispatcherResolver;
    private Json $json;
    private Debugger $debugger;

    public function __construct(
        Config $config,
        MeasurementProtocolMapper $mapper,
        OutboxRepository $outboxRepository,
        DispatcherResolver $dispatcherResolver,
        Json $json,
        Debugger $debugger
    ) {
        $this->config = $config;
        $this->mapper = $mapper;
        $this->outboxRepository = $outboxRepository;
        $this->dispatcherResolver = $dispatcherResolver;
        $this->json = $json;
        $this->debugger = $debugger;
    }

    public function publish(array $dataLayerEvent, int $storeId, string $dedupKey, ?Context $context): ?int
    {
        try {
            return $this->doPublish($dataLayerEvent, $storeId, $dedupKey, $context);
        } catch (Throwable $exception) {
            $this->debugger->debug('Server-side event "' . $dedupKey . '" could not be published', $exception->getMessage());
            return null;
        }
    }

    private function doPublish(array $dataLayerEvent, int $storeId, string $dedupKey, ?Context $context): ?int
    {
        $eventName = (string)($dataLayerEvent['event'] ?? '');
        if ($eventName === '' || false === $this->config->isEventEnabled($eventName, $storeId)) {
            return null;
        }

        if ($context === null) {
            $this->debugger->debug('Server-side event "' . $dedupKey . '" skipped: no context');
            return null;
        }

        if ($this->config->requireConsent($storeId) && false === $context->hasConsent('analytics')) {
            $this->debugger->debug('Server-side event "' . $dedupKey . '" skipped: no analytics consent');
            return null;
        }

        if ($this->outboxRepository->exists($dedupKey)) {
            return null;
        }

        $payload = $this->json->serialize($this->mapper->map($dataLayerEvent, $context));
        $outboxId = $this->outboxRepository->add($storeId, $eventName, $dedupKey, $payload);
        if ($outboxId === null) {
            return null;
        }

        $this->dispatcherResolver->resolve()->dispatch($outboxId);
        return $outboxId;
    }
}
