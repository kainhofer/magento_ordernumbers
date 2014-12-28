<?php
class OpenTools_Ordernumber_Model_Ordernumber extends Mage_Core_Model_Abstract
{
    protected $_numberTypes = array();

     public function _construct()
     {
         parent::_construct();
         $this->_init('opentools_ordernumber/ordernumber');
     }
     public function getCounterValueIncremented($nrtype, $format, $increment=1, $scope_id='') {
        $helper = Mage::helper('ordernumber');
        $this->loadNumberCounter($nrtype, $format, $scope_id);

        $this->setNumberScope($scope_id);
        $this->setNumberType($nrtype);
        $this->setNumberFormat($format);
        $count = $this->getCount() + $increment;
        $this->setCount($count);
        $res = $this->save();
        return $count;
     }
    public function loadNumberCounter($nrtype, $format, $scope_id='')
    {
        $this->_getResource()->loadNumberCounter($this, $nrtype, $format, $scope_id);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }
    public function getNumberTypes()
    {
        if (empty($this->_numberTypes)) {
            $helper = Mage::helper('ordernumber');
            $this->_numberTypes = array(
                    'order'      => $helper->__('Order number'),
                    'invoice'    => $helper->__('Invoice'),
                    'shipment'   => $helper->__('Shipment'),
                    'creditmemo' => $helper->__('Credit Memo'),
            );
        }
        return $this->_numberTypes;
    }
    public function readableType($type)
    {
        return $this->getNumberTypes()[$type];
    }

}