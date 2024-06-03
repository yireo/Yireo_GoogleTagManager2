<?php

declare(strict_types=1);

namespace Tagging\GTM\Model;

use Tagging\GTM\Api\OrderNotesInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;

class OrderNotes implements OrderNotesInterface
{
    protected $checkoutSession;
    protected $logger;
    protected $json;

    public function __construct(CheckoutSession $checkoutSession, LoggerInterface $logger, Json $json)
    {
        $this->json = $json;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
    }

    public function saveData($jsonData)
    {
        try {
            $jsonData['ip'] = $_SERVER['REMOTE_ADDR'];
            $this->checkoutSession->setData('trytagging_marketing', $this->json->serialize($jsonData));
            return 'Data saved in checkout session';
        } catch (\Exception $e) {
            $this->logger->error("OrderNotes: " . $e->getMessage());
            return "Error while processing request";
        }
    }
}
