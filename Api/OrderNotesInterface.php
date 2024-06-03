<?php declare(strict_types=1);

namespace Tagging\GTM\Api;


interface OrderNotesInterface
{
    /**
     * Save custom data
     *
     * @param mixed $jsonData
     * @return string
     */
    public function saveData($jsonData);
}