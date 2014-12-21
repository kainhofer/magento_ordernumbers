<?php
class OpenTools_Ordernumber_Model_Backend_Counters extends Mage_Core_Model_Config_Data
{
    /**
     * Instead of storing the counter values in the config (they are NOT config values),
     * change the database and then clear the value:
     */
    protected function _beforeSave()
    {
        if (is_array($this->getValue())) {
            // TODO: Store new values in the database
            // Compare with old values
        }
    }
}