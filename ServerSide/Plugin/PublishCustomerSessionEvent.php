<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Plugin;

use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\ServerSide\SessionEventPublisher;

class PublishCustomerSessionEvent
{
    private SessionEventPublisher $sessionEventPublisher;

    public function __construct(
        SessionEventPublisher $sessionEventPublisher
    ) {
        $this->sessionEventPublisher = $sessionEventPublisher;
    }

    public function afterAdd(CustomerSessionDataProviderInterface $subject, $result, string $identifier, array $data)
    {
        $this->sessionEventPublisher->publish($data);
        return $result;
    }
}
