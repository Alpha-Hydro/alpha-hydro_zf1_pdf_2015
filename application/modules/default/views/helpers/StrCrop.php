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
class Zend_View_Helper_StrCrop {
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	/**
	 */
	public function strCrop( $string , $len = 130) {
		// TODO Auto-generated Zend_View_Helper_StrCrop::strCrop() helper
		
		$string = strip_tags($string);
		
		if ( mb_strlen($string, 'utf-8') > $len ){
			$string = mb_substr($string, 0, $len, 'utf-8');
			$string = mb_substr($string, 0, mb_strrpos($string, ' ', 'UTF-8'), 'utf-8');
			$string .= '...';
			return $string;
		} else
			return $string;
		
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
