<?php
/**
 * Table class for Category Index
 *
 * @author Dmitri Emelianov
 * @package PDF Generation
 * @category Site
 * @subpackage Models
 */
class Model_DbTable_CategoryIndex extends Zend_Db_Table_Abstract {
    /**
     * Table name
     */
    protected $_name = 'categoryIndex';
    
    
    /**
     * Clear product index
     */
    public function truncate(){
        $this->_db->query("TRUNCATE TABLE `categoryIndex`");
    }    
}
?>