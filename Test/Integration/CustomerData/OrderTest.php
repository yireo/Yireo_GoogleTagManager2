<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\CustomerData;

use Magento\Directory\Model\Currency;
use Mockery;
use PHPUnit\Framework\TestCase;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Yireo\GoogleTagManager2\CustomerData\Order as TestTarget;

/**
 * Class OrderTest
 *
 * @package Yireo\GoogleTagManager2\Test\Integration\CustomerData
 */
class OrderTest extends TestCase
{
    /**
     * @var array
     */
    private $sectionDataStub = [];

    /**
     * Test whether getSectionData() returns empty by default
     */
    public function testGetEmptySectionData()
    {
        $checkoutSession = $this->getCheckoutSessionMock();
        $scopeConfig = $this->getScopeConfigMock();
        $currency = $this->getCurrencyMock();

        $testTarget = new TestTarget($checkoutSession, $scopeConfig, $currency);
        $sectionData = $testTarget->getSectionData();

        $this->setSectionDataStub([]);
        $this->assertSame($sectionData, $this->getSectionDataStub());
    }

    /**
     * Test whether getSectionData() returns data with an order
     *
     * @magentoDataFixture Magento/Sales/_files/order_pending_payment.php
     */
    public function testGetSectionDataWithOrderFixture()
    {
        $checkoutSession = $this->getObjectManager()->get(CheckoutSession::class);
        $checkoutSession->setLastOrderId(100000001);

        $scopeConfig = $this->getObjectManager()->get(ScopeConfigInterface::class);
        $currency = $this->getObjectManager()->get(Currency::class);

        $testTarget = new TestTarget($checkoutSession, $scopeConfig, $currency);
        $sectionData = $testTarget->getSectionData();

        $this->setSectionDataStub([
            'transactionEntity' => 'ORDER',
            'transactionId' => '',
        ]);

        $sectionDataStub = $this->getSectionDataStub();
        $this->assertSame($sectionData['transactionEntity'], $sectionDataStub['transactionEntity']);
        $this->assertSame($sectionData['transactionId'], $sectionDataStub['transactionId']);
    }

    /**
     * @return CheckoutSession
     */
    private function getCheckoutSessionMock(): CheckoutSession
    {
        $mock = Mockery::mock(CheckoutSession::class);
        return $mock;
    }

    /**
     * @return ScopeConfigInterface
     */
    private function getScopeConfigMock(): ScopeConfigInterface
    {
        $mock = Mockery::mock(ScopeConfigInterface::class);
        return $mock;
    }

    /**
     * @return Currency
     */
    private function getCurrencyMock(): Currency
    {
        $mock = Mockery::mock(Currency::class);
        return $mock;
    }

    /**
     * @return array
     */
    private function getSectionDataStub(): array
    {
        return $this->sectionDataStub;
    }

    /**
     * @param array $sectionDataStub
     */
    private function setSectionDataStub(array $sectionDataStub)
    {
        $this->sectionDataStub = $sectionDataStub;
    }

    /**
     * @return ObjectManagerInterface
     */
    private function getObjectManager()
    {
        return Bootstrap::getObjectManager();
    }
}