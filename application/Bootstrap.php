<?php
/**
 * Classic Zend Bootstrap
 *
 * @author Bakin Vlad
 * @version $Id: Bootstrap.php 2012$
 * @package Default
 * @category System
 */

/**
 * Standard Bootstrap class. Add models default directories and change theme files extension
 *
 * @package Default
 * @author Bakin Vlad
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    /**
     * Initialize auto loader and add resource loaders(for models)
     */
    public function _initAutoloader() {
        $autoloader = Zend_Loader_Autoloader::getInstance();

        $resourceLoader = new Zend_Loader_Autoloader_Resource( array('basePath' => APPLICATION_PATH . '/models', 'namespace' => 'Model'));
        $resourceLoader -> addResourceType('DbTable', 'DbTable/', 'DbTable');
        $resourceLoader -> addResourceType('DbRow', 'DbRow/', 'DbRow');
        $resourceLoader -> addResourceType('Static', 'Static/', 'Static');
    }

    protected function _initPlugins() {
        $this -> bootstrap('frontController');
        
        $pluginsLoader = new Zend_Loader_PluginLoader();
        $pluginsLoader->addPrefixPath("Plugin", APPLICATION_PATH.'/plugins');
        
        $pluginsLoader->load("Layout");
        if ( $pluginsLoader->isLoaded("Layout"))
            $front = Zend_Controller_Front::getInstance()->registerPlugin(new Plugin_Layout());

        $pluginsLoader->load("Acl");
        if ( $pluginsLoader->isLoaded("Acl"))
        	$front = Zend_Controller_Front::getInstance()->registerPlugin(new Plugin_Acl());       
    }

}
?>