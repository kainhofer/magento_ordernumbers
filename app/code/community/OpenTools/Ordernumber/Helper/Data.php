<?php
class OpenTools_Ordernumber_Helper_Data extends Mage_Core_Helper_Abstract
{
function logitem($label, $item) {
    Mage::Log($label . " " . get_class($item) . "\n", null, 'ordernumber.log');
    Mage::Log(is_array($item)?$item:$item->debug(), null, 'ordernumber.log');
    Mage::Log(get_class_methods(get_class($item)), null, 'ordernumber.log');
}

    /* Return a random "string" of the given length taken from the given alphabet */
    function randomString($alphabet, $len) {
        $alen = strlen($alphabet);
        $r = "";
        for ($n=0; $n<$len; $n++) {
            $r .= $alphabet[mt_rand(0, $alen-1)];
        }
        return $r;
    }

    function replaceRandom ($match) {
        /* the regexp matches (random)(Type)(Len) as match, Type and Len is optional */
        $len = ($match[3]?$match[3]:1);
        // Fallback: If no Type is given, use Digit
        $alphabet = "0123456789";
        // Select the correct alphabet depending on Type
        switch (strtolower($match[2])) {
            case "digit": $alphabet = "0123456789"; break;
            case "hex": $alphabet = "0123456789abcdef"; break;
            case "letter": $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"; break;
            case "uletter": $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; break;
            case "lletter": $alphabet = "abcdefghijklmnopqrstuvwxyz"; break;
            case "alphanum": $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"; break;
        }
        return self::randomString ($alphabet, $len);
    }

    function setupDateTimeReplacements (&$reps, $nrtype) {
        $reps["[year]"] = date ("Y");
        $reps["[year2]"] = date ("y");
        $reps["[month]"] = date("m");
        $reps["[day]"] = date("d");
        $reps["[hour]"] = date("H");
        $reps["[hour12]"] = date("h");
        $reps["[ampm]"] = date("a");
        $reps["[minute]"] = date("i");
        $reps["[second]"] = date("s");
    }
    function setupAddressReplacements(&$reps, $prefix, $address, $nrtype) {
        if (!$address) return;
        $reps["[".$prefix."addressid]"] = $address->getId();

        $reps["[".$prefix."firstname]"] = $address->firstname;
        $reps["[".$prefix."lastname]"] = $address->lastname;
        $reps["[".$prefix."company]"] = $address->company;
        $reps["[".$prefix."city]"] = $address->city;
        $reps["[".$prefix."zip]"] = $address->postcode;
        $reps["[".$prefix."postcode]"] = $address->postcode;

        $reps["[".$prefix."region]"] = $address->getRegion();
        $reps["[".$prefix."regioncode]"] = $address->getRegionCode();
        $reps["[".$prefix."regionid]"] = $address->getRegionId();

        $country = $address->getCountryModel();
        $reps["[".$prefix."country]"] = $country->getName();
        $reps["[".$prefix."countrycode2]"] = $country->iso2_code;
        $reps["[".$prefix."countrycode3]"] = $country->iso3_code;
        $reps["[".$prefix."countryid]"] = $country->getId();

    }
    function setupStoreReplacements (&$reps, $order, $nrtype) {
        $store = $order->getStore();
        $reps["[storeid]"] = $store->getStoreId();
        $reps["[storecurrency]"] = $order->getStoreCurrency();
    }
    function setupOrderReplacements (&$reps, $order, $nrtype) {
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $address = $shippingAddress;
        /* if ($nrtype == "invoice") {
            // Invoices use the billing address for un-prefixed fields
            $address = $billingAddress;
        } */
        $reps["[orderid]"] = $order->getId();
        $reps["[ordernumber]"] = $order->getIncrementId();
        $reps["[orderstatus]"] = $order->status;
        $reps["[currency]"] = $order->getOrderCurrency()->getCurrencyCode();
        $reps["[customerid]"] = $order->customer_id;
        $this->setupAddressReplacements($reps, "", $address, $nrtype);
        $this->setupAddressReplacements($reps, "shipping", $shippingAddress, $nrtype);
        $this->setupAddressReplacements($reps, "billing", $billingAddress, $nrtype);

        $reps["[totalitems]"] = $order->total_item_count;
        $reps["[totalquantity]"] = $order->total_qty_ordered;
    }
    function setupShippingReplacements(&$reps, $order, $nrtype) {
        $reps["[shippingmethod]"] = $order->getShippingMethod();
    }

    function setupShipmentReplacements (&$reps, $shipment, $order, $nrtype) {
        // TODO
    }
    function setupInvoiceReplacements (&$reps, $invoice, $order, $nrtype) {
        $reps["[invoiceid]"] = $invoice->getId();
    }
    function setupCreditMemoReplacements (&$reps, $creditmemo, $order, $nrtype) {
        // TODO
    }
    function setupReplacements($nrtype, $info) {
        $reps = array();
        $order = $info['order'];
        $this->setupDateTimeReplacements($reps, $nrtype);
        $this->setupStoreReplacements($reps, $order, $nrtype);
        $this->setupOrderReplacements($reps, $order, $nrtype);
        $this->setupShippingReplacements($reps, $order, $nrtype);
        if (isset($info['shipment'])) {
            $this->setupShipmentReplacements($reps, $info['shipment'], $order, $nrtype);
        }
        if (isset($info['invoice'])) {
            $this->setupInvoiceReplacements($reps, $info['invoice'], $order, $nrtype);
        }
        if (isset($info['creditmemo'])) {
            $this->setupCreditMemoReplacements($reps, $info['creditmemo'], $order, $nrtype);
        }
// Mage::Log('Replacements at end of setupReplacements(nrtype='.$nrtype.'): '.print_r($reps,1), null, 'ordernumber.log');

        return $reps;
    }

    function applyCustomVariables ($nrtype, $info, $reps, $customvars) {
        static $listvars = array("groups", "skus");
// Mage::getSingleton('core/session')->addWarning('<pre>custom variables, conditionvar='.$conditionvar.', reps='.print_r($reps,1).', customvars='.print_r($customvars,1).'</pre>');
        $order = $info['order'];
        $customer = $order->getCustomer();
        $address = $order->getShippingAddress();
        $store = $order->getStore();
// $this->logitem("Order: ", $order);
        foreach ($customvars as $c) {
            $conditionvar = $c['conditionvar'];

            $found = false;
            $compareval = null;

            if (!$found && isset($reps[$conditionvar])) {
                $found = true;
                $compareval = $reps[$conditionvar];
            } elseif (isset($reps['['.$conditionvar.']'])) {
                $found = true;
                $compareval = $reps['['.$conditionvar.']'];
            } elseif (in_array($conditionvar, $listvars)) {
                // TODO: Handle lists
                $found = true;
                $compareval = null /* TODO */;
            } elseif ($compareval = $order->getData($conditionvar)) {
                // TODO: Handle order property
                $found = true;
            } elseif ($compareval = $customer->getData($conditionvar)) {
                // TODO: Handle customer property
                $found = true;
            } elseif ($compareval = $address->getData($conditionvar)) {
                // TODO: Handle address property
                $found = true;
            } elseif ($compareval = $store->getData($conditionvar)) {
                // TODO: Handle store property
                $found = true;
            } else {
                // TODO: Handly other possible properties!
                // TODO: Print out warning that variable could not be found.
                Mage::getSingleton('core/session')->addWarning($this->__('Unable to find variable "%s" used in the ordernumber custom variable definitions.', $conditionvar));
            }
            if ($found) {
                if (is_array($compareval)) {
                    $match = in_array($c['conditionval'], $compareval);
                } else {
                    $match = ($c['conditionval'] == $compareval);
                }
            }
            if ($found && $match) {
                $varname = '['.strtolower($c['newvar']).']';
                $reps[$varname] = $c['newval'];
            }
// $this->logitem("Reps after $conditionvar: ", $order);
        }
        return $reps;
    }

    function doReplacements ($fmt, $reps) {
        // First, replace all randomXXX[n] fields. This needs to be done with a regexp and a callback:
        $fmt = preg_replace_callback ('/\[(random)(.*?)([0-9]*?)\]/', array($this, 'replaceRandom'), $fmt);
        return str_ireplace (array_keys($reps), array_values($reps), $fmt);
    }

    function replace_fields ($fmt, $nrtype, $info, $customvars) {
        $reps = $this->setupReplacements ($nrtype, $info);
        $reps = $this->applyCustomVariables ($nrtype, $info, $reps, $customvars);
// $this->logitem("All replacements after custom variables: ", $reps);
        return $this->doReplacements($fmt, $reps);
    }


}
