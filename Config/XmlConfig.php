<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;
use Yireo\GoogleTagManager2\Config\XmlConfig\Converter;

class XmlConfig
{
    /**
     * @var DataInterface
     */
    protected $dataStorage;

    private ObjectManagerInterface $objectManager;
    private StoreManagerInterface $storeManager;
    private array $resolvedDefaults = [];

    /**
     * @param DataInterface $dataStorage
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DataInterface $dataStorage,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        $this->dataStorage = $dataStorage;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
    }

    public function getDefault(): array
    {
        try {
            $storeId = (int)$this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            return $this->resolveTags($this->dataStorage->get('default'));
        }

        if (!isset($this->resolvedDefaults[$storeId])) {
            $this->resolvedDefaults[$storeId] = $this->resolveTags($this->dataStorage->get('default'));
        }

        return $this->resolvedDefaults[$storeId];
    }

    public function getEvents(): array
    {
        return $this->resolveTags($this->dataStorage->get('events'));
    }

    public function getEvent(string $eventName): array
    {
        $events = $this->dataStorage->get('events');
        foreach ($events as $eventId => $eventData) {
            if ($eventName === $eventId) {
                return $this->resolveTags($eventData);
            }
        }

        return [];
    }

    public function _resetState(): void
    {
        $this->resolvedDefaults = [];
    }

    private function resolveTags(array $data): array
    {
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if (isset($value[Converter::TAG_CLASS_KEY])) {
                $data[$key] = $this->getDataFromTagClass((string)$value[Converter::TAG_CLASS_KEY]);
                continue;
            }

            $data[$key] = $this->resolveTags($value);
        }

        return $data;
    }

    private function getDataFromTagClass(string $tagClassName)
    {
        $tagObject = $this->objectManager->get($tagClassName);
        if (!$tagObject instanceof TagInterface) {
            return [];
        }

        return $tagObject->get();
    }
}
