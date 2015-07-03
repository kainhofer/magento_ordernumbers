<?php
/**
 * Advanced Ordernumbers magento-specific helper class
 * Reinhold Kainhofer, Open Tools, office@open-tools.net
 * @copyright (C) 2012-2015 - Reinhold Kainhofer
 * @license AFL
**/

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
    
    public function print_admin_styles() {
// 		wp_register_style('ordernumber-styles',  $this->urlPath('css', 'ordernumber.css'));
// 		wp_enqueue_style('ordernumber-styles');
	}
	
	public function print_admin_scripts() {
// 		wp_register_script( 'ordernumber-script', $this->urlPath('js', 'ordernumber.js',  __FILE__), array('jquery') );
// 		wp_enqueue_script( 'ordernumber-script');
		
		// Handle the translations:
// 		$localizations = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );
		
// 		$localizations['ORDERNUMBER_JS_JSONERROR'] = $this->__("Error reading response from server:");
// 		$localizations['ORDERNUMBER_JS_NOT_AUTHORIZED'] = $this->__("You are not authorized to modify order number counters.");
// 		$localizations['ORDERNUMBER_JS_NEWCOUNTER'] = $this->__("Please enter the format/name of the new counter:");
// 		$localizations['ORDERNUMBER_JS_ADD_FAILED'] = $this->__("Failed adding counter {0}");
// 		$localizations['ORDERNUMBER_JS_INVALID_COUNTERVALUE'] = $this->__("You entered an invalid value for the counter.\n\n");
		
// 		$localizations['ORDERNUMBER_JS_EDITCOUNTER'] = $this->__("{0}Please enter the new value for the counter '{1}' (current value: {2}):");
// 		$localizations['ORDERNUMBER_JS_MODIFY_FAILED'] = $this->__("Failed modifying counter {0}");
// 		$localizations['ORDERNUMBER_JS_DELETECOUNTER'] = $this->__("Really delete counter '{0}' with value '{1}'?");
// 		$localizations['ORDERNUMBER_JS_DELETE_FAILED'] = $this->__("Failed deleting counter {0}");

		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
// 		wp_localize_script( 'ordernumber-script', 'ajax_ordernumber', $localizations );
	}




 	function getAllCounters($type) {
		$counters = array();
// 		$pfxlen = strlen(self::$ordernumber_counter_prefix );
// 		foreach (wp_load_alloptions() as $name => $value) {
// 			if (substr($name, 0, $pfxlen) == self::$ordernumber_counter_prefix) {
// 				$parts = explode('-', substr($name, $pfxlen), 2);
// 				if (sizeof($parts)==1) {
// 					array_unshift($parts, 'ordernumber');
// 				}
// 				if ($parts[0]==$type) {
// 					$counters[] = array(
// 						'type'  => $parts[0],
// 						'name'  => $parts[1],
// 						'value' => $value,
// 					);
// 				}
// 			}
// 		}
		return $counters;
	}

    function getCounter($type, $format, $default=0) {
		return get_option (self::$ordernumber_counter_prefix.$type.'-'.$format, $default);
	}
    
	function addCounter($type, $format, $value) {
		return $this->setCounter($type, $format, $value);
	}

	function setCounter($type, $format, $value) {
		return update_option(self::$ordernumber_counter_prefix.$type.'-'.$format, $value);
	}

	function deleteCounter($type, $format) {
		return delete_option(self::$ordernumber_counter_prefix.$type.'-'.$format);
	}


}
