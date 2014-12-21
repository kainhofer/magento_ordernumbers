<?php
class OpenTools_Ordernumber_Model_Source_Reset
{
    public function toOptionArray()
    {
        $reset = array(
            array('value' => '0', 'label' => 'One counter without reset'),
            array('value' => '[year]', 'label' => 'New counter for each year'),
            array('value' => '[year]-[month]', 'label' => 'New counter for each month'),
            array('value' => '[year]-[month]-[day]', 'label' => 'New counter for each day'),
            array('value' => '1', 'label' => 'Separate counter for each Format Value'),
        	array('value' => '-1', 'label' => 'Custom counter Name'),
        );
        return $reset;
    }

}
