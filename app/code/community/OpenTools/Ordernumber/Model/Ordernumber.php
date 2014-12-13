<?php
class OpenTools_Ordernumber_Model_Ordernumber extends Mage_Core_Model_Abstract
{
     public function _construct()
     {
         parent::_construct();
         $this->_init('opentools_ordernumber/ordernumber');
     }
     public function getCounterValueIncremented($nrtype, $format) {
        $helper = Mage::helper('ordernumber');
        $this->loadNumberCounter($nrtype, $format);

        $this->setNumberType($nrtype);
        $this->setNumberFormat($format);
        $count = $this->getCount()+1;
        $this->setCount($count);
        $res = $this->save();
        return $count;
     }
    public function loadNumberCounter($nrtype, $format)
    {
        $this->_getResource()->loadNumberCounter($this, $nrtype, $format);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }
}