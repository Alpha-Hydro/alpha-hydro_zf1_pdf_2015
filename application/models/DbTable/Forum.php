<?php
/**
 * Table class for forum
 *
 * @author Bakin Vlad
 * @package Default
 * @category Site
 * @subpackage Models
 */

/**
 * Table class for forum
 *
 * @package Default
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbTable_Forum extends Zend_Db_Table_Abstract {
	/**
	 * Table name
	 */
	protected $_name = 'forum';
	
	/**
	 * Dependent tables
	 */
	protected $_dependentTables = array (
			'Model_DbTable_Forum'
	);
	
	/**
	 * Relations
	 */
	protected $_referenceMap = array (
			'ForumRel' => array (
					'columns' => array (
							'parent_id' 
					),
					'refTableClass' => 'Model_DbTable_Forum',
					'refColumns' => array (
							'id' 
					),
					'onDelete' => self::CASCADE 
			) 
	);
}
?>