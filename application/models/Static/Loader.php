<?php

/**
* 
* Loading resource for current module
* 
* @author Bakin Vladislav
*
*/
class Model_Static_Loader {
	/**
	 * Create Zend_Form from XML file which stored in {module_dir}/forms
	 *
	 * @param string $formName
	 *        	Form name
	 * @return Zend_Form Created form
	 */
	public static function loadForm($formName) {
		$module_dir = Zend_Controller_Front::getInstance ()->getModuleDirectory ();
		return new Zend_Form ( new Zend_Config_Xml ( $module_dir . "/forms/$formName.xml" ) );
	}
}

?>