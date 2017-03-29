<?php
/**
 * Table class for Pages
 *
 * @author Bakin Vlad
 * @package Default
 * @category Site
 * @subpackage Models
 */

/**
 * Table class for Pages
 *
 * @package Default
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbTable_Pages extends Zend_Db_Table_Abstract {
	/**
	 * Table name
	 */
	protected $_name = 'pages';
	
	/**
	 * Row class for results
	 *
	 * @see Model_DbRow_Page more information
	 */
	protected $_rowClass = 'Model_DbRow_Page';
	public function random() {
		return $this->select ()->order ( "RAND()" )->limit ( 1 );
	}
	
	/**
	 * Relations
	 */
	protected $_referenceMap = array (
			'CategoriesRel' => array (
					'columns' => array (
							'category_id' 
					),
					'refTableClass' => 'Model_DbTable_PagesCategories',
					'refColumns' => array (
							'id' 
					),
					'onDelete' => self::CASCADE 
			) 
	);
}
?>