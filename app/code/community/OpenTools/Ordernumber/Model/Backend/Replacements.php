<?php
class OpenTools_Ordernumber_Model_Backend_Replacements extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
{
    protected function logitem($label, $item)
    {
        Mage::Log($label . " " . get_class($item) . "\n", null, 'ordernumber.log');
        Mage::Log(is_array($item)?$item:$item->debug(), null, 'ordernumber.log');
        Mage::Log(get_class_methods(get_class($item)), null, 'ordernumber.log');
    }

    protected $_keywords = array("conditionvar", "conditionval", "newvar", "newval");

    /**
     * The form contains the values as one array for the conditionvars, one for the conditionvals,
     * one for the newvariables, one for the newvalues. In the database we want to store it
     * transposed as an array of (conditionvar, conditionval, newvariable, newval) arrays.
     * Everything else should be inherited from the serialized config backend.
     */
    protected function _beforeSave()
    {
$this->logitem("OpenTools_Ordernumber_Model_Backend_Replacements: ", $this);
        $vals = $this->getValue();
        // Transpose the vals:
        $vallist = array();
        if ($vals) {
            foreach ($vals[$this->_keywords[1]] as $i => $dummy) {
                $entry = array();
                foreach ($this->_keywords as $k) {
                    $entry[$k] = $vals[$k][$i];
                }
                $vallist[] = $entry;
            }
        }
Mage::Log("transposed vallist: ".print_r($vallist, 1), null, 'ordernumber.log');
        $this->setValue($vallist);
        return parent::_beforeSave();
    }


}