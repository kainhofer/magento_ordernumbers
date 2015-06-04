<?php
class OpenTools_Ordernumber_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function logitem($label, $item)
    {
        Mage::Log($label . " " . get_class($item) . "\n", null, 'ordernumber.log');
        Mage::Log(is_array($item)?$item:$item->debug(), null, 'ordernumber.log');
        Mage::Log(get_class_methods(get_class($item)), null, 'ordernumber.log');
    }

    /* Return a random "string" of the given length taken from the given alphabet */
    protected function randomString($alphabet, $len)
    {
        $alen = strlen($alphabet);
        $r = "";
        for ($n=0; $n<$len; $n++) {
            $r .= $alphabet[mt_rand(0, $alen-1)];
        }
        return $r;
    }

    protected function replaceRandom ($match)
    {
        /* the regexp matches (random)(Type)(Len) as match, Type and Len is optional */
        $len = ($match[3]?$match[3]:1);
        // Fallback: If no Type is given, use Digit
        $alphabet = "0123456789";
        // Select the correct alphabet depending on Type
        switch (strtolower($match[2])) {
            case "digit":
                $alphabet = "0123456789";
                break;

            case "hex":
                $alphabet = "0123456789abcdef";
                break;

            case "letter":
                $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                break;

            case "uletter":
                $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                break;

            case "lletter":
                $alphabet = "abcdefghijklmnopqrstuvwxyz";
                break;

            case "alphanum":
                $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                break;
        }
        return self::randomString ($alphabet, $len);
    }

    protected function setupDateTimeReplacements (&$reps, $nrtype)
    {
        $utime = microtime(true);
        $reps["[year]"] = date ("Y", $utime);
        $reps["[year2]"] = date ("y", $utime);
        $reps["[month]"] = date("m", $utime);
        $reps["[day]"] = date("d", $utime);
        $reps["[hour]"] = date("H", $utime);
        $reps["[hour12]"] = date("h", $utime);
        $reps["[ampm]"] = date("a", $utime);
        $reps["[minute]"] = date("i", $utime);
        $reps["[second]"] = date("s", $utime);
        $milliseconds = (int)(1000*($utime - (int)$utime));
        $millisecondsstring = sprintf('%03d', $milliseconds);
        $reps["[decisecond]"] = $millisecondsstring[0];
        $reps["[centisecond]"] = substr($millisecondsstring, 0, 2);
        $reps["[millisecond]"] = $millisecondsstring;
    }

    protected function setupAddressReplacements(&$reps, $prefix, $address, $nrtype)
    {
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
    protected function setupStoreReplacements (&$reps, $order, $nrtype)
    {
        $store = $order->getStore();
        $reps["[storeid]"] = $store->getStoreId();
        $reps["[storecurrency]"] = $order->getStoreCurrency();
    }
    protected function setupOrderReplacements (&$reps, $order, $nrtype)
    {
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
        $this->setupAddressReplacements($reps, "shipping", $shippingAddress, $nrtype);
        $this->setupAddressReplacements($reps, "billing", $billingAddress, $nrtype);

        $reps["[totalitems]"] = $order->getTotalItemCount();
        $reps["[totalquantity]"] = $order->getTotalQtyOrdered();
    }
    protected function setupShippingReplacements(&$reps, $order, $nrtype)
    {
        $reps["[shippingmethod]"] = $order->getShippingMethod();
    }

    protected function setupShipmentReplacements (&$reps, $shipment, $order, $nrtype)
    {
        // TODO
    }
    protected function setupInvoiceReplacements (&$reps, $invoice, $order, $nrtype)
    {
        $reps["[invoiceid]"] = $invoice->getId();
    }
    protected function setupCreditMemoReplacements (&$reps, $creditmemo, $order, $nrtype)
    {
        // TODO
    }
    protected function setupReplacements($nrtype, $info)
    {
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

    protected function applyCustomVariables ($nrtype, $info, $reps, $customvars)
    {
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
            } elseif ($order && $compareval = $order->getData($conditionvar)) {
                // TODO: Handle order property
                $found = true;
            } elseif ($customer && $compareval = $customer->getData($conditionvar)) {
                // TODO: Handle customer property
                $found = true;
            } elseif ($address && $compareval = $address->getData($conditionvar)) {
                // TODO: Handle address property
                $found = true;
            } elseif ($store && $compareval = $store->getData($conditionvar)) {
                // TODO: Handle store property
                $found = true;
            } else {
                // TODO: Handle other possible properties!
                // TODO: Print out warning that variable could not be found.
//                 Mage::getSingleton('core/session')->addWarning($this->__('Unable to find variable "%s" used in the ordernumber custom variable definitions.', $conditionvar));
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

    protected function doReplacements ($fmt, $reps)
    {
        // First, replace all random...[n] fields. This needs to be done with a regexp and a callback:
        $fmt = preg_replace_callback ('/\[(random)(.*?)([0-9]*?)\]/', array($this, 'replaceRandom'), $fmt);
        return str_ireplace (array_keys($reps), array_values($reps), $fmt);
    }

    public function replace_fields ($fmt, $nrtype, $info, $customvars)
    {
        $reps = $this->setupReplacements ($nrtype, $info);
        $reps = $this->applyCustomVariables ($nrtype, $info, $reps, $customvars);
// $this->logitem("All replacements after custom variables: ", $reps);
        return $this->doReplacements($fmt, $reps);
    }


}
