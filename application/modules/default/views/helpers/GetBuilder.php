<?php
/**
 *
 * @author Vladislav
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * StrCrop helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GetBuilder {
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	/**
	 * 
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @param  string                             $action
     * @uses   Zend_Acl::isAllowed()
	 * @return boolean
	 */
	public function getBuilder( $params ) {
		$request = $_GET;
		
		foreach ( $params as $key => $val ){
			$request[$key] = $val;
		}
		
		return http_build_query($request);
	}
	
	/**
	 * Sets the view field
	 * 
	 * @param $view Zend_View_Interface        	
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
