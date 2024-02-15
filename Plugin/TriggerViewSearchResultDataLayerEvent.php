<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Plugin;

use Magento\CatalogSearch\Controller\Result\Index;
use Magento\Framework\Controller\AbstractResult;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\ViewSearchResult as ViewSearchResultEvent;

class TriggerViewSearchResultDataLayerEvent
{
    private ViewSearchResultEvent $viewSearchResultEvent;
    private CustomerSessionDataProviderInterface $customerSessionDataProvider;

    public function __construct(
        ViewSearchResultEvent $viewSearchResultEvent,
        CustomerSessionDataProviderInterface $customerSessionDataProvider
    ) {
        $this->viewSearchResultEvent = $viewSearchResultEvent;
        $this->customerSessionDataProvider = $customerSessionDataProvider;
    }

    public function afterExecute(Index $subject, AbstractResult $result): ?AbstractResult
    {
        $searchTerm = $subject->getRequest()->getParam('q');
        $this->viewSearchResultEvent->setSearchTerm($searchTerm);
        $this->customerSessionDataProvider->add('view_search_result', $this->viewSearchResultEvent->get());
        return $result;
    }
}
