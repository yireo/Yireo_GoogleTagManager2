<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Plugin;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\CustomerData\Cart as CartCustomerData;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Controller\Result\Index as SearchResultController;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\CustomerData\Customer as CustomerCustomerData;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\AddPaymentInfo;
use Yireo\GoogleTagManager2\DataLayer\Event\AddShippingInfo;
use Yireo\GoogleTagManager2\DataLayer\Event\AddToCart;
use Yireo\GoogleTagManager2\DataLayer\Event\AddToWishlist;
use Yireo\GoogleTagManager2\DataLayer\Event\Login;
use Yireo\GoogleTagManager2\DataLayer\Event\Logout;
use Yireo\GoogleTagManager2\DataLayer\Event\Purchase;
use Yireo\GoogleTagManager2\DataLayer\Event\RemoveFromCart;
use Yireo\GoogleTagManager2\DataLayer\Event\SignUp;
use Yireo\GoogleTagManager2\DataLayer\Event\ViewSearchResult;
use Yireo\GoogleTagManager2\Observer\TriggerAddToCartDataLayerEvent;
use Yireo\GoogleTagManager2\Observer\TriggerAddToWishlistDataLayerEvent;
use Yireo\GoogleTagManager2\Observer\TriggerLoginDataLayerEvent;
use Yireo\GoogleTagManager2\Observer\TriggerLogoutDataLayerEvent;
use Yireo\GoogleTagManager2\Observer\TriggerPurchaseDataLayerEvent;
use Yireo\GoogleTagManager2\Observer\TriggerRemoveFromCartDataLayerEvent;
use Yireo\GoogleTagManager2\Observer\TriggerSignUpDataLayerEvent;
use Yireo\GoogleTagManager2\Plugin\AddDataToCartSection;
use Yireo\GoogleTagManager2\Plugin\AddDataToCustomerSection;
use Yireo\GoogleTagManager2\Plugin\TriggerAddGuestPaymentInfoDataLayerEvent;
use Yireo\GoogleTagManager2\Plugin\TriggerAddPaymentInfoDataLayerEvent;
use Yireo\GoogleTagManager2\Plugin\TriggerAddShippingInfoDataLayerEvent;
use Yireo\GoogleTagManager2\Plugin\TriggerViewSearchResultDataLayerEvent;

/**
 * @magentoAppArea frontend
 */
class ExceptionSafeTrackingTest extends TestCase
{
    public function testAddPaymentInfoPluginReturnsOrderIdWhenTrackingFails()
    {
        $plugin = $this->create(TriggerAddPaymentInfoDataLayerEvent::class, [
            'addPaymentInfo' => $this->createThrowingMock(AddPaymentInfo::class),
        ]);

        $subject = $this->createMock(PaymentInformationManagementInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $this->assertSame(42, $plugin->afterSavePaymentInformationAndPlaceOrder($subject, 42, 1, $payment));
    }

    public function testAddPaymentInfoPluginReturnsResultOfSavePaymentInformationWhenTrackingFails()
    {
        $plugin = $this->create(TriggerAddPaymentInfoDataLayerEvent::class, [
            'addPaymentInfo' => $this->createThrowingMock(AddPaymentInfo::class),
        ]);

        $subject = $this->createMock(PaymentInformationManagementInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $this->assertTrue($plugin->afterSavePaymentInformation($subject, true, 1, $payment));
    }

    public function testGuestPaymentInfoPluginReturnsOrderIdWhenTrackingFails()
    {
        $maskedQuoteIdToQuoteId = $this->createMock(MaskedQuoteIdToQuoteIdInterface::class);
        $maskedQuoteIdToQuoteId->method('execute')->willThrowException(new NoSuchEntityException());
        $plugin = $this->create(TriggerAddGuestPaymentInfoDataLayerEvent::class, [
            'maskedQuoteIdToQuoteId' => $maskedQuoteIdToQuoteId,
        ]);

        $subject = $this->createMock(GuestPaymentInformationManagementInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $this->assertSame(
            '43',
            $plugin->afterSavePaymentInformationAndPlaceOrder($subject, '43', 'masked', 'a@example.com', $payment)
        );
    }

    public function testGuestPaymentInfoPluginReturnsResultOfSavePaymentInformationWhenTrackingFails()
    {
        $plugin = $this->create(TriggerAddGuestPaymentInfoDataLayerEvent::class, [
            'addPaymentInfo' => $this->createThrowingMock(AddPaymentInfo::class),
        ]);

        $subject = $this->createMock(GuestPaymentInformationManagementInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $this->assertTrue($plugin->afterSavePaymentInformation($subject, true, '1', 'a@example.com', $payment));
    }

    public function testShippingInfoPluginReturnsPaymentDetailsWhenTrackingFails()
    {
        $plugin = $this->create(TriggerAddShippingInfoDataLayerEvent::class, [
            'addShippingInfo' => $this->createThrowingMock(AddShippingInfo::class),
        ]);

        $subject = $this->createMock(ShippingInformationManagementInterface::class);
        $paymentDetails = $this->createMock(PaymentDetailsInterface::class);
        $addressInformation = $this->createMock(ShippingInformationInterface::class);
        $this->assertSame(
            $paymentDetails,
            $plugin->afterSaveAddressInformation($subject, $paymentDetails, 1, $addressInformation)
        );
    }

    public function testViewSearchResultPluginReturnsResultWhenTrackingFails()
    {
        $plugin = $this->create(TriggerViewSearchResultDataLayerEvent::class, [
            'viewSearchResultEvent' => $this->createThrowingMock(ViewSearchResult::class),
        ]);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->willReturn('shirt');
        $subject = $this->createMock(SearchResultController::class);
        $subject->method('getRequest')->willReturn($request);
        $result = new \stdClass();
        $this->assertSame($result, $plugin->afterExecute($subject, $result));
    }

    public function testCartSectionPluginReturnsResultWhenTrackingFails()
    {
        $plugin = $this->create(AddDataToCartSection::class, [
            'checkoutCart' => $this->createThrowingMock(CheckoutCart::class),
        ]);

        $result = ['summary_count' => 1];
        $this->assertSame($result, $plugin->afterGetSectionData($this->createMock(CartCustomerData::class), $result));
    }

    public function testCustomerSectionPluginReturnsResultWhenTrackingFails()
    {
        $plugin = $this->create(AddDataToCustomerSection::class, [
            'customerSessionDataProvider' => $this->createThrowingMock(CustomerSessionDataProviderInterface::class),
        ]);

        $result = ['firstname' => 'John'];
        $subject = $this->createMock(CustomerCustomerData::class);
        $this->assertSame($result, $plugin->afterGetSectionData($subject, $result));
    }

    #[DataProvider('getObservers')]
    public function testObserverDoesNotThrowWhenTrackingFails(string $observerClass, string $argument, string $event)
    {
        /** @var ObserverInterface $observer */
        $observer = $this->create($observerClass, [$argument => $this->createThrowingMock($event)]);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->willReturn('1');
        $observer->execute(new Observer([
            'event' => new Event(['customer' => $this->createMock(CustomerInterface::class)]),
            'request' => $request,
            'product' => $this->createMock(Product::class),
            'order' => $this->createMock(OrderInterface::class),
            'quote_item' => $this->createMock(CartItemInterface::class),
        ]));
    }

    public static function getObservers(): array
    {
        return [
            [TriggerAddToCartDataLayerEvent::class, 'addToCartEvent', AddToCart::class],
            [TriggerAddToWishlistDataLayerEvent::class, 'addToWishlistEvent', AddToWishlist::class],
            [TriggerLoginDataLayerEvent::class, 'loginEvent', Login::class],
            [TriggerLogoutDataLayerEvent::class, 'logoutEvent', Logout::class],
            [TriggerPurchaseDataLayerEvent::class, 'purchaseEvent', Purchase::class],
            [TriggerRemoveFromCartDataLayerEvent::class, 'removeFromCartEvent', RemoveFromCart::class],
            [TriggerSignUpDataLayerEvent::class, 'signUpEvent', SignUp::class],
        ];
    }

    private function create(string $class, array $arguments): object
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');
        $arguments['logger'] = $logger;

        return Bootstrap::getObjectManager()->create($class, $arguments);
    }

    private function createThrowingMock(string $class): object
    {
        $mock = $this->createMock($class);
        $mock->method($this->anything())->willThrowException(new RuntimeException('Tracking failed'));

        return $mock;
    }
}
