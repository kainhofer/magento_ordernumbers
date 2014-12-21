<?php
class OpenTools_Ordernumber_Model_Source_Scope
{
    public function toOptionArray()
    {
        $scopes = array(
            array('value' => '0', 'label' => Mage::helper('ordernumber')->__('Use all counters across all stores')),
            array('value' => '1', 'label' => Mage::helper('ordernumber')->__('Separate counters for each Website')),
            array('value' => '2', 'label' => Mage::helper('ordernumber')->__('Separate counters for each Store')),
            array('value' => '3', 'label' => Mage::helper('ordernumber')->__('Separate counters for each Store View')),
        );
        return $scopes;
    }

}