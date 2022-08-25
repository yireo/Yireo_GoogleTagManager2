<?php declare(strict_types=1);
namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\Config\Config;

class AddAdditionalLayoutHandles implements ObserverInterface
{
    private RequestInterface $request;
    private LayoutInterface $layout;
    private Config $config;

    public function __construct(
        Request $request,
        LayoutInterface $layout,
        Config $config
    ) {
        $this->request = $request;
        $this->layout = $layout;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        // @todo: Make this configurable
        $handles = [];
        $handles[] = 'yireo_googletagmanager2_enhanced_ecommerce';
        $handles[] = 'yireo_googletagmanager2_enhanced_ecommerce_'.$this->getSystemPath();

        foreach ($handles as $handle) {
            $this->layout->getUpdate()->addHandle($handle);
        }
    }

    private function getSystemPath(): string
    {
        $parts = explode('/', $this->request->getFullActionName());
        return implode('_', array_slice($parts, 0, 3));
    }
}
