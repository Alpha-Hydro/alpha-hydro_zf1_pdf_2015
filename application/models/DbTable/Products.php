<?php
/**
 * Table class for Products
 *
 * @author Bakin Vlad
 * @package Catalog
 * @category Site
 * @subpackage Models
 */

/**
 * Table class for Products
 *
 * @package Catalog
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbTable_Products extends Zend_Db_Table_Abstract {
    /**
     * Table name
     */
    protected $_name = 'products';
    
    /**
     * Row class for results
     * 
     * @see Model_DbRow_Product more information
     */    
	protected $_rowClass = 'Model_DbRow_Product';
    
    /**
     * Dependent tables
     */
	protected $_dependentTables = array('Model_DbTable_CategoryXref', 'Model_DbTable_Subproducts', 'Model_DbTable_ProductParams', 'Model_DbTable_SubproductParams');
	
    /**
     * Relations
     */
//	protected $_referenceMap    =   array('SubproductsRel' => array(
//                                                            'columns'           =>  array('parent_id'),
//                                                            'refTableClass'     =>  'Model_DbTable_Subproducts',
//                                                            'refColumns'        =>  array('id'),
//															'onDelete'			=>	self::CASCADE
//                                                    		)
//										  );
}
?>