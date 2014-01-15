<?php
App::uses('WysiwygAppHelper', 'Wysiwyg.View/Helper');
App::uses('View', 'View');
App::uses('MyCakeTestCase', 'Tools.TestSuite');


class WysiwygAppHelperTest extends MyCakeTestCase {

	public $WysiwygAppHelper;

	public function setUp() {
		parent::setUp();
		$this->WysiwygAppHelper = new WysiwygAppTestHelper(new View(null));
	}

	public function testInitializationOptions() {
		$test = '{"toolbar":"undo redo | bold italic underline | link searchreplace | code | insertButtonShop insertButtonDate insertButtonStart insertButtonEnd insertButtonWorth","plugins":"searchreplace link code paste","menubar":false,"paste_as_text":true,"statusbar":false,"toolbar_items_size":"small","setup":function(editor) {editor.addButton("insertButtonShop", { text: "Shopname", label: "Select :", icon: false, onclick: function() { editor.insertContent("{SHOP}"); }});editor.addButton("insertButtonDate", { text: "Aktuelles Datum", label: "Select :", icon: false, onclick: function() { editor.insertContent("{DATE}"); }});editor.addButton("insertButtonStart", { text: "Startdatum", label: "Select :", icon: false, onclick: function() { editor.insertContent("{START}"); }});editor.addButton("insertButtonEnd", { text: "Enddatum", label: "Select :", icon: false, onclick: function() { editor.insertContent("{END}"); }});editor.addButton("insertButtonWorth", { text: "Wert", label: "Select :", icon: false, onclick: function() { editor.insertContent("{VALUE}"); }});},"selector":"#OfferHowItWorks"}';
		$result = $this->WysiwygAppHelper->initializationOptions($test);
		debug($result, null, false);
	}

	//TODO

}

class WysiwygAppTestHelper extends WysiwygAppHelper {
	public function initializationOptions($options = array()) {
		return $this->_initializationOptions($options);
	}
}
