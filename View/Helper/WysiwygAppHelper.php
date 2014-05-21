<?php
/**
 * Wysiwyg App Helper class file.
 *
 * Base WysiwygHelper class
 *
 * Copyright 2009, Jose Diaz-Gonzalez (http://josediazgonzalez.com)
 *
 * Licensed under The MIT License
 *
 * @copyright     Copyright 2009, Jose Diaz-Gonzalez (http://josediazgonzalez.com)
 * @link          http://github.com/josegonzalez/cakephp-wysiwyg-plugin
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('AppHelper', 'View/Helper');

/**
 * Wysiwyg App Helper class
 *
 * WysiwygAppHelper encloses all methods needed while working with HTML pages.
 *
 * @link http://github.com/josegonzalez/cakephp-wysiwyg-plugin
 * @property FormHelper $Form
 * @property HtmlHelper $Html
 * @property JsHelper $Js
 */
class WysiwygAppHelper extends AppHelper {

	/**
	 * Helper dependencies
	 *
	 * @var array
	 */
	public $helpers = array('Form', 'Html', 'Js');

	/**
	 * Whether helper has been initialized once or not
	 *
	 * @var boolean
	 */
	protected $_initialized = false;

	/**
	 * Array of defaults configuration for editors, specified when
	 * importing Wysiwyg in your controller. For example:
	 *
	 *   public $helpers = array(
	 *     'Wysiwyg.Tinymce' => array(
	 *       'theme_advanced_toolbar_align' => 'right',
	 *     )
	 *   );
	 */
	protected $_helperOptions = array();

	/**
	 * Sets the $this->helper to the helper configured in the session.
	 *
	 * @param View $View The View this helper is being attached to.
	 * @param array $settings Configuration settings for the helper.
	 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);

		$defaults = (array)Configure::read('Wysiwyg.default');
		$settings = array_merge(array('_editor' => 'tinymce'), $defaults, (array)$settings);
		$this->_helperOptions = $settings;
	}

	/**
	 * Creates a wsyiwyg input field.
	 *
	 * @param string $fieldName This should be "fieldname"
	 * @param array $options Each type of input takes different options.
	 * @param array $helperOptions Each type of wysiwyg helper takes different options.
	 * @return string An HTML input element with Wysiwyg Js
	 */
	public function input($fieldName, $options = array(), $helperOptions = array()) {
		$model = false;
		$append = false;
		if (!empty($this->request->params['models']) && empty($helperOptions['dontUseModel'])) {
			$model = key($this->request->params['models']);
			$append = '.';// The $fieldName for _build should look like this 'Model.fieldName'
		}
		$helperOptions = $this->_formatOptions($model, $helperOptions);
		return $this->Form->input($fieldName, $options) . $this->_build($model . $append . $fieldName, $helperOptions);
	}

	/**
	 * Creates a wsyiwyg textarea.
	 *
	 * @param string $fieldName This should be "fieldname"
	 * @param array $options Each type of input takes different options.
	 * @param array $helperOptions Each type of wysiwyg helper takes different options.
	 * @return string An HTML textarea element with Wysiwyg Js
	 */
	public function textarea($fieldName, $options = array(), $helperOptions = array()) {
		$model = false;
		$append = false;
		if (!empty($this->request->params['models'])) {
			$model = key($this->request->params['models']);
			$append = '.';
		}
		$helperOptions = $this->_formatOptions($model, $helperOptions);
		return $this->Form->textarea($fieldName, $options) . $this->_build($model . $append . $fieldName, $helperOptions);
	}

	/**
	 * Initializes the helper css and js for a given input field.
	 *
	 * @param array $options array of css files, javascript files, and css text to enqueue
	 * @return void
	 */
	protected function _initialize($options = array()) {
		if ($this->_initialized) {
			return;
		}

		$this->_initialized = true;
		if (!empty($options['_css'])) {
			foreach ((array)$options['_css'] as $css) {
				$this->Html->css($css, array('inline' => false));
			}
		}

		if (!empty($options['_cssText'])) {
			$out = $this->Html->tag('style', $options['_cssText']);
			$this->_View->append('css', $out);
		}

		if (!empty($options['_scripts'])) {
			foreach ((array)$options['_scripts'] as $script) {
				$this->Html->script($script, false);
			}
		}
	}

	/**
	 * Returns a json string containing helper settings.
	 *
	 * @param array $options array of Wysiwyg editor settings
	 * @return string json_encoded array of options
	 */
	protected function _initializationOptions($options = array()) {
		$defaults = array(
			'_buffer' => true,
			'_css' => true,
			'_cssText' => true,
			'_scripts' => true,
			'_editor' => true,
		);

		$settings = array_diff_key(array_merge($defaults, (array)$options), $defaults);

		$value_arr = array();
		$replace_keys = array();
		foreach ($settings as $key => $value) {
			if (strpos($value, 'function(') === 0) {
				$value_arr[] = $value;
				$value = '%' . $key . '%';
				$replace_keys[] = '"' . $value . '"';
				$settings[$key] = $value;
			}
		}
		$json = json_encode($settings);
		$json = str_replace($replace_keys, $value_arr, $json);
		return $json;
	}

	/**
	 * Modifies the option depending on given paremeter.
	 * Different output depending on model.
	 *
	 * @param string $model The name of the current model
	 * @param array $helperOptions Options from the view template
	 * @return array
	 */
	protected function _formatOptions($model, $helperOptions = array()) {
		$defaults = array();
		$setup = array();
		if (isset($helperOptions['template'])) {
			$defaults = (array)Configure::read('Wysiwyg.' . $helperOptions['template']);
			unset($helperOptions['template']);
		}
		$defaults = Set::merge($this->_helperOptions, $defaults);
		if (empty($helperOptions['autoFields'])) {
			return Set::merge($defaults, $helperOptions);
		}
		switch ($model) {
			case 'Offer':
				$setup = array('shop' => array('label' => 'Shopname', 'output' => '{SHOP}'),
						'date' => array('label' => __('Current Date'), 'output' => '{DATE}'),
						'start' => array('label' => __('Startdate'), 'output' => '{START}'),
						'end' => array('label' => __('Enddate'), 'output' => '{END}'),
						'worth' => array('label' => __('Worth'), 'output' => '{VALUE}'),
					);
				break;
			case 'Shop':
				$setup = array('shop' => array('label' => 'Shopname', 'output' => '{SHOP}'),
						'date' => array('label' => __('Current Date'), 'output' => '{DATE}'),
					);
				break;
			default:
				$setup = array();
				break;
		}

		if ($helperOptions['autoFields'] === 'select') {
			$inputName = 'insertButton';
			$defaults['setup'] = 'function(editor) { editor.addButton("' . $inputName . '", { text: "Autofields", type: "menubutton", icon: false, menu: [';
			foreach ($setup as $key => $value) {
				$defaults['setup'] .= '{text: "' . $value['label'] . '", icon: false, onclick: function() { editor.insertContent("' . $value['output'] . '"); }},';
			}
			$defaults['setup'] .= ']});}';
			$defaults['toolbar'] .= ' | '.$inputName;
		} else {
			$defaults['toolbar'] .= ' |';
			$defaults['setup'] = 'function(editor) {';
			foreach ($setup as $key => $value) {
				$defaults['setup'] .= 'editor.addButton("insertButton' . ucfirst($key) . '", { text: "' . $value['label'] . '", icon: false, onclick: function() { editor.insertContent("' . $value['output'] . '"); }});';
				$defaults['toolbar'] .= ' insertButton' . ucfirst($key);
			}
			$defaults['setup'] .= '}';
		}
		unset($helperOptions['autoFields']);
		$helperOptions = Set::merge($defaults, $helperOptions);
		return $helperOptions;
	}

}
