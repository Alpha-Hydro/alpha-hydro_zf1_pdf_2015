<?php
/**
 * Book error controller
 *
 * @author Bakin Vlad
 * @package PDF Generation
 * @category Site
 * @subpackage Controllers
 */

/**
 * Book Error controller
 *
 * @package PDF Generation
 * @author Bakin Vlad
 * @subpackage Controllers
 */
class ErrorController extends Zend_Controller_Action {

    /**
     * Format and show error message
     *
     * @var Exception $error_handler
     */
    public function errorAction() {
        $errors = $this -> _getParam('error_handler');
        $this -> view -> content = $errors -> exception -> getMessage();
    }

}
?>