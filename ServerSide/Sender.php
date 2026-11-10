<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide;

use Magento\Framework\HTTP\Client\CurlFactory;
use Throwable;
use Yireo\GoogleTagManager2\Logger\Debugger;

class Sender
{
    private const TIMEOUT = 3;

    private CurlFactory $curlFactory;
    private Config $config;
    private Debugger $debugger;

    public function __construct(
        CurlFactory $curlFactory,
        Config $config,
        Debugger $debugger
    ) {
        $this->curlFactory = $curlFactory;
        $this->config = $config;
        $this->debugger = $debugger;
    }

    public function send(int $storeId, string $payload, ?bool $validation = null): SendResult
    {
        $validation = $validation ?? $this->config->isValidationMode($storeId);
        $endpoint = $this->config->getEndpointUrl($storeId, $validation);
        $url = $endpoint . '?' . http_build_query([
            'measurement_id' => $this->config->getMeasurementId($storeId),
            'api_secret' => $this->config->getApiSecret($storeId),
        ]);

        $curl = $this->curlFactory->create();
        $curl->setTimeout(self::TIMEOUT);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);
        $curl->addHeader('Content-Type', 'application/json');

        try {
            $curl->post($url, $payload);
            $status = (int)$curl->getStatus();
            $body = (string)$curl->getBody();
        } catch (Throwable $exception) {
            $this->debugger->debug('Server-side tracking request to ' . $endpoint . ' failed', $exception->getMessage());
            return new SendResult(false, 0, $exception->getMessage());
        }

        $success = $status >= 200 && $status < 300;
        if ($validation && $success && $this->hasValidationMessages($body)) {
            $success = false;
        }

        $this->debugger->debug('Server-side tracking request to ' . $endpoint . ' returned ' . $status, [
            'payload' => $payload,
            'response' => $body,
        ]);

        return new SendResult($success, $status, $body);
    }

    private function hasValidationMessages(string $body): bool
    {
        $data = json_decode($body, true);
        return is_array($data) && !empty($data['validationMessages']);
    }
}
