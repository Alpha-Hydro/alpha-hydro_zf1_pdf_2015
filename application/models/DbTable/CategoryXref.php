<?php
/**
 * Table class for Categories xref to Products
 *
 * @author Bakin Vlad
 * @package Catalog
 * @category Site
 * @subpackage Models
 */

/**
 * Table class for Categories xref to Products
 *
 * @package Catalog
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbTable_CategoryXref extends Zend_Db_Table_Abstract {
    /**
     * Table name
     */
    protected $_name = 'categories_xref';
    
    /**
     * Primary keys
     */
    protected $_primary = array('product_id', 'category_id');
	
    /**
     * Relations
     */
    protected $_referenceMap    =   array(
                                            'CategoriesRel' => array(
                                                            'columns'           =>  array('category_id'),
                                                            'refTableClass'     =>  'Model_DbTable_Categories',
                                                            'refColumns'        =>  array('id'),
															'onDelete'			=>	self::CASCADE
                                                    ),
                                            'ProductsRel' => array(
                                                            'columns'           =>  array('product_id'),
                                                            'refTableClass'     =>  'Model_DbTable_Products',
                                                            'refColumns'        =>  array('id')
                                                    )
                                    );	
}
?>