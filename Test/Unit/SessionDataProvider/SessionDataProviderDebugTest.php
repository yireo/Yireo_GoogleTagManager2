<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\SessionDataProvider;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Logger\Debugger;
use Yireo\GoogleTagManager2\SessionDataProvider\CheckoutSessionDataProvider;
use Yireo\GoogleTagManager2\SessionDataProvider\CustomerSessionDataProvider;

class SessionDataProviderDebugTest extends TestCase
{
    public function testCheckoutSessionDataProviderOnlyLogsIdentifier(): void
    {
        $debugger = $this->createMock(Debugger::class);
        $debugger->expects($this->once())->method('debug')
            ->with('CheckoutSessionDataProvider::add(): purchase_event', $this->isNull());

        $provider = new CheckoutSessionDataProvider($this->createMock(CheckoutSession::class), $debugger);
        $provider->add('purchase_event', ['email' => 'john@example.com']);
    }

    public function testCustomerSessionDataProviderOnlyLogsIdentifier(): void
    {
        $debugger = $this->createMock(Debugger::class);
        $debugger->expects($this->once())->method('debug')
            ->with('CustomerSessionDataProvider::add(): login_event', $this->isNull());

        $provider = new CustomerSessionDataProvider($this->createMock(CustomerSession::class), $debugger);
        $provider->add('login_event', ['email' => 'john@example.com']);
    }
}
