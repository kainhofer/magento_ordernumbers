<?php
class OpenTools_Ordernumber_Model_Backend_Counters extends Mage_Core_Model_Config_Data
{
    protected $_dbModel = null;
    protected function _getModel() {
        return Mage::getModel('opentools_ordernumber/ordernumber');
    }

    public function getModel() {
        if (is_null($this->_dbModel))
            $this->_dbModel = $this->_getModel();
        return $this->_dbModel;
    }

    /**
     * Instead of storing the counter values in the config (they are NOT config values),
     * change the database and then clear the value:
     */
    protected function _beforeSave()
    {
        $vals = $this->getValue();
        $helper = Mage::helper('ordernumber');
        $session = Mage::getSingleton('core/session');
        if (is_array($vals)) {
            $model = $this->getModel();
            // First check each existing counter
            foreach ($vals['counter'] as $countid => $newval) {
                $oldval = $vals['oldcounter'][$countid];
                // Check if the counter has changed meanwhile in the DB!
                if ($oldval != $newval) {
                    $counter = $model->load($countid);
                    if ($counter->getCount() != $oldval) {
                        $session->addWarning($helper->__('Counter "%s" (type: %s, scope: %s) was changed in the background in the dabase from %d to %d. Overwriting with %d.',
                                $counter->getNumberFormat(), $model->readableType($counter->getNumberType()), $counter->getNumberScope(), $oldval, $counter->getCount(), $newval));
                    }
                    $counter->setCount($newval)
                            ->save();
                    $session->addSuccess($helper->__('Successfully changed counter "%s" (type: %s, scope: %s) from %d to %d',
                            $counter->getNumberFormat(), $model->reNadableType($counter->getNumberType()), $counter->getNumberScope(), $oldval, $counter->getCount()));
                }
            }
            // Deleting counters:
            foreach ($vals['deletecounter'] as $nr => $countid) {
                $oldval = $vals['oldcounter'][$countid];
                // Check if the counter has changed meanwhile in the DB!
                $counter = $model->load($countid);
                if ($counter->getCount() != $oldval) {
                    $session->addWarning($helper->__('Counter "%s" (type: %s, scope: %s) was changed in the background in the dabase from %d to %d. Deleting it nonetheless.',
                            $counter->getNumberFormat(), $model->readableType($counter->getNumberType()), $counter->getNumberScope(), $oldval, $counter->getCount()));
                }
                $counter->delete();
                $session->addSuccess($helper->__('Successfully deleted counter "%s" (type: %s, scope: %s) with value %d',
                          $counter->getNumberFormat(), $model->readableType($counter->getNumberType()), $counter->getNumberScope(), $counter->getCount()));
            }
            // New counters
            foreach ($vals['new_counter_type'] as $nr =>$countertype) {
                // TODO: Check whether a counter with these name, scope and type already exists!
                $scope = $vals['new_counter_scope'][$nr];
                $counter = $model->unsetData()
                                 ->setNumberType($countertype)
                                 ->setNumberScope($vals['new_counter_scope'][$nr])
                                 ->setNumberFormat($vals['new_counter_name'][$nr])
                                 ->setCount($vals['new_counter_value'][$nr])
                                 ->save();
                $session->addSuccess($helper->__('Successfully created counter "%s" (type: %s, scope: %s) with value %d',
                          $counter->getNumberFormat(), $model->readableType($counter->getNumberType()), $counter->getNumberScope(), $counter->getCount()));
            }
        }
    }
}