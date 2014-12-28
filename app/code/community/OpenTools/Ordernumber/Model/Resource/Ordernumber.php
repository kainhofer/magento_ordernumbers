<?php
class OpenTools_Ordernumber_Model_Resource_Ordernumber extends Mage_Core_Model_Resource_Db_Abstract
{
     public function _construct() {
         $this->_init('opentools_ordernumber/ordernumber', 'ordernumber_id');
     }

    public function loadNumberCounter(Mage_Core_Model_Abstract $object, $nrtype, $format, $scope='') {
        $read = $this->_getWriteAdapter();
        if ($read && !is_null($nrtype)) {
            $typefield = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'number_type'));
            $formatfield = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'number_format'));
            $scopefield = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'number_scope'));

            $select = $read->select()
                ->from($this->getMainTable())
                ->where($typefield . '=?', $nrtype)
                ->where($formatfield .'=?', $format)
                ->where($scopefield . '=?', $scope);
            $data = $read->fetchRow($select);
            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }
}