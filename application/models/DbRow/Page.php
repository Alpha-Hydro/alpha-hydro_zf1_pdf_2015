<?php
/**
 * Row class for Model_DbRow_Pages
 *
 * @author Bakin Vlad
 * @package Default
 * @category Site
 * @subpackage Models
 */

/**
 * Row class for Model_DbRow_Pages
 *
 * @package Default
 * @author Bakin Vlad
 * @subpackage Models
 */
class Model_DbRow_Page extends Zend_Db_Table_Row {

    /**
     * Adding short content if not exist
     * 
     */
    public function init() {
        if (!$this -> s_content){
        	if ( mb_strlen($this->content, 'utf8') > 350 ){
        		$this-> s_content = trim(mb_substr(strip_tags($this->content), 0, 350, 'utf8'));
        		$this -> s_content = mb_substr($this->s_content, 0, mb_strrpos($this->s_content, ' ', 'utf8'), 'utf8')."...";
        	}
        }
    }
    
}
?>