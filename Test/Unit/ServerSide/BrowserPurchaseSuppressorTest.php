<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Unit\ServerSide;

use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\ServerSide\BrowserPurchaseSuppressor;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Consent\ConsentResolverInterface;

class BrowserPurchaseSuppressorTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testIsSuppressed(bool $suppress, bool $requireConsent, ?array $consent, bool $expected): void
    {
        $config = $this->createMock(Config::class);
        $config->method('suppressBrowserPurchase')->willReturn($suppress);
        $config->method('requireConsent')->willReturn($requireConsent);

        $consentResolver = $this->createMock(ConsentResolverInterface::class);
        $consentResolver->method('getConsent')->willReturn($consent);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getStoreId')->willReturn(1);

        $suppressor = new BrowserPurchaseSuppressor($config, $consentResolver);
        $this->assertSame($expected, $suppressor->isSuppressed($order));
    }

    public static function getCases(): array
    {
        return [
            'setting disabled' => [false, false, null, false],
            'consent not required' => [true, false, null, true],
            'consent unknown' => [true, true, null, false],
            'analytics denied' => [true, true, ['analytics' => false, 'marketing' => true], false],
            'analytics granted' => [true, true, ['analytics' => true], true],
        ];
    }
}
