<?php
/**
 * Table class for Product Index
 *
 * @author Bakin Vlad
 * @package PDF Generation
 * @category Site
 * @subpackage Models
 */

/**
 * Table class for Product Index
 *
 * @package PDF Generation
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbTable_ProductIndex extends Zend_Db_Table_Abstract {
    /**
     * Table name
     */
    protected $_name = 'productIndex';
    
    
    /**
     * Clear product index
     */
    public function truncate(){
        $this->_db->query("TRUNCATE TABLE `productIndex`");
    }    
}
?>