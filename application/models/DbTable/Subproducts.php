<?php

/**
 * Table class for SubProducts
 *
 * @package Catalog
 * @category Site
 * @author Dmitry Bykov
 * @subpackage Models
 */
class Model_DbTable_Subproducts extends Zend_Db_Table_Abstract {
    /**
     * Table name
     */
    protected $_name = 'subproducts';
    
    /**
     * Row class for results
     * 
     * @see Model_DbRow_Product more information
     */    
	protected $_rowClass = 'Model_DbRow_Subproduct';
    
    /**
     * Dependent tables
     */
	protected $_dependentTables = array('Model_DbTable_SubproductParams');
	
    /**
     * Relations
     */
    protected $_referenceMap    =   array('SubproductsRel' => array(
            'columns'           =>  array('parent_id'),
            'refTableClass'     =>  'Model_DbTable_Products',
            'refColumns'        =>  array('id'),
            'onDelete'			=>	self::CASCADE
        )
    );

}
?>