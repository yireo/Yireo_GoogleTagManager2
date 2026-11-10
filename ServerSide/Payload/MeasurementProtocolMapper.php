<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Payload;

use InvalidArgumentException;
use Yireo\GoogleTagManager2\ServerSide\Context\Context;

class MeasurementProtocolMapper
{
    private const FLOAT_PARAMS = ['value', 'tax', 'shipping'];
    private const FLOAT_ITEM_PARAMS = ['price', 'discount'];
    private const INT_ITEM_PARAMS = ['quantity', 'index'];
    private const EXCLUDED_PARAMS = ['value_adjusted', 'value_adjusted_currency', 'payment_method'];
    private const MAX_PARAMS = 25;
    private const MAX_VALUE_LENGTH = 100;

    public function map(array $dataLayerEvent, Context $context, ?int $timestampMicros = null): array
    {
        $eventName = (string)($dataLayerEvent['event'] ?? '');
        if (false === (bool)preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,39}$/', $eventName)) {
            throw new InvalidArgumentException('Invalid event name "' . $eventName . '"');
        }

        $params = $this->getParams($dataLayerEvent);
        if ($context->getSessionId() !== null) {
            $params['session_id'] = $context->getSessionId();
        }

        $params['engagement_time_msec'] = 1;

        $body = [
            'client_id' => $context->getClientId(),
        ];

        if ($context->getUserId() !== null && $context->getUserId() !== '') {
            $body['user_id'] = $context->getUserId();
        }

        $body['timestamp_micros'] = $timestampMicros ?? (int)floor(microtime(true) * 1000000);

        if ($context->getConsent() !== null) {
            $marketing = $context->hasConsent('marketing') ? 'GRANTED' : 'DENIED';
            $body['consent'] = [
                'ad_user_data' => $marketing,
                'ad_personalization' => $marketing,
            ];
        }

        $body['events'] = [
            [
                'name' => $eventName,
                'params' => $params,
            ],
        ];

        return $body;
    }

    private function getParams(array $dataLayerEvent): array
    {
        $params = [];
        foreach ($dataLayerEvent as $key => $value) {
            if (in_array($key, ['event', 'ecommerce'], true) || false === is_scalar($value)) {
                continue;
            }

            $params[$key] = $value;
        }

        $ecommerce = $dataLayerEvent['ecommerce'] ?? [];
        if (is_array($ecommerce)) {
            foreach ($ecommerce as $key => $value) {
                if ($key === 'items' && is_array($value)) {
                    $params['items'] = $this->getItems($value);
                    continue;
                }

                if (is_scalar($value)) {
                    $params[$key] = $value;
                }
            }
        }

        $result = [];
        foreach ($params as $key => $value) {
            if (in_array($key, self::EXCLUDED_PARAMS, true) || $value === '' || $value === null) {
                continue;
            }

            if (in_array($key, self::FLOAT_PARAMS, true)) {
                $value = (float)$value;
            } elseif (is_string($value)) {
                $value = mb_substr($value, 0, self::MAX_VALUE_LENGTH);
            }

            $result[(string)$key] = $value;
        }

        return array_slice($result, 0, self::MAX_PARAMS, true);
    }

    private function getItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (false === is_array($item)) {
                continue;
            }

            $resultItem = [];
            foreach ($item as $key => $value) {
                if (false === is_scalar($value) || $value === '') {
                    continue;
                }

                if (in_array($key, self::FLOAT_ITEM_PARAMS, true)) {
                    $value = (float)$value;
                } elseif (in_array($key, self::INT_ITEM_PARAMS, true)) {
                    $value = (int)$value;
                } elseif (is_string($value)) {
                    $value = mb_substr($value, 0, self::MAX_VALUE_LENGTH);
                }

                $resultItem[(string)$key] = $value;
            }

            if (isset($resultItem['item_id']) || isset($resultItem['item_name'])) {
                $result[] = $resultItem;
            }
        }

        return array_slice($result, 0, 200);
    }
}
