<?php
/**
 * Pages controller
 *
 * @author Bakin Vlad
 * @package Default
 * @category Site
 * @subpackage Controllers
 */

/**
 * Pages controller
 *
 * @package Default
 * @author Bakin Vlad
 * @subpackage Controllers
 */
class PagesController extends Zend_Controller_Action {

    /**
     * Default action, will forward, or show start page
     *
     * @var int $category Selected category
     */
    public function indexAction() {

        $category = $this -> getRequest() -> getParam("category");
        $pagesModel = new Model_DbTable_Pages();

        $pages = $pagesModel -> fetchAll($pagesModel -> select() -> where("category_id = ?", $category) -> order("name ASC"));
        
        //$pages = $pages as
    }

}
?>