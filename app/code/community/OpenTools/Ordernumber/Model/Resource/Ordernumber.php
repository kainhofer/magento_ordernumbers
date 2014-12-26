<?php
class OpenTools_Ordernumber_Model_Resource_Ordernumber extends Mage_Core_Model_Resource_Db_Abstract
{
     public function _construct() {
         $this->_init('opentools_ordernumber/ordernumber', 'ordernumber_id');
     }

    public function loadNumberCounter(Mage_Core_Model_Abstract $object, $nrtype, $format, $website_id, $group_id, $store_id) {
        $read = $this->_getWriteAdapter();
        if ($read && !is_null($nrtype)) {
            $typefield = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'number_type'));
            $formatfield = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'number_format'));
            $websitefield = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'website_id'));
            $groupfield = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'group_id'));
            $storefield = $read->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'store_id'));

            $select = $read->select()
                ->from($this->getMainTable())
                ->where($typefield . '=?', $nrtype)
                ->where($formatfield .'=?', $format)
                ->where($websitefield . '=?', $website_id)
                ->where($groupfield . '=?', $group_id)
                ->where($storefield . '=?', $store_id);
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