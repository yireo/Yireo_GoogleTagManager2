<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\CatalogSearch\Controller\Result\Index;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\ViewSearchResult as ViewSearchResultEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class TriggerViewSearchResultDataLayerEvent
{
    private ViewSearchResultEvent $viewSearchResultEvent;
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;
    private LoggerInterface $logger;

    public function __construct(
        ViewSearchResultEvent $viewSearchResultEvent,
        CustomerSessionDataProviderInterface $customerSessionDataProvider,
        LoggerInterface $logger
    ) {
        $this->viewSearchResultEvent = $viewSearchResultEvent;
        $this->customerSessionDataProvider = $customerSessionDataProvider;
        $this->logger = $logger;
    }

    public function afterExecute(Index $subject, $return)
    {
        try {
            $searchTerm = $subject->getRequest()->getParam('q');

            if (is_string($searchTerm) || $searchTerm === null) {
                $this->viewSearchResultEvent->setSearchTerm($searchTerm);
                $this->customerSessionDataProvider->add('view_search_result', $this->viewSearchResultEvent->get());
            }
        } catch (Throwable $exception) {
            $this->logger->error('GTM data layer plugin ' . self::class . ' failed: ' . $exception->getMessage());
        }

        return $return;
    }
}
