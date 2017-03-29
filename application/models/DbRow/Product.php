<?php
/**
 * Row class for Model_DbTable_Products
 *
 * @author Bakin Vlad
 * @package Default
 * @category Site
 * @subpackage Models
 */

/**
 * Row class for Model_DbTable_Products
 *
 * @package Default
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbRow_Product extends Zend_Db_Table_Row {

    /**
     * Unserialize images array
     * Normalize images and a_images filenames
     * 
     */
    public function init() {
        if ($this -> a_images){
            $this -> a_images = unserialize($this -> a_images);
            $a_images = array();
            foreach ($this-> a_images as $i=>$image)
                $a_images[$i] = str_replace(array("+", " "), "_", $image);
            
            $this -> a_images = $a_images;
        }
        
        $this->image = str_replace(array("+", " "), "_", $this->image);
    }
    
    public function save(){
    	$this -> a_images = serialize($this->a_images);
    	return parent::save();
    }

    // параметры товара по порядку
    public function getParams(){
        $paramsTable = new Model_DbTable_ProductParams();
        $select = $paramsTable->select()->order('order ASC');
    	return $this -> findDependentRowset("Model_DbTable_ProductParams",null,$select);
    }

    // параметры подтоваров по порядку
    public function getSubParams(){
        $paramsTable = new Model_DbTable_SubproductParams();
        $select = $paramsTable->select()->order('order ASC');
    	return $this -> findDependentRowset("Model_DbTable_SubproductParams","ProductRel",$select);
    }

    public function getPrimaryCategory(){
    	if ( $this -> parent_id )
    		$item = $this -> findParentRow("Model_DbTable_Products");
    	else
    		$item = $this;
    
    	return $item -> findManyToManyRowset("Model_DbTable_Categories", "Model_DbTable_CategoryXref") -> current();
    }
    
    public function getPrimaryImage(){
    	if ( $this -> parent_id )
    		$item = $this -> findParentRow("Model_DbTable_Products");
    	else
    		$item = $this;
    	
    	return $item -> image;
    }
}
?>