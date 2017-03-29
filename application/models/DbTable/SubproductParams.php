<?php
/**
 * Table class for Subproduct params
 *
 * @author Dmitry Bykov
 * @package Catalog
 * @category Site
 * @subpackage Models
 */

class Model_DbTable_SubproductParams extends Zend_Db_Table_Abstract {
    /**
     * Table name
     */
    protected $_name = 'subproduct_params';
	
    /**
     * Relations
     */
	protected $_referenceMap    =   array(
                                        	'ProductRel' => array(
                                                            'columns'           =>  array('product_id'),
                                                            'refTableClass'     =>  'Model_DbTable_Products',
                                                            'refColumns'        =>  array('id'),
															'onDelete'			=>	self::CASCADE
                                                    		)
										  );
}
?>