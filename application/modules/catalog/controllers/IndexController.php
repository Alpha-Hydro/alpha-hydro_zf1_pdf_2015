<?php
/**
 * Default catalog controller
 *
 * @author Bakin Vlad
 * @package Catalog
 * @category Site
 * @subpackage Controllers
 */

/**
 * Default catalog controller
 *
 * @package Catalog
 * @author Bakin Vlad
 * @subpackage Controllers
 */
class Catalog_IndexController extends Zend_Controller_Action {

    /**
     * Default action, will forward, or show start page
     *
     */
    public function indexAction() {
        $this -> forward("index", "categories");
    }

    /**
     * @ignore
     */
    public function serializeSKU($sku) {
        $sku = str_replace(array('.', ',', ' ', '-', '_', '/', '\\', '*', '+', '&', '^', '%', '#', '@', '!', '(', ')', '~', '<', '>', ':', ';', '"', "'", "|"), '', $sku);
        return strtolower($sku);
    }
	
    public function editCatalogAction(){
    	$catalogForm = Model_Static_Loader::loadForm("catalog");
    	$catalogForm -> preview -> setDestination(APPLICATION_ROOT."/public/files/catalogs");
    	$catalogForm -> file -> setDestination(APPLICATION_ROOT."/public/files/catalogs");
    	
    	$catalogs = new Zend_Config_Xml(APPLICATION_PATH."/config/catalogs.xml");
    	$id = $this -> getRequest() -> getParam('guid');
    	
    	if ( $id && !isset($catalogs->$id))
    		throw new Zend_Exception("Not found", 404);
    	elseif ($id) {
    		$catalogForm -> setDefaults( $catalogs -> $id -> toArray() );
    	}
    	
    	if ( $this -> getRequest() -> isPost() && $catalogForm -> isValid($_POST) ){
    		$data = $catalogForm -> getValues();
    		$data["preview"] = "/files/catalogs/".$data["preview"];
    		$data["file"] = "/files/catalogs/".$data["file"];
    		
    		
			$catalogs = $catalogs -> toArray();
    		if ( $id ){
				$catalogs[$id]= $data;
    		} else {
    			$catalogs['cat'.date("ymdhis")] = $data;
    		}
    		
    		$xml = new Zend_Config_Writer_Xml();
    		$xml -> setConfig( new Zend_Config($catalogs) );
    		$xml -> setFilename(APPLICATION_PATH."/config/catalogs.xml");
    		$xml -> write();
    	}
    	
    	$this -> view -> form = $catalogForm;
    }
}
?>