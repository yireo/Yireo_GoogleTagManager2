<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Event\Promotion;

class PromotionItem
{
    private string $id;
    private string $name;
    private string $createName;
    private string $createSlot;
    private string $locationId;

    /**
     * @param string $id
     * @param string $name
     * @param string $createName
     * @param string $createSlot
     * @param string $locationId
     */
    public function __construct(
        string $id,
        string $name,
        string $createName = '',
        string $createSlot = '',
        string $locationId = ''
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->createName = $createName;
        $this->createSlot = $createSlot;
        $this->locationId = $locationId;
    }

    public function get(): array
    {
        return [
            'promotion_id' => $this->id,
            'promotion_name' => $this->name,
            'creative_name' => $this->createName,
            'creative_slot' => $this->createSlot,
            'location_id' => $this->locationId,
        ];
    }
}
