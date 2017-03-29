<?php
/**
 * Table class for Categories
 *
 * @author Bakin Vlad
 * @package Catalog
 * @category Site
 * @subpackage Models
 */

/**
 * Table class for Categories
 *
 * @package Catalog
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbTable_Categories extends Zend_Db_Table_Abstract {
    
    /**
     * Table name
     */
    protected $_name = 'categories';
    
    /**
     * Row class for results
     * 
     * @see Model_DbRow_Product more information
     */    
    protected $_rowClass = 'Model_DbRow_Category';
    /**
     * Dependent tables
     */
    protected $_dependentTables = array('Model_DbTable_CategoryXref', 'Model_DbTable_Categories');
    
    /**
     * Relation tables
     */
    protected $_referenceMap = array('CategoriesRel' => array('columns' => array('parent_id'), 'refTableClass' => 'Model_DbTable_Categories', 'refColumns' => array('id'), 'onDelete' => self::CASCADE));

}
?>