<?php
/**
 * Row class for Model_DbTable_Categories
 *
 * @author Bakin Vlad
 * @package Default
 * @category Site
 * @subpackage Models
 */

/**
 * Row class for Model_DbTable_Categories
 *
 * @property string parent_id
 * @package Default
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbRow_Category extends Zend_Db_Table_Row {
	
	public function getParent($depth = null) {
		if(!$depth) {
			$parent = $this->findParentRow("Model_DbTable_Categories");
		} else {
			$arParents = array();
			$category = $arParents[] = $this;
						 									
			while($category->parent_id) {				
				$category = $arParents[] = $category->findParentRow("Model_DbTable_Categories");								 	
			}													
			$arParents = array_reverse($arParents);			
														
			if(isset($arParents[$depth])) {
				$parent = $arParents[$depth];
			} else {
				$parent = false;
			}			
		}
		return $parent;		
	}
	
    /**
     * Get top level category
     *
     * @return Model_DbRow_Category Top level category ( with parent_id == 0 )
     */
    public function findTopParent() {
        $category = $this;
		
        while ($category -> parent_id) {
            $category = $category -> findParentRow("Model_DbTable_Categories");
		}
				
        return $category;
    }

    /**
     * Get breadcrumbs for category
     *
     * @return string Bcn
     *
     */
    public function getBcnName() {
        $category = $this;

        $bcn = $category -> name;

        while ($category -> parent_id) {
            $category = $category -> findParentRow('Model_DbTable_Categories');
            $bcn = $category -> name . ' > ' . $bcn;
        }

        return $bcn;
    }

}
?>