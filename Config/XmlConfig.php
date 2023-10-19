<?php declare(strict_types=1);

namespace AdPage\GTM\Config;

use Magento\Framework\Config\DataInterface;

class XmlConfig
{
    /**
     * @var DataInterface
     */
    protected $dataStorage;

    /**
     * @param DataInterface $dataStorage
     */
    public function __construct(DataInterface $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    public function getDefault(): array
    {
        return $this->dataStorage->get('default');
    }

    public function getEvents(): array
    {
        return $this->dataStorage->get('events');
    }

    public function getEvent(string $eventName): array
    {
        $events = $this->getEvents();
        foreach ($events as $eventId => $eventData) {
            if ($eventName === $eventId) {
                return $eventData;
            }
        }

        return [];
    }
}
