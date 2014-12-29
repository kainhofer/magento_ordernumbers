<?php
class OpenTools_Ordernumber_Block_Counters extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected $_addRowButtonHtml = array();
	protected $_addRowDeleteButtonHtml = array();
	protected $_editRowButtonHtml = array();
	protected $_editCancelRowButtonHtml = array();
	protected $_deleteRowButtonHtml = array();
	protected $_undeleteRowButtonHtml = array();
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

        $html = '';
		$html .= '<table id="ordernumber_counters_template_table" style="display:none">';
		$html .= $this->_getRowHtmlNew();
		$html .= '</table>';

		$html .= '<div class="grid"><table id="ordernumber_counters_table" class="data" style="width: 100%;">';
		$html .= $this->_getRowHeader();

		$collection = $this->getModel()->getCollection()->addOrder('number_type', Varien_Data_Collection::SORT_ORDER_ASC)->addOrder('number_format', Varien_Data_Collection::SORT_ORDER_ASC);
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
		$html .= '<th>'. $this->__('Counter Type') . '</th>';
		$html .= '<th>'. $this->__('Scope') . '</th>';
		$html .= '<th>'. $this->__('Counter Name') . '</th>';
		$html .= '<th>'. $this->__('Counter') . '</</th>';
		$html .= '<th>' . $this->_getAddRowButtonHtml('ordernumber_counters_table',
				'ordernumber_counters_template_table', 'Add New Counter') . '</th>';
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
		$html .= '<td>' . $this->_getNumberTypeSelect($this->getElement()->getName() . '[new_counter_type][]', true) . '</td>';
		$html .= '<td>' . $this->_getNumberScopeSelect($this->getElement()->getName() . '[new_counter_scope][]', true) . '</td>';
		$html .= '<td><input type="text" name="' . $this->getElement()->getName() . '[new_counter_name][]" disabled /></td>';
		$html .= '<td><input class="counter_edit  validate-not-negative-number input-text" type="text" name="' . $this->getElement()->getName() . '[new_counter_value][]" disabled /></td>';
		$html .= '<td>' . $this->_getAddRowDeleteButtonHtml() . '</td>';
		$html .= '</tr>';
		return $html;
	}

	protected function _getNumberTypeSelect($name, $id='', $disabled=true, $current=null)
	{
	    $options = array();
	    foreach ($this->getNumberTypes() as $type=>$label) {
	        $options[] = array('value'=>$type, 'label'=>$label);
	    }
		$html = $this->getLayout()->createBlock('core/html_select')
		    ->setName($name)
		    ->setClass($this->_getDisabled($disabled))
		    ->setValue($current)
		    ->setOptions($options)
		    ->setExtraParams($this->_getDisabled($disabled))
			->toHtml();
		return $html;
	}

	/**
	 * Retrieve stores structure; Adjusted from Mage_Adminhtml_Model_System_Store::getStoreValuesForForm
	 * to also include "All stores|views of website|store: ..." in the possible selections
	 * Also adjusted the values of the options
	 *
	 * @param bool $isAll
	 * @param array $storeIds
	 * @param array $groupIds
	 * @param array $websiteIds
	 * @return array
	 */
	public function _getScoreStructureOptions($isAll = false, $websiteIds = array(), $groupIds = array(), $storeIds = array())
	{
	    $sstore = Mage::getSingleton('adminhtml/system_store');
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
	    $out = array();

	    if ($isAll) {
	        $out[] = array(
	                'value' => '',
	                'label' => $this->__('Global')
	        );
	    }

	    $websites = $sstore->getWebsiteCollection();
	    foreach ($websites as $website) {
	        $websiteId = $website->getId();
	        $siteStr = (int)$websiteId;
	        if ($websiteIds && !in_array($websiteId, $websiteIds)) {
	            continue;
	        }
	        $out[] = array(
	                'value' => $siteStr,
	                'label' => $this->__('All stores of website: %s', $website->getName())
	        );
	        // We cannot nest optiongroups, so we have to emulate it by inserting an empty optiongroup and manually indenting the groups and stores
	        $out[] = array(
	                'value' => array(),
	                'label' => $this->__('Stores of website: %s', $website->getName()),
	        );

	        foreach ($website->getGroups() as $group) {
	            $groupId = $group->getId();
	            $groupStr = $siteStr . '/' . (int)$groupId;

	            if ($groupIds && !in_array($groupId, $groupIds)) {
	                continue;
	            }

	            $out[] = array(
	                    'value' => $groupStr,
	                    'label' => str_repeat($nonEscapableNbspChar, 4) . $this->__('All views of store: %s', $group->getName()),
	            );

	            $stores = array();
	            foreach ($group->getStores() as $store) {

	                $storeId = $store->getId();
	                $storeStr = $groupStr . '/' . (int)$storeId;
	                if ($storeIds && !in_array($storeId, $storeIds)) {
	                    continue;
	                }
	                $stores[] = array(
	                        'value' => $storeStr,
	                        'label' => str_repeat($nonEscapableNbspChar, 4) . $store->getName(),
	                );
	            }
	            if (!empty($stores)) {
	                $out[] = array(
	                        'value' => $stores,
	                        'label' => str_repeat($nonEscapableNbspChar, 4) . $this->__('Views of store: %s', $group->getName()),
	                );
	            }
	        }
	    }
	    return $out;
	}

	protected function _getNumberScopeSelect($name, $disabled=true, $current=null)
	{
        $options = $this->_getScoreStructureOptions(true);
	    $html = $this->getLayout()->createBlock('core/html_select')
	        ->setName($name)
	        ->setClass($this->_getDisabled($disabled))
	        ->setValue($current)
    	    ->setOptions($options)
	        ->setExtraParams($this->_getDisabled($disabled))
	        ->toHtml();
	    return $html;
	}

	/** Convert the scope id ('' for global, websiteID/groupID/storeID)
	 *  to a human-readable string
	 *
	 * @param $scope_ids scope id ('' for global, websiteID/groupID/storeID)
	 * @return string
	 */
	protected function _convertScopeToString($scope_ids)
	{
	    $sstore = Mage::getSingleton('adminhtml/system_store');
		$nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
		$ids = explode ('/', $scope_ids);

	    $scopes = array();
	    if (empty($ids) || empty($ids[0])) {
	        $scopes[] = $this->__('Global');
	    } else {
	        $website = Mage::getModel('core/website')->load($ids[0]);
	        if ($website)
    	        $scopes[] = $this->__('Website: %s', $website->getName());
	    }
	    if (count($ids)>1) {
	        $group = Mage::getModel('core/store_group')->load($ids[1]);
	        if ($group)
    	        $scopes[] = str_repeat($nonEscapableNbspChar, 4) . $this->__('Store: %s', $group->getName());
	    }
	    if (count($ids)>2) {
	        $store = Mage::getModel('core/store')->load($ids[2]);
	        if ($store)
    	        $scopes[] = str_repeat($nonEscapableNbspChar, 8) . $this->__('View: %s', $store->getName());
	    }
	    return implode('<br />', $scopes);
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
		$html .= '<td class="ordernumber_scope">' . $this->_convertScopeToString($counter->getNumberScope()) . '</td>';
		$html .= '<td class="ordernumber_name">' . $counter->getNumberFormat() . '</td>';
		$html .= '<td class="ordernumber_counter">' .
		           '<div class="">' . (int)$counter->getCount() . '</div>' .
		           '<input type="hidden" disabled class="oton-counter-formelement oton-counter-edit oton-counter-delete" name="' . $this->getElement()->getName() . '[oldcounter][' . (int)$counter->getId() . ']" value="' . (int)$counter->getCount() . '" />' .
		           '<input style="display:none" disabled="disabled" class="oton-counter-formelement oton-counter-edit validate-not-negative-number input-text" name="' . $this->getElement()->getName() . '[counter][' . (int)$counter->getId() . ']" value="' . (int)$counter->getCount() . '" ' . $this->_getDisabled() . '/>' .
		         '</td>';

		$html .= '<td>' .
		          $this->_getEditRowButtonHtml() .
		          $this->_getEditCancelRowButtonHtml() .
		          '<input type="hidden" disabled class="oton-counter-formelement oton-counter-delete" name="'. $this->getElement()->getName() . '[deletecounter][]" value="' . (int)$counter->getId() . '" />' .
		          $this->_getDeleteRowButtonHtml() .
		          $this->_getUndeleteRowButtonHtml() .
		          '</td>';
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
	            $('" . $template . "').select('input, select, button').invoke('disable').invoke('addClassName', 'disabled'); ";
	    return $js;
	}
	protected function _getJSEditRow($selector) {
	    $js = "
	            $(this).up('" . $selector . "').select('.oton-counter-formelement').invoke('hide').invoke('disable').invoke('addClassName', 'disabled');
	            $(this).up('" . $selector . "').select('.oton-counter-edit').invoke('show').invoke('enable').invoke('removeClassName', 'disabled');";
	    return $js;

	}
	protected function _getJSEditCancelRow($selector) {
	    $js = "
	            $(this).up('" . $selector . "').select('.oton-counter-formelement').invoke('hide').invoke('disable').invoke('addClassName', 'disabled');
	            $(this).up('" . $selector . "').select('.oton-counter-display').invoke('show').invoke('enable').invoke('removeClassName', 'disabled');";
	    return $js;

	}
	protected function _getJSDeleteRow($selector) {
		$js = "
		        var tr=$(this).up('" . $selector . "');
		        tr.select('.oton-counter-formelement').invoke('hide').invoke('disable').invoke('addClassName', 'disabled');
		        tr.select('.oton-counter-delete').invoke('show').invoke('enable').invoke('removeClassName', 'disabled');
		        tr.setStyle({textDecoration: 'line-through'});";
	    return $js;
	}
	protected function _getJSUndeleteRow($selector) {
		$js = "
		        var tr=$(this).up('" . $selector . "');
		        tr.select('.oton-counter-formelement').invoke('hide').invoke('disable').invoke('addClassName', 'disabled');
		        tr.select('.oton-counter-display').invoke('show').invoke('enable').invoke('removeClassName', 'disabled');
		        tr.setStyle({textDecoration: ''});";
		return $js;
	}
	protected function _getJSDeleteNewRow($selector) {
	    $js = "Element.remove($(this).up('" . $selector . "'))";
		return $js;
	}



	protected function _getAddRowDeleteButtonHtml($selector = 'tr', $title='Delete')
	{
		if (!$this->_addRowDeleteButtonHtml) {
			$this->_addRowDeleteButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('delete v-middle oton-counter-add-delete' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick($this->_getJSDeleteNewRow($selector))
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
			->setClass('add oton-counter-add' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick($this->_getJSAddRow($container, $template))
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
			->setClass('edit v-middle oton-counter-formelement oton-counter-display' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick($this->_getJSEditRow($selector))
			->setDisabled($this->_getDisabled())
			->toHtml();
		}
		return $this->_editRowButtonHtml;
	}

	protected function _getEditCancelRowButtonHtml($selector = 'tr', $title='Cancel')
	{
		if (!$this->_editCancelRowButtonHtml) {
			$this->_editCancelRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('cancel v-middle oton-counter-formelement oton-counter-edit' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick($this->_getJSEditCancelRow($selector))
			->setDisabled($this->_getDisabled())
	        ->setStyle('display: none;')
			->toHtml();
		}
		return $this->_editCancelRowButtonHtml;
	}

	protected function _getDeleteRowButtonHtml($selector = 'tr', $title = 'Delete')
	{
		if (!$this->_deleteRowButtonHtml) {
			$this->_deleteRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setType('button')
			->setClass('delete v-middle oton-counter-formelement oton-counter-display oton-counter-edit' . $this->_getDisabled())
			->setLabel($this->__($title))
			->setOnClick($this->_getJSDeleteRow($selector))
			->setDisabled($this->_getDisabled())
			->toHtml();
		}
		return $this->_deleteRowButtonHtml;
	}
	protected function _getUndeleteRowButtonHtml($selector = 'tr', $title = 'Restore')
	{
	    if (!$this->_undeleteRowButtonHtml) {
	        $this->_undeleteRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
	        ->setType('button')
	        ->setClass('v-middle oton-counter-formelement oton-counter-delete')
	        ->setLabel($this->__($title))
	        ->setOnClick($this->_getJSUndeleteRow($selector))
	        ->setDisabled($this->_getDisabled() || true)
	        ->setStyle('display: none;')
	        ->toHtml();
	    }
	    return $this->_undeleteRowButtonHtml;
	}


}