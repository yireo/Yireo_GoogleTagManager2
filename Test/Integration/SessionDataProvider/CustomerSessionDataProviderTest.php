<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\SessionDataProvider;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractController;
use Yireo\GoogleTagManager2\SessionDataProvider\CustomerSessionDataProvider;

class CustomerSessionDataProviderTest extends AbstractController
{
    /**
     * @magentoAppArea frontend
     * @return void
     */
    public function testSettingOnce()
    {
        $serializer = ObjectManager::getInstance()->get(SerializerInterface::class);

        $customerSessionDataProvider = ObjectManager::getInstance()->get(CustomerSessionDataProvider::class);
        $customerSessionDataProvider->add('foobar', ['foo' => 'bar']);

        $this->getRequest()->setParams(['sections' => 'customer']);
        $this->dispatch('customer/section/load');
        $body = $this->getResponse()->getBody(); // @phpstan-ignore-line
        $data = $serializer->unserialize($body, true);
        $this->assertEquals('bar', $data['customer']['gtm_events']['foobar']['foo']);

        $this->getRequest()->setParams(['sections' => 'customer']);
        $this->dispatch('customer/section/load');
        $body = $this->getResponse()->getBody(); // @phpstan-ignore-line
        $data = $serializer->unserialize($body, true);
        $this->assertEmpty($data['customer']['gtm_events']);
    }
}
