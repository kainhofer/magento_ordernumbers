<?php
require_once(Mage::getBaseDir('lib') . '/OpenTools/Ordernumber/ordernumber_helper_magento.php');

class OpenTools_Ordernumber_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $helper = null;
	
    function logitem($label, $item)
    {
        Mage::Log($label . " " . get_class($item) . "\n", null, 'ordernumber.log');
        Mage::Log(is_array($item)?$item:$item->debug(), null, 'ordernumber.log');
        Mage::Log(get_class_methods(get_class($item)), null, 'ordernumber.log');
	}
    function __construct()
    {
        // Setup the ordernumber helper object and register the required callbacks:
        $this->helper = OrdernumberHelperMagento::getHelper();

		$this->helper->registerCallback('setupStoreReplacements',		array($this, 'setupStoreReplacements'));
		
		
		$this->helper->registerCallback('setupOrderReplacements',		array($this, 'setupOrderReplacements'));
		$this->helper->registerCallback('setupUserReplacements',		array($this, 'setupUserReplacements'));
		$this->helper->registerCallback('setupShippingReplacements',	array($this, 'setupShippingReplacements'));
		$this->helper->registerCallback('setupThirdPartyReplacements',	array($this, 'setupThirdPartyReplacements'));
    }
    
    public function getOrdernumberHelper() {
		return $this->helper;
	}

    public function setupAddressReplacements(&$reps, $prefix, $address, $nrtype) {
        if (!$address) {
            return;
        }
        $reps["[".$prefix."addressid]"] = $address->getId();

        $reps["[".$prefix."firstname]"] = $address->getFirstname();
        $reps["[".$prefix."lastname]"] = $address->getLastname();
        $reps["[".$prefix."company]"] = $address->getCompany();
        $reps["[".$prefix."city]"] = $address->getCity();
        $reps["[".$prefix."zip]"] = $address->getPostcode();
        $reps["[".$prefix."postcode]"] = $address->getPostcode();

        $reps["[".$prefix."region]"] = $address->getRegion();
        $reps["[".$prefix."regioncode]"] = $address->getRegionCode();
        $reps["[".$prefix."regionid]"] = $address->getRegionId();

        $country = $address->getCountryModel();
        $reps["[".$prefix."country]"] = $country->getName();
        $reps["[".$prefix."countrycode2]"] = $country->getIso2Code();
        $reps["[".$prefix."countrycode3]"] = $country->getIso3Code();
        $reps["[".$prefix."countryid]"] = $country->getId();

    }
    public function setupStoreReplacements (&$reps, $details, $nrtype) {
		if (isset($details->order)) {
			$order = $details->order;
			$store = $order->getStore();
			$reps["[storeid]"] = $store->getStoreId();
			$reps["[storecurrency]"] = $order->getStoreCurrency();
		}
    }
    public function setupOrderReplacements (&$reps, $details, $nrtype) {
		if (isset($details->order)) {
			$order = $details->order;
			$shippingAddress = $order->getShippingAddress();
			$billingAddress = $order->getBillingAddress();
			if ($shippingAddress) {
				$address = $shippingAddress;
			} else {
				$address = $billingAddress;
			}
			/* if ($nrtype == "invoice") {
				// Invoices use the billing address for un-prefixed fields
				$address = $billingAddress;
			} */
			$reps["[orderid]"] = $order->getId();
			$reps["[ordernumber]"] = $order->getIncrementId();
			$reps["[orderstatus]"] = $order->getStatus();
			$reps["[currency]"] = $order->getOrderCurrency()->getCurrencyCode();
			$reps["[customerid]"] = $order->getCustomerId();
			$this->setupAddressReplacements($reps, "", $address, $nrtype);
			$this->setupAddressReplacements($reps, "shipping", $shippingAddress, 	$nrtype);
			$this->setupAddressReplacements($reps, "billing", $billingAddress, $nrtype);

			$reps["[totalitems]"] = $order->getTotalItemCount();
			$reps["[totalquantity]"] = $order->getTotalQtyOrdered();
        
			// TODO: Add list variables, like SKUs, shipping classes, categories, manufacturers, etc.
			/*
			// List-valued properties for custom variable checks:
			// TODO: Also implement variable for:
			//  - Shipping needed
			//  - Downloads available
			$lineitems = $order->get_items();
			$skus = array();
			$categories = array();
			$tags = array();
			$shippingclasses = array();
			foreach ($lineitems as $l) {
				$p = $order->get_product_from_item($l);
				$skus[$p->get_sku()] = 1;
				foreach (wc_get_product_terms( $p->id, 'product_cat') as $c) {
					$categories[$c->slug] = 1;
				}
				foreach (wc_get_product_terms( $p->id, 'product_tag') as $c) {
					$tags[$c->slug] = 1;
				}
				$shippingclasses[$p->get_shipping_class()] = 1;
			}
			$reps["[skus]"] = array_keys($skus);
			$reps["[categories]"] = array_keys($categories);
			$reps["[tags]"] = array_keys($tags);
			$reps["[shippingclasses]"] = array_keys($shippingclasses);
		*/
		 }
		if (isset($details->invoice)) {
			$invoice = $details->invoice;
			$reps["[invoiceid]"] = $invoice->getId();
		}
    }
	public function setupUserReplacements (&$reps, $details, $nrtype) {
		// TODO
// 		$reps["[ipaddress]"]   = $details->customer_ip_address;
// 		$reps["[userid]"]      = $details->get_user_id();
	}

    public function setupShippingReplacements(&$reps, $details, $nrtype) {
		if (isset($details->order)) {
			$order = $details->order;
			$reps["[shippingmethod]"] = $order->getShippingMethod();
		}
    }


}
