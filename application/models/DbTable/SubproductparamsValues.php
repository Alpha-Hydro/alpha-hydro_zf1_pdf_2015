<?php
/**
 * Table class for Subproduct params
 *
 * @author Dmitry Bykov
 * @package Catalog
 * @category Site
 * @subpackage Models
 */

class Model_DbTable_SubproductparamsValues extends Zend_Db_Table_Abstract {
    /**
     * Table name
     */
    protected $_name = 'subproduct_params_values';

    /**
     * Primary keys
     */
    protected $_primary = array('subproduct_id', 'param_id');

    /**
     * Relations
     */
	protected $_referenceMap    =   array(
                                            'ParamsRel' => array(
                                                            'columns'           =>  array('param_id'),
                                                            'refTableClass'     =>  'Model_DbTable_SubproductParams',
                                                            'refColumns'        =>  array('id'),
															'onDelete'			=>	self::CASCADE
                                                    		),
                                            'SubproductRel' => array(
                                                            'columns'           =>  array('subproduct_id'),
                                                            'refTableClass'     =>  'Model_DbTable_Subproducts',
                                                            'refColumns'        =>  array('id'),
															'onDelete'			=>	self::CASCADE
                                                    		)
										  );
}
?>