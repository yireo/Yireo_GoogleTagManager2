<?php declare(strict_types=1);

namespace Tagging\GTM\Plugin;

use Magento\CatalogSearch\Controller\Result\Index;
use Tagging\GTM\Api\CustomerSessionDataProviderInterface;
use Tagging\GTM\DataLayer\Event\ViewSearchResult as ViewSearchResultEvent;

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

    public function afterExecute(Index $subject, $return)
    {
        $searchTerm = $subject->getRequest()->getParam('q');
        $this->viewSearchResultEvent->setSearchTerm($searchTerm);
        $this->customerSessionDataProvider->add('view_search_result', $this->viewSearchResultEvent->get());
        return $return;
    }
}
