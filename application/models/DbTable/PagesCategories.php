<?php
/**
 * Table class for Categories
 *
 * @author Bakin Vlad
 * @package Default
 * @category Site
 * @subpackage Models
 */

/**
 * Table class for Categories
 *
 * @package Default
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbTable_PagesCategories extends Zend_Db_Table_Abstract {
    /**
     * Table name
     */
    protected $_name = 'pages_categories';

    /**
     * Dependent tables
     */
    protected $_dependentTables = array('Model_DbTable_Pages', "Model_DbTable_PagesCategories");
    
    public function getMedia(){
    	$category = $this->fetchRow ( $this->select ()->where ( "name = ?", "Пресса" )->where ( "parent_id IS NULL" ) );
    	$categories = $category->findDependentRowset ( "Model_DbTable_PagesCategories" );
    		
    	$cats = array();
    		
    	foreach ( $categories as $category ) {
    		switch ($category->name) {
    				
    			case 'Новости' :
    				$cats["news"] = $category;
    				break;
    			case 'Акции' :
    				$cats["stocks"] = $category;
    				break;
    			case 'Статьи' :
    				$cats["posts"] = $category;
    				break;
    		}
    	}
    	
    	return $cats;
    }
    
    /**
     * Relations
     */    
    protected $_referenceMap    =   array('ParentsRel' => array(
                                                            'columns'           =>  array('parent_id'),
                                                            'refTableClass'     =>  'Model_DbTable_PagesCategories',
                                                            'refColumns'        =>  array('id' ),
                                                            'onDelete'          =>  self::CASCADE
                                                            )
                                          );    
}
?>