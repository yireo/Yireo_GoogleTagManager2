<?php declare(strict_types=1);
namespace AdPage\GTM\Observer;

use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

class AddAdditionalLayoutHandles implements ObserverInterface
{
    private RequestInterface $request;
    private LayoutInterface $layout;

    public function __construct(
        Request $request,
        LayoutInterface $layout
    ) {
        $this->request = $request;
        $this->layout = $layout;
    }

    public function execute(Observer $observer)
    {
        $handles = [];
        $handles[] = 'AdPage_GTM';
        $handles[] = 'AdPage_GTM_'.$this->getSystemPath();

        foreach ($handles as $handle) {
            $this->layout->getUpdate()->addHandle($handle);
        }
    }

    private function getSystemPath(): string
    {
        $parts = explode('/', $this->request->getFullActionName()); // @phpstan-ignore-line
        return implode('_', array_slice($parts, 0, 3));
    }
}
