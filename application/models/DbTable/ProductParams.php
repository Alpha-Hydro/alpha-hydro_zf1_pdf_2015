<?php
/**
 * Table class for Product params
 *
 * @author Bakin Vlad
 * @package Catalog
 * @category Site
 * @subpackage Models
 */

/**
 * Table class for Product params
 *
 * @package Catalog
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbTable_ProductParams extends Zend_Db_Table_Abstract {
    /**
     * Table name
     */
    protected $_name = 'product_params';
	
    /**
     * Relations
     */
	protected $_referenceMap    =   array('ProductsRel' => array(
                                                            'columns'           =>  array('product_id'),
                                                            'refTableClass'     =>  'Model_DbTable_Products',
                                                            'refColumns'        =>  array('id'),
															'onDelete'			=>	self::CASCADE
                                                    		)
										  );
}
?>