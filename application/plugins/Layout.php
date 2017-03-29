<?php
class Plugin_Layout extends Zend_Controller_Plugin_Abstract {
    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        $frontController = Zend_Controller_Front::getInstance();
        $config = $frontController -> getParam('bootstrap') -> getApplication() -> getOptions();

        if (isset($config[$request -> getModuleName()]['resources']['layout']['layout'])) {
            $layout = Zend_Layout::getMvcInstance();
            $layout -> setLayout($config[$request -> getModuleName()]['resources']['layout']['layout']);
        }
    }

}
