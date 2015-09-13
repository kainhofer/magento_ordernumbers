<?php
/**
 * Advanced Ordernumbers magento-specific helper class
 * Reinhold Kainhofer, Open Tools, office@open-tools.net
 * @copyright (C) 2012-2015 - Reinhold Kainhofer
 * @license AFL
**/

defined ('_OPENTOOLS_ORDERNUMBER_FRAMEWORK') or define('_OPENTOOLS_ORDERNUMBER_FRAMEWORK', 1);
if (!class_exists( 'OrdernumberHelper' )) 
	require_once (dirname(__FILE__) . '/library/ordernumber_helper.php');

class OrdernumberHelperMagento extends OrdernumberHelper {
	protected $maghelper = null;

	function __construct($helper) {
		parent::__construct();
		$this->maghelper = $helper;
		// TODO: Load translations
// 		load_plugin_textdomain('opentools-ordernumbers', false, basename( dirname( __FILE__ ) ) . '/languages' );
		// Magento-specific Defaults for the HTML tables
// 		$this->_styles['counter-table-class']  = "widefat";
// 		$this->_styles['variable-table-class'] = "widefat wc_input_table sortable";
	}

	static function getHelper() {
		static $helper = null;
		if (!$helper) {
			$helper = new OrdernumberHelperMagento();
		}
		return $helper;
	}
	
	/**
	 * HELPER FUNCTIONS, Magento-specific
	 */
	public function __($string) {
		$string = $this->readableString($string);
		return $this->maghelper->__($string);
	}
	function urlPath($type, $file) {
		// TODO
// 		return plugins_url('library/' . $type . '/' . $file, __FILE__);
    }

	function getAllCounters($type) {
		// This function is not implemented for Magento, as we don't use the 
		// library's counter modification table, but a Magento-customized
		// counter modification table without AJAX calls!
		return array();
	}

    function getCounter($type, $format, $default=0) {
    // TODO...
// 		return get_option (self::$ordernumber_counter_prefix.$type.'-'.$format, $default);
	}

	function addCounter($type, $format, $value) {
		return $this->setCounter($type, $format, $value);
	}

	function setCounter($type, $format, $value) {
		// TODO...
// 		return update_option(self::$ordernumber_counter_prefix.$type.'-'.$format, $value);
	}

	function deleteCounter($type, $format) {
		// TODO...
// 		return delete_option(self::$ordernumber_counter_prefix.$type.'-'.$format);
	}

}
