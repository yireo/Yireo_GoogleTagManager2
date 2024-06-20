<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\GoogleTagManager2\Util\SecureHtmlRendererStub;
use Magento\Framework\View\Element\Template;

class FixNonExistingSecureRenderer implements ObserverInterface
{
    private SecureHtmlRendererStub $secureHtmlRendererStub;

    public function __construct(
        SecureHtmlRendererStub $secureHtmlRendererStub
    )
    {
        $this->secureHtmlRendererStub = $secureHtmlRendererStub;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $block = $event->getBlock();
        if (false === $block instanceof Template) {
            return;
        }

        if (empty($block->getNameInLayout())) {
            return;
        }

        if (false === strstr($block->getNameInLayout(), 'yireo_googletagmanager2')) {
            return;
        }

        if (class_exists('\Magento\Framework\View\Helper\SecureHtmlRenderer')) {
            return;
        }

        $block->assign('secureRenderer', $this->secureHtmlRendererStub);
    }
}
