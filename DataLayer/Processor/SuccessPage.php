<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Processor;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\DataLayer\Mapper\CustomerDataMapper;
use Yireo\GoogleTagManager2\DataLayer\Mapper\GuestDataMapper;
use Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper;
use Yireo\GoogleTagManager2\Util\PriceFormatter;

class SuccessPage implements ProcessorInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PriceFormatter
     */
    protected $priceFormatter;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    private ProductDataMapper $productDataMapper;
    private CustomerDataMapper $customerDataMapper;
    private CustomerRepositoryInterface $customerRepository;
    private GuestDataMapper $guestDataMapper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CheckoutSession $checkoutSession
     * @param Config $config
     * @param PriceFormatter $priceFormatter
     * @param StoreManagerInterface $storeManager
     * @param ProductDataMapper $productDataMapper
     * @param CustomerDataMapper $customerDataMapper
     * @param CustomerRepositoryInterface $customerRepository
     * @param GuestDataMapper $guestDataMapper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        CheckoutSession $checkoutSession,
        Config $config,
        PriceFormatter $priceFormatter,
        StoreManagerInterface $storeManager,
        ProductDataMapper $productDataMapper,
        CustomerDataMapper $customerDataMapper,
        CustomerRepositoryInterface $customerRepository,
        GuestDataMapper $guestDataMapper
    ) {
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->priceFormatter = $priceFormatter;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->productDataMapper = $productDataMapper;
        $this->customerDataMapper = $customerDataMapper;
        $this->customerRepository = $customerRepository;
        $this->guestDataMapper = $guestDataMapper;
    }

    /**
     * @inheritDoc
     */
    public function process(array $data): array
    {
        $order = $this->checkoutSession->getLastRealOrder();

        $data =  [
            'event' => 'purchase',
            'ecommerce' => [
                'value' => $this->priceFormatter->format((float) $order->getGrandTotal()),
                'currencyCode' => $order->getBaseCurrencyCode(),
                'purchase' => [
                    'actionField' => $this->getActionField($order),
                    'products' => $this->getProductData($order)
                ]
            ],
            'user' => [
                'has_transacted' => true
            ]
        ];

        return $data;
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    protected function getActionField($order): array
    {
        if ($order->getCustomerId() > 0) {
            $customer = $this->customerRepository->getById($order->getCustomerId());
            $customerData = $this->customerDataMapper->mapByCustomer($customer);
        } else {
            $customerData = $this->guestDataMapper->mapByOrder($order);
        }


        return [
            'id' => $order->getIncrementId(),
            'affiliation' => $this->config->getStoreName($this->storeManager->getStore()->getId()),
            'revenue' => $this->priceFormatter->format((float) $order->getGrandTotal()),
            'discount' => $this->priceFormatter->format((float) $order->getDiscountAmount()),
            'shipping' => $this->priceFormatter->format((float) $order->getShippingAmount()),
            'tax' => $this->priceFormatter->format((float) $order->getTaxAmount()),
            'coupon' => $order->getCouponCode(),
            'date' => date("Y-m-d", strtotime($order->getCreatedAt())),
            'paymentType' => $this->getPaymentType($order),
            'customer' => $customerData
        ];
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    protected function getPaymentType($order): string
    {
        if (!$order || !$order->getPayment() || !$order->getPayment()->getMethodInstance()) {
            return '';
        }

        return $order->getPayment()->getMethodInstance()->getTitle();
    }

    /**
     * @param Order $order
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProductData($order): array
    {
        $data = [];

        /** @var Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            if (isset($data[$item->getSku()])) {
                $data[$item->getSku()]['quantity'] += $item->getQtyOrdered();
            } else {
                try {
                    /** @var ProductInterface $product */
                    $product = $this->productRepository->get($item->getSku());
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                $itemData = $this->productDataMapper->mapByProduct($product);

                $itemData = array_replace($itemData, [
                    'quantity' => $item->getQtyOrdered(),
                    'unit_price' => $this->priceFormatter->format((float) $item->getPriceInclTax())
                ]);

                // checkout data does not need stock data
                unset($itemData['details']['availability']);
                if (empty($itemData['details'])) {
                    unset($itemData['details']);
                }

                $data[$item->getSku()] = $itemData;
            }
        }

        return array_values($data);
    }
}
