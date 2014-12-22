<?php
class OpenTools_Ordernumber_Block_Replacements extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected $_addRowButtonHtml = array();
	protected $_removeRowButtonHtml = array();

	/**
	 * Returns html part of the setting
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	*/
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$this->setElement($element);

		$html = '';
		$html .= '<table id="ordernumber_replacements_template_table" style="display:none">';
		$html .= $this->_getRowTemplateHtml();
		$html .= '</table>';

		$html .= '<table id="ordernumber_replacements_table">';
		$html .= $this->_getRowHeader();
		if ($this->_getValue('replacements')) {
			foreach ($this->_getValue('replacements') as $i => $f) {
				if ($i) {
					$html .= $this->_getRowTemplateHtml($i);
				}
			}
		}
		$html .= '</table>';

        return '<tr id="row_' . $element->getHtmlId() . '"><td colspan="5">' . $html . '</td></tr>';
	}

	/**
	 * Retrieve html for the table header
	 * @return string
	 */
	protected function _getRowHeader()
	{
		$html = '<tr>';
		$html .= '<th></th>';
		$html .= '<th>Variable</th>';
		$html .= '<th>Condition</th>';
		$html .= '<th>Value</th>';
		$html .= '<th>New Variable</th>';
		$html .= '<th>New Value</th>';
		$html .= '<th>' . $this->_getAddRowButtonHtml('ordernumber_replacements_table',
				'ordernumber_replacements_template_table', 'Add New Replacement') . '</th>';
		$html .= '</tr>';
		return $html;
	}

	/**
	 * Retrieve html template for setting
	 *
	 * @param int $rowIndex
	 * @return string
	 */
	protected function _getRowTemplateHtml($rowIndex = 0)
	{
		$html = '<tr>';

		$html .= '<td>If</td>';
		$html .= '<td><input style="width: 100px" name="' . $this->getElement()->getName() . '[variables][]" value="' . $this->_getValue('variables/' . $rowIndex) . '" ' . $this->_getDisabled() . '/></td>';
// 		$html .= '<td>Condition</td>';
// 		$html .= '<td>Value</td>';
// 		$html .= '<td>New Variable</td>';
// 		$html .= '<td>New Value</td>';

// 		$html .= '<div style="margin:5px 0 10px;">';
// 		$html .= '<input style="width:100px;" name="'
// 				. $this->getElement()->getName() . '[addresses][]" value="'
// 						. $this->_getValue('addresses/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';

		$html .= '<td>' . $this->_getRemoveRowButtonHtml() . '</td>';
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