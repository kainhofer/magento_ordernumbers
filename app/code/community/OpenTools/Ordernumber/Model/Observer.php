<?php

/**
 * Open Tools Ordernumber module for Magento
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   OpenTools
 * @package    Ordernumber
 * @copyright  Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OpenTools_Ordernumber_Model_Observer extends Mage_Core_Model_Abstract
{
     public function _construct()
     {
         parent::_construct();
         $this->_init('ordernumber/ordernumber');
     }

    protected $_dbModel = null;
    protected function _getModel() {
        return Mage::getModel('opentools_ordernumber/ordernumber');
    }

    public function getModel() {
        if (is_null($this->_dbModel))
            $this->_dbModel = $this->_getModel();
        return $this->_dbModel;
    }

    /** This trigger is called directly after the increment ID is reserved for an order
     * Ideally, we would overwrite the reserveOrderId function, so that magento does not
     * create/reserve an order number in the first place
     * Problem is, no order information is passed to the increment id model,
     * so we would have to hack (i.e. rewrite) many more models to pass on this information,
     * which will in the end lead to an even worse code quality...
     */
    public function sales_model_service_quote_submit_before ($observer) {
        $order = $observer->getEvent()->getOrder();
        return $this->handle_new_number('order', $order, $order);
    }
    public function sales_order_save_before ($observer) {
        $order = $observer->getEvent()->getOrder();
        return $this->handle_new_number('order', $order, $order);
    }

    public function sales_order_invoice_save_before ($observer) {
        $invoice = $observer->getEvent()->getInvoice();
        return $this->handle_new_number('invoice', $invoice, $invoice->getOrder());
    }

    public function sales_order_shipment_save_before ($observer) {
        $shipment = $observer->getEvent()->getShipment();
        return $this->handle_new_number('shipment', $shipment, $shipment->getOrder());
    }

    public function sales_order_creditmemo_save_before ($observer) {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        return $this->handle_new_number('creditmemo', $creditmemo, $creditmemo->getOrder());
    }

    public function handle_new_number ($nrtype, $object, $order) {
        $store = $order->getStore();
        $storeId = $store->getStoreId();
        $cfgprefix = 'ordernumber/'.$nrtype.'numbers';
        $enabled = Mage::getStoreConfig($cfgprefix.'/active', $storeId);

        if ($enabled && !$object->getId() && !$object->getOrdernumberProcessed()) {
            // This trigger might be called twice, so ignore it the second time!
            $object->setOrdernumberProcessed(true);

            $format = Mage::getStoreConfig($cfgprefix.'/format', $storeId);
            $scope = Mage::getStoreConfig($cfgprefix.'/scope', $storeId);
            $reset = Mage::getStoreConfig($cfgprefix.'/reset', $storeId);
            $counterfmt = Mage::getStoreConfig($cfgprefix.'/resetformat', $storeId);
            $digits = Mage::getStoreConfig($cfgprefix.'/digits', $storeId);
            $increment = Mage::getStoreConfig($cfgprefix.'/increment', $storeId);

            // First, replace all variables:
            $helper = Mage::helper('ordernumber');
            $info = array('order'=>$order, $nrtype=>$object);

            // The ordernumber/...numbers/reset contains some pre-defined counter names as
            // well as enum values indicating certain behavior. Replace those by the actual
            // counter names for the current counter:
            switch ($reset) {
                case 0:  $format = $format . '|'; break;
                case 1:  $format = $format . '|' . $format; break;
                case -1: $format = $format . '|' . $counterfmt; break;
                default: /* Pre-defined counter formats saved in the /reset config field */
                    $counterfmt = $format . '|' . $reset; break;
            }
            $customvars = Mage::getStoreConfig('ordernumber/replacements', $storeId);
            if (isset($customvars['replacements']))
                $customvars = $customvars['replacements'];
            if ($customvars)
                $customvars = unserialize($customvars);
// Mage::Log('customvars: '.print_r($customvars,1), null, 'ordernumber.log');

            // Now apply the replacements
            $nr = $helper->replace_fields ($format, $nrtype, $info, $customvars);

            // Split at a | to get the number format and a possibly different counter increment format
            // If a separate counter format is given after the |, use it, otherwise reuse the number format itself as counter format
            $parts = explode ("|", $nr);
            $format = $parts[0];
            $counterfmt = $parts[(count($parts)>1)?1:0];

            // Now find the next counter that does not lead to duplicate
            $newnumber = null;
            $model = $this->getModel();

            $count = 0;
            $created = false;
            // Make up to 150 attempts to create a number...
            while (empty($newnumber) && ($count<150)) {
                $count += 1;

                // Find the next counter value
                $scope_id = '';
                if ($scope>=1) $scope_id = $store->getWebsiteId();
                if ($scope>=2) $scope_id .= '/' . $store->getGroupId();
                if ($scope>=3) $scope_id .= '/' . $store->getStoreId();
                $count = $model->getCounterValueIncremented($nrtype, $counterfmt, $increment, $scope_id);
                $newnumber = str_replace ("#", sprintf('%0' . (int)$digits . 's', $count), $format);

                // Check whether that number is already in use. If so, attempt to create the next number:
                $modelname=($nrtype=='order') ? 'sales/order' : ('sales/order_'.$nrtype);
                $collection = Mage::getModel($modelname)->getCollection()->addFieldToFilter('increment_id', $newnumber);
                if ($collection->getAllIds()) {
                    Mage::Log("$nrtype number $newnumber already in use, trying again", null, 'ordernumber.log');
                    //number already exists => next attempt in the loop
                    $newnumber = null;
                } else {
                    $object->setIncrementId($newnumber);
                    $created = true;
                }
            }
            if (!$created) {
                Mage::Log("Unable to create $nrtype number for counter format $nr (name $counterfmt, scope $scope_id)...", null, 'ordernumber.log');
            }
        }
    }

}
