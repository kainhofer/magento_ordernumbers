<?php
class OpenTools_Ordernumber_Model_Backend_Counters extends Mage_Core_Model_Config_Data
{
    protected $_dbModel = null;
    protected function _getModel()
    {
        return Mage::getModel('opentools_ordernumber/ordernumber');
    }

    public function getModel()
    {
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
			if (!isset($vals['counter'])) $vals['counter'] = array();
			if (!isset($vals['new_counter_type'])) $vals['new_counter_type'] = array();
			if (!isset($vals['deletecounter'])) $vals['deletecounter'] = array();
// $helper->logitem("counter form values: ", $vals);
            $model = $this->getModel();
            // First, handle new counters
            // If we already have a counter with the same type, scope and name, treat it like a counter edit
            foreach ($vals['new_counter_type'] as $nr =>$countertype) {
                // TODO: Check whether a counter with these name, scope and type already exists!
                $countername = $vals['new_counter_name'][$nr];
                $counterscope = $vals['new_counter_scope'][$nr];
                $countervalue = $vals['new_counter_value'][$nr];
                $counter = $model->loadNumberCounter($countertype, $countername, $counterscope);
                if ($id = $counter->getId()) {
// $helper->logitem("Existing Counter for new: ", $counter);
                    if (isset($vals['counter'][$id]) && ($vals['counter'][$id] != $vals['oldcounter'][$id])) {
                        // The counter to be added already exists, and is manually changed
                        $session->addSuccess($helper->__('Counter \'%s\' (type: %s, scope: %s) already exists and is being modified. Will not add again.',
                                  $countername, $model->readableType($countertype), $model->readableScope($counterscope)));
                    } else {
                        // Counter already exists, add it to the modifications
                        $session->addSuccess($helper->__('Counter \'%s\' (type: %s, scope: %s) already exists, modifying it to value %d',
                                  $countername, $model->readableType($countertype), $model->readableScope($counterscope), $countervalue));
                        $vals['counter'][$id] = $countervalue;
                        $vals['oldcounter'][$id] = $counter->getCount();
                    }
                } else {
                    $counter = $model->unsetData()
                                     ->setNumberType($countertype)
                                     ->setNumberScope($vals['new_counter_scope'][$nr])
                                     ->setNumberFormat($countername)
                                     ->setCount($countervalue)
                                     ->save();
                    $session->addSuccess($helper->__('Successfully created counter \'%s\' (type: %s, scope: %s) with value %d',
                              $counter->getNumberFormat(), $model->readableType($counter->getNumberType()), $model->readableScope($counter->getNumberScope()), $counter->getCount()));
                }
            }
            // First check each existing counter
            foreach ($vals['counter'] as $countid => $newval) {
                $oldval = $vals['oldcounter'][$countid];
                // Check if the counter has changed meanwhile in the DB!
                if ($oldval != $newval) {
                    $counter = $model->load($countid);
                    if ($counter->getCount() != $oldval) {
                        $session->addWarning($helper->__('Counter \'%s\' (type: %s, scope: %s) was changed in the background in the dabase from %d to %d. Overwriting with %d.',
                                $counter->getNumberFormat(), $model->readableType($counter->getNumberType()), $model->readableScope($counter->getNumberScope()), $oldval, $counter->getCount(), $newval));
                    }
                    $counter->setCount($newval)
                            ->save();
                    $session->addSuccess($helper->__('Successfully changed counter \'%s\' (type: %s, scope: %s) from %d to %d',
                            $counter->getNumberFormat(), $model->readableType($counter->getNumberType()), $model->readableScope($counter->getNumberScope()), $oldval, $counter->getCount()));
                }
            }
            // Deleting counters:
            foreach ($vals['deletecounter'] as $nr => $countid) {
                $oldval = $vals['oldcounter'][$countid];
                // Check if the counter has changed meanwhile in the DB!
                $counter = $model->load($countid);
                if ($counter->getCount() != $oldval) {
                    $session->addWarning($helper->__('Counter \'%s\' (type: %s, scope: %s) was changed in the background in the dabase from %d to %d. Deleting it nonetheless.',
                            $counter->getNumberFormat(),
                            $model->readableType($counter->getNumberType()),
                            $model->readableScope($counter->getNumberScope()),
                            $oldval,
                            $counter->getCount()));
                }
                $counter->delete();
                $session->addSuccess($helper->__('Successfully deleted counter \'%s\' (type: %s, scope: %s) with value %d',
                          $counter->getNumberFormat(), $model->readableType($counter->getNumberType()), $model->readableScope($counter->getNumberScope()), $counter->getCount()));
            }
			$this->setValue('');
        }
    }
}
