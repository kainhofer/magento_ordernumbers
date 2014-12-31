<?php
class OpenTools_Ordernumber_Model_Source_Reset
{
    public function toOptionArray()
    {
        $helper = Mage::helper('ordernumber');

        $reset = array(
            array('value' => '0', 'label' => $helper->__('One counter without reset')),
            array('value' => '[year]', 'label' => $helper->__('New counter for each year')),
            array('value' => '[year]-[month]', 'label' => $helper->__('New counter for each month')),
            array('value' => '[year]-[month]-[day]', 'label' => $helper->__('New counter for each day')),
            array('value' => '1', 'label' => $helper->__('Separate counter for each Format Value')),
            array('value' => '-1', 'label' => $helper->__('Custom counter Name')),
        );
        return $reset;
    }

}
