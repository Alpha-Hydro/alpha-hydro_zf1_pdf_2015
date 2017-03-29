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
class Zend_View_Helper_isAllowed {
	
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
	public function isAllowed( $resource, $action ) {
		if ( !Zend_Auth::getInstance()->hasIdentity() ) return false;
		
		$user = Zend_Auth::getInstance()->getIdentity();
		$acl = Zend_Registry::get('acl');
		
		return $acl -> isAllowed( $user -> role, $resource, $action );
		
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
