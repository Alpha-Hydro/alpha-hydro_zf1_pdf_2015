<?php
/**
 * Catalog error controller
 *
 * @author Bakin Vlad
 * @package Catalog
 * @category Site
 * @subpackage Controllers
 */

 
 /**
  * Catalog Error controller
  *
  * @package Catalog
  * @author Bakin Vlad
  * @subpackage Controllers
  */ 
class Catalog_ErrorController extends Zend_Controller_Action {
    
    /**
     * Format and show error message
     * 
     * @var Exception $error_handler throwed exception
     */
    public function errorAction() {
    	$errors = $this -> _getParam('error_handler');
    	if ($errors->exception->getCode () == 404 || in_array ( $errors->type, array (
    			Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE,
    			Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER,
    			Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION
    	) )) {
    		$this->getResponse ()->setHttpResponseCode ( 404 );
    		$this->_helper->viewRenderer ( '404' );
    	}    	
    	
        
        $this -> view -> content = $errors -> exception -> getMessage();
    }

}
?>