<?php

/**
 * jQuery combobox Yii extension
 * 
 * Allows selecting a value from a dropdown list or entering in text.
 * Also works as an autocomplete for items in the select.
 *
 * @copyright © Digitick <www.digitick.net> 2011
 * @license GNU Lesser General Public License v3.0
 * @author Ianaré Sévi
 * @author Jacques Basseck
 *
 */
Yii::import('zii.widgets.jui.CJuiInputWidget');

/**
 * Base class.
 */
class EJuiComboBox extends CJuiInputWidget
{
	/**
	 * @var array the entries that the autocomplete should choose from.
	 */
	public $data = array();
	
	/**
	 * @var bool whether the $data is an associative array or not
	 */
	public $assoc = true;
	
	/**
	 * @var string - name of the text field (if custom text is allowed)
	 */
	public $textFieldName = 'text';
	
	public $textFieldValue;
	
	public $textFieldAttribute;
	
	/**
	 * @var string A jQuery selector used to apply the widget to the element(s).
	 * Use this to have the elements keep their binding when the DOM is manipulated
	 * by Javascript, ie ajax calls or cloning.
	 * Can also be useful when there are several elements that share the same settings,
	 * to cut down on the amount of JS injected into the HTML.
	 */
	public $scriptSelector;
	public $defaultOptions = array('allowText' => true);

	protected function setSelector($id, $script, $event=null)
	{
		if ($this->scriptSelector) {
			if (!$event)
				$event = 'focusin';
			$js = "jQuery('body').delegate('{$this->scriptSelector}','{$event}',function(e){\$(this).{$script}});";
			$id = $this->scriptSelector;
		}
		else
			$js = "jQuery('#{$id}').{$script}";
		return array($id, $js);
	}
    
	/**
	 * (non-PHPDoc)
	 * @see parent::init()
	 */
	public function init()
	{
		$cs = Yii::app()->getClientScript();
		$assets = Yii::app()->getAssetManager()->publish(dirname(__FILE__) . '/assets');
		//$cs->registerScriptFile($assets . '/jquery.ui.widget.min.js');
		$cs->registerScriptFile($assets . '/jquery.ui.combobox.js');

		parent::init();
	}

	/**
	 * Run this widget.
	 * This method registers necessary javascript and renders the needed HTML code.
	 */
	public function run()
	{
		list($name, $id) = $this->resolveNameID();
        //CVarDumper::dump($this->attribute, 10, true);die;
		if (is_array($this->data) && !empty($this->data)){
			//if $data is not an assoc array make each value its key
			$data=($this->assoc)?$this->data:array_combine($this->data, $this->data);
			
			//does the same as array_unshift($data,null) but does not break assoc arrays
			$data=array(""=>"")+$data;
		} else {
		    $data = array();
		}
		
		$this->options = array_merge($this->defaultOptions, $this->options);
		
		if ($this->hasModel())
		{
		    echo CHtml::activeDropDownList($this->model, $this->attribute, $data);
		    $attributeName = mb_ereg_replace('\[[0-9a-zA-Z_ -]{0,255}\]', '', $this->attribute);
		    $textFieldName = mb_ereg_replace($attributeName, $this->textFieldName, $name);
		    $textFieldValue = CHtml::resolveValue($this->model, $this->textFieldAttribute);
		}else
		{
		    echo CHtml::dropDownList($name, $this->value, $data);
		    // @todo set text field name without model
		    $textFieldName = $this->textFieldName;
		    $textFieldValue = $this->textFieldValue;
		}
	    
		echo CHtml::textField($textFieldName, $textFieldValue, array('id'=>$id.'_combobox'));
	    
		$options = CJavaScript::encode($this->options);

		$cs = Yii::app()->getClientScript();

		$js = "combobox({$options});";

		list($id, $js) = $this->setSelector($id.'_combobox', $js);
		$cs->registerScript(__CLASS__ . '#' . $id, $js);
	}

}