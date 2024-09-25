<?php
namespace Tagging\GTM\Model;

class Order extends \Magento\Sales\Model\Order
{
    public function getTrytaggingMarketing()
    {
        return $this->getData('trytagging_marketing');
    }

    public function setTrytaggingMarketing($value)
    {
        return $this->setData('trytagging_marketing', $value);
    }
}