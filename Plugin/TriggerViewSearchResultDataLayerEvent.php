<?php declare(strict_types=1);

namespace AdPage\GTM\Plugin;

use Magento\CatalogSearch\Controller\Result\Index;
use AdPage\GTM\Api\CustomerSessionDataProviderInterface;
use AdPage\GTM\DataLayer\Event\ViewSearchResult as ViewSearchResultEvent;

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

    public function afterExecute(Index $subject): void
    {
        $searchTerm = $subject->getRequest()->getParam('q');
        $this->viewSearchResultEvent->setSearchTerm($searchTerm);
        $this->customerSessionDataProvider->add('view_search_result', $this->viewSearchResultEvent->get());
    }
}
