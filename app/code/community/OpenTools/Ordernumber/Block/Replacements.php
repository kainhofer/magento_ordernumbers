<?php
class OpenTools_Ordernumber_Block_Replacements extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected $_addRowButtonHtml = array();
	protected $_deleteRowButtonHtml = array();

// 	function logitem($label, $item) {
// 		Mage::Log($label . " " . get_class($item) . "\n", null, 'ordernumber.log');
// 		Mage::Log(is_array($item)?$item:$item->debug(), null, 'ordernumber.log');
// 		Mage::Log(get_class_methods(get_class($item)), null, 'ordernumber.log');
// 	}

	/**
	 * Returns html part of the setting.
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	*/
	public function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$this->setElement($element);

        $html = '';
		$html .= '<table id="ordernumber_replacements_template_table" style="display:none">';
		$html .= $this->_getRowHtml();
		$html .= '</table>';

		$html .= '<div class="grid"><table id="ordernumber_replacements_table" class="data" style="width: 100%;">';
		$html .= $this->_getRowHeader();

// $this->logitem("render element: ", $element);
		if ($this->_getValue('conditionvar')) {
		    foreach ($this->_getValue('conditionvar') as $i => $var) {
                $html .= $this->_getRowHtml($i);
		    }
		} else {
		    $html .= $this->_getEmptyRowHtml();
		}

		$html .= '</table></div>';
		return $html;
	}

	/**
	 * Render the whole table row for the form field.
	 * Adjusted from ./app/code/core/Mage/Adminhtml/Block/System/Config/Form/Field.php
	 * Adjustments: Removed label, form control spans two columns.
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
	    $id = $element->getHtmlId();

	    //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
	    $isMultiple = $element->getExtType()==='multiple';

	    // replace [value] with [inherit]
	    $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

	    $options = $element->getValues();

	    $addInheritCheckbox = false;
	    if ($element->getCanUseWebsiteValue()) {
	        $addInheritCheckbox = true;
	        $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
	    }
	    elseif ($element->getCanUseDefaultValue()) {
	        $addInheritCheckbox = true;
	        $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
	    }

	    if ($addInheritCheckbox) {
	        $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
	        if ($inherit) {
	            $element->setDisabled(true);
	        }
	    }

	    if ($element->getTooltip()) {
	        $html .= '<td class="value with-tooltip"  colspan="2">';
	        $html .= $this->_getElementHtml($element);
	        $html .= '<div class="field-tooltip"><div>' . $element->getTooltip() . '</div></div>';
	    } else {
	        $html .= '<td class="value">';
	        $html .= $this->_getElementHtml($element);
	    };
	    if ($element->getComment()) {
	        $html.= '<p class="note"><span>'.$element->getComment().'</span></p>';
	    }
	    $html.= '</td>';

	    if ($addInheritCheckbox) {

	        $defText = $element->getDefaultValue();
	        if ($options) {
	            $defTextArr = array();
	            foreach ($options as $k=>$v) {
	                if ($isMultiple) {
	                    if (is_array($v['value']) && in_array($k, $v['value'])) {
	                        $defTextArr[] = $v['label'];
	                    }
	                } elseif ($v['value']==$defText) {
	                    $defTextArr[] = $v['label'];
	                    break;
	                }
	            }
	            $defText = join(', ', $defTextArr);
	        }

	        // default value
	        $html.= '<td class="use-default">';
	        $html.= '<input id="' . $id . '_inherit" name="'
	                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
	                        . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
	        $html.= '<label for="' . $id . '_inherit" class="inherit" title="'
	                . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
	        $html.= '</td>';
	    }

	    $html.= '<td class="scope-label">';
	    if ($element->getScope()) {
	        $html .= $element->getScopeLabel();
	    }
	    $html.= '</td>';

	    $html.= '<td class="">';
	    if ($element->getHint()) {
	        $html.= '<div class="hint" >';
	        $html.= '<div style="display: none;">' . $element->getHint() . '</div>';
	        $html.= '</div>';
	    }
	    $html.= '</td>';

	    return $this->_decorateRowHtml($element, $html);
	}


	/**
	 * Retrieve html for the table header
	 * @return string
	 */
	protected function _getRowHeader()
	{
		$html  = '<tr class="headings">';
		$html .= '<th>'. $this->__('If Variable ...') . '</th>';
		$html .= '<th>'. $this->__('is / contains ...') . '</th>';
		$html .= '<th></th>';
		$html .= '<th>'. $this->__('Set this variable ..') . '</th>';
		$html .= '<th>'. $this->__('to value ...') . '</th>';
		$html .= '<th>' . $this->_getAddRowButtonHtml('ordernumber_replacements_table',
				'ordernumber_replacements_template_table', 'Add New Replacement') . '</th>';
		$html .= '</tr>';
		return $html;
	}

	/**
	 * Retrieve html template for variable replacements
	 *
	 * @param int $rowIndex
	 * @return string
	 */
	protected function _getRowHtml($rowIndex = -1)
	{
	    static $class = 'odd';
		$html  = '<tr class="'.$class.'">';
		$class = ($class=='odd')?'even':'odd';
		$html .= '<td class="oton-replacement-variable"><input name="' . $this->getElement()->getName() . '[conditionvar][]" value="' . $this->_getValue('conditionvar/' . $rowIndex) . '" ' . $this->_getDisabled($rowIndex == -1) . '/></td>';
		$html .= '<td class="oton-replacement-value"   ><input name="' . $this->getElement()->getName() . '[conditionval][]" value="' . $this->_getValue('conditionval/' . $rowIndex) . '" ' . $this->_getDisabled($rowIndex == -1) . '/></td>';
		$html .= '<td>=></td>';
		$html .= '<td class="oton-replacement-variable"><input name="' . $this->getElement()->getName() . '[newvar][]"       value="' . $this->_getValue('newvar/' . $rowIndex) .       '" ' . $this->_getDisabled($rowIndex == -1) . '/></td>';
		$html .= '<td class="oton-replacement-newvalue"><input name="' . $this->getElement()->getName() . '[newval][]"       value="' . $this->_getValue('newval/' . $rowIndex) .       '" ' . $this->_getDisabled($rowIndex == -1) . '/></td>';
		$html .= '<td>' . $this->_getDeleteRowButtonHtml() . '</td>';
		$html .= '</tr>';

		return $html;
	}

	/**
	 * Display notice that no custom variables have been defined...
	 */
	protected function _getEmptyRowHtml()
	{
	    $html  = '<tr class="oton-empty-row-notice">';
	    $html .= '<td class="oton-empty-row-notice" colspan="6"><em>' . $this->__('No custom variables have been defined.') . '</em></td>';
	    $html .= '</tr>';
	    return $html;
	}

    protected function _getDisabled($forceDisabled=false)
    {
        return ($forceDisabled || $this->getElement()->getDisabled()) ? ' disabled' : '';
    }

	protected function _getValue($key)
	{
		return $this->getElement()->getData('value/' . $key);
	}

	protected function _getSelected($key, $value)
	{
		return $this->getElement()->getData('value/' . $key) == $value ? 'selected="selected"' : '';
	}

	protected function _getJSAddRow($container, $template) {
	    $js  = "
	            var tmpl=$('" . $template . "');
	            Form.getElements(tmpl).invoke('enable').invoke('removeClassName', 'disabled');
	            var newc=$('" . $template . "').innerHTML;
	            Element.insert($('" . $container . "'), {bottom: newc});
	            $('" . $template . "').select('input, select, button').invoke('disable').invoke('addClassName', 'disabled');
	            $(this).up('table').select('tr.oton-empty-row-notice').invoke('hide');";

	    return $js;
	}
	protected function _getJSDeleteRow($selector) {
	    $js = "Element.remove($(this).up('" . $selector . "'))";
		return $js;
	}



	protected function _getAddRowButtonHtml($container, $template, $title='Add')
	{
		if (!isset($this->_addRowButtonHtml[$container])) {
			$this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('add oton-replacement-add' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick($this->_getJSAddRow($container, $template))
			->setDisabled($this->_getDisabled())
			->toHtml();
		}
		return $this->_addRowButtonHtml[$container];
	}

	protected function _getDeleteRowButtonHtml($selector = 'tr', $title = 'Delete')
	{
		if (!$this->_deleteRowButtonHtml) {
			$this->_deleteRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('delete v-middle oton-replacement-formelement oton-replacement-display oton-replacement-edit' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick($this->_getJSDeleteRow($selector))
			->setDisabled($this->_getDisabled())
			->toHtml();
		}
		return $this->_deleteRowButtonHtml;
	}

}