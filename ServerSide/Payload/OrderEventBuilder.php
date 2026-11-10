<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Payload;

use Magento\Framework\App\Area;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\PurchaseFactory;

class OrderEventBuilder
{
    private PurchaseFactory $purchaseFactory;
    private CreditmemoRefund $creditmemoRefund;
    private Emulation $emulation;
    private StoreManagerInterface $storeManager;

    public function __construct(
        PurchaseFactory $purchaseFactory,
        CreditmemoRefund $creditmemoRefund,
        Emulation $emulation,
        StoreManagerInterface $storeManager
    ) {
        $this->purchaseFactory = $purchaseFactory;
        $this->creditmemoRefund = $creditmemoRefund;
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
    }

    public function buildPurchase(OrderInterface $order): array
    {
        return $this->emulate((int)$order->getStoreId(), function () use ($order) {
            return $this->purchaseFactory->create()->setOrder($order)->setServerSide()->get();
        });
    }

    public function buildRefund(CreditmemoInterface $creditmemo, OrderInterface $order): array
    {
        return $this->emulate((int)$order->getStoreId(), function () use ($creditmemo, $order) {
            return $this->creditmemoRefund->build($creditmemo, $order);
        });
    }

    private function emulate(int $storeId, callable $callback): array
    {
        if ($storeId === (int)$this->storeManager->getStore()->getId()) {
            return $callback();
        }

        $this->emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        try {
            return $callback();
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }
    }
}
