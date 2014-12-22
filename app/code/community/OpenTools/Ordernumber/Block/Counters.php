<?php
class OpenTools_Ordernumber_Block_Counters extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected $_addRowButtonHtml = array();
	protected $_addRowDeleteButtonHtml = array();
	protected $_editRowButtonHtml = array();
	protected $_removeRowButtonHtml = array();
	protected $_types = null;

	protected $_dbModel = null;
	protected function _getModel() {
	    return Mage::getModel('opentools_ordernumber/ordernumber');
	}
	public function getModel() {
	    if (is_null($this->_dbModel))
	        $this->_dbModel = $this->_getModel();
	    return $this->_dbModel;
	}

	public function getNumberTypes() {
	    if (is_null($this->_types)) {
	        $this->_types = $this->getModel()->getNumberTypes();
	    }
	    return $this->_types;
	}

	function logitem($label, $item) {
		Mage::Log($label . " " . get_class($item) . "\n", null, 'ordernumber.log');
		Mage::Log(is_array($item)?$item:$item->debug(), null, 'ordernumber.log');
		Mage::Log(get_class_methods(get_class($item)), null, 'ordernumber.log');
	}

	/**
	 * Returns html part of the setting
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	*/
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$this->setElement($element);

// $this->logitem("adminhtml/system_store: ", Mage::getSingleton('adminhtml/system_store'));
// $this->logitem("adminhtml/system_store store values for form: ", Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, true));
// $this->logitem("adminhtml/system_store website values for form: ", Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(true, true));
// $this->logitem("adminhtml/system_store stores structure: ", Mage::getSingleton('adminhtml/system_store')->getStoresStructure(true));
        $html = '';
		$html .= '<table id="ordernumber_counters_template_table" style="display:none">';
		$html .= $this->_getRowHtmlNew();
		$html .= '</table>';

		$html .= '<div class="grid"><table id="ordernumber_counters_table" class="data" style="width: 100%;">';
		$html .= $this->_getRowHeader();

		$collection = $this->getModel()->getCollection();
		foreach ($collection as $counter) {
			$html .= $this->_getRowHtml($counter);
		}

		$html .= '</table></div>';

        return '<tr id="row_' . $element->getHtmlId() . '"><td colspan="5">' . $html . '</td></tr>';
	}

	/**
	 * Retrieve html for the table header
	 * @return string
	 */
	protected function _getRowHeader()
	{
		$html  = '<tr class="headings">';
		$html .= '<th>Counter Type</th>';
		$html .= '<th>Scope</th>';
		$html .= '<th>Counter Name</th>';
		$html .= '<th>Counter</th>';
		$html .= '<th>' . $this->_getAddRowButtonHtml('ordernumber_counters_table',
				'ordernumber_counters_template_table', $this->__('Add New Counter')) . '</th>';
		$html .= '</tr>';
		return $html;
	}

	/**
	 * Retrieve html template for new counters
	 *
	 * @return string
	 */
	protected function _getRowHtmlNew()
	{
		$html  = '<tr>';
		$html .= '<td>' . $this->_getNumberTypeSelect($this->getElement()->getName() . '[new_counter_type][]') . '</td>';
		// TODO: Turn the text input to a select box
		$html .= '<td>' . $this->_getNumberScopeSelect($this->getElement()->getName() . '[new_counter_scope][]') . '</td>';
		$html .= '<td><input type="text" name="' . $this->getElement()->getName() . '[new_counter_name][]" /></td>';
		$html .= '<td><input class="counter_edit  validate-not-negative-number input-text" type="text" name="' . $this->getElement()->getName() . '[new_counter_value][]" /></td>';
		$html .= '<td>' . $this->_getAddRowDeleteButtonHtml() . '</td>';
		$html .= '</tr>';
		return $html;
	}

	protected function _getNumberTypeSelect($name, $id='', $current=null)
	{
	    $options = array();
	    foreach ($this->getNumberTypes() as $type=>$label) {
	        $options[] = array('value'=>$type, 'label'=>$label);
	    }
		$html = $this->getLayout()->createBlock('core/html_select')
		    ->setName($name)
		    ->setValue($current)
		    ->setOptions($options)
		    ->setDisabled($this->_getDisabled)
			->toHtml();
		return $html;
	}

	protected function _getNumberScopeSelect($name, $id='', $current=null)
	{
	    // TODO: Create the tree similar to ./app/code/core/Mage/Adminhtml/Model/System/Store.php,
	    // but let the user select websites and groups, too.
// 	    $options = Mage::getSingleton('adminhtml/system_store')->getStoresStructure(true);
	    $options = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, true);
	    $html = $this->getLayout()->createBlock('core/html_select')
	        ->setName($name)
	        ->setValue($current)
    	    ->setOptions($options)
	        ->setDisabled($this->_getDisabled)
	        ->toHtml();
	    return $html;
	}

	/**
	 * Retrieve html template for setting
	 *
	 * @param int $rowIndex
	 * @return string
	 */
	protected function _getRowHtml($counter)
	{
	    static $class='odd';

		$html  = '<tr class="'.$class.'">';
		$class = ($class=='odd')?'even':'odd';
		$types = $this->getNumberTypes();
		$html .= '<td class="ordernumber_type">'.$types[$counter->getNumberType()].'</td>';

		$scopes = array();
		if ($counter->getWebsiteId()==0) {
			$scopes[] = $this->__('Global');
		} else {
			$scopes[] = $this->__('Website #').(int)$counter->getWebsiteId();
		}
    	if ($counter->getGroupId()!=0) {
			$scopes[] = $this->__('Group #').(int)$counter->getGroupId();
    	}
		if ($counter->getStoreId()!=0) {
			$scopes[] = $this->__('Store #').(int)$counter->getStoreId();
		}
		$html .= '<td class="ordernumber_scope">' . implode('<br />', $scopes) . '</td>';
		$html .= '<td class="ordernumber_name">' . $counter->getNumberFormat() . '</td>';
		$html .= '<td class="ordernumber_counter">' . (int)$counter->getCount() .
		           '<input type="hidden" name="' . $this->getElement()->getName() . '[oldcounter][' . (int)$counter->getId() . ']" value="' . (int)$counter->getCount() . '" />' .
		           '<input style="display:none" class="counter_edit  validate-not-negative-number input-text" name="' . $this->getElement()->getName() . '[counter][' . (int)$counter->getId() . ']" value="' . (int)$counter->getCount() . '" ' . $this->_getDisabled() . '/>' .
		         '</td>';

		$html .= '<td>' . $this->_getEditRowButtonHtml() . $this->_getRemoveRowButtonHtml() . '</td>';
		$html .= '</tr>';

		return $html;
	}

   protected function _getDisabled()
   {
       return $this->getElement()->getDisabled() ? ' disabled' : '';
   }

	protected function _getValue($key)
	{
		return $this->getElement()->getData('value/' . $key);
	}

	protected function _getSelected($key, $value)
	{
		return $this->getElement()->getData('value/' . $key) == $value ? 'selected="selected"' : '';
	}

	protected function _getAddRowDeleteButtonHtml($selector = 'tr', $title='Delete')
	{
		if (!$this->_addRowDeleteButtonHtml) {
			$this->_addRowDeleteButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('delete v-middle ' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick("Element.remove($(this).up('" . $selector . "'))")
			->setDisabled($this->_getDisabled())
			->toHtml();
		}
		return $this->_addRowDeleteButtonHtml;
	}

	protected function _getAddRowButtonHtml($container, $template, $title='Add')
	{
		if (!isset($this->_addRowButtonHtml[$container])) {
			$this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('add ' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick("Element.insert($('" . $container . "'), {bottom: $('" . $template . "').innerHTML})")
			->setDisabled($this->_getDisabled())
			->toHtml();
		}
		return $this->_addRowButtonHtml[$container];
	}

	protected function _getEditRowButtonHtml($selector = 'tr', $title='Edit')
	{
		if (!$this->_editRowButtonHtml) {
			$this->_editRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('edit v-middle ' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick("Element.toggle(Element.select($(this).up('" . $selector . "'), 'input.counter_edit'))")
			->setDisabled($this->_getDisabled())
			->toHtml();
		}
		return $this->_editRowButtonHtml;
	}

	protected function _getRemoveRowButtonHtml($selector = 'tr', $title = 'Delete')
	{
		if (!$this->_removeRowButtonHtml) {
			$this->_removeRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('delete v-middle ' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick("Element.remove($(this).up('" . $selector . "'))")
			->setDisabled($this->_getDisabled())
			->toHtml();
		}
		return $this->_removeRowButtonHtml;
	}


}