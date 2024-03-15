<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

class CheckoutOnepageSuccessTest extends PageTestCase
{
    private ?CheckoutSession $checkoutSession = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->checkoutSession = $this->_objectManager->create(CheckoutSession::class);
        $this->_objectManager->addSharedInstance($this->checkoutSession, CheckoutSession::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(CheckoutSession::class);
        parent::tearDown();
    }

    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Checkout/_files/orders.php
     * @return void
     */
    public function testSuccessPage()
    {
        $order = $this->getOrder();
        $checkoutSession = $this->_objectManager->get(CheckoutSession::class);
        $checkoutSession->setLastRealOrderId($order->getIncrementId());
        $checkoutSession->setLastRealOrder($order);

        $pageFactory = $this->_objectManager->create(PageFactory::class);
        $page = $pageFactory->create();
        $page->addHandle([
            'default',
            'checkout_onepage_success',
        ]);
        $page->getLayout()->generateXml();
        $page->getLayout()->generateElements();

        $block = $page->getLayout()->getBlock('yireo_googletagmanager2.data-layer');
        $this->assertNotFalse($block);

        $this->assertDataLayerEquals('Success Page', 'page_title');
        $this->assertDataLayerContains('sha256_email_address');
        $this->assertDataLayerContains('sha256_customer_telephone');
    }

    private function getOrder(): OrderInterface
    {
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->setPageSize(1);
        $searchResult = $orderRepository->getList($searchCriteriaBuilder->create());
        $orders = $searchResult->getItems();
        $this->assertNotEmpty($orders);
        $order = array_pop($orders);
        $this->assertNotEmpty($order);
        $this->assertNotEmpty($order->getId());
        return $order;
    }
}
