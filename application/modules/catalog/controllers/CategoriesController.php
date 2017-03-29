<?php
/**
 * Categories controller
 *
 * @author Bakin Vlad
 * @package Catalog
 * @category Site
 * @subpackage Controllers
 */

/**
 * Categories controller
 *
 * @package Catalog
 * @author Bakin Vlad
 * @subpackage Controllers
 */
class Catalog_CategoriesController extends Zend_Controller_Action {

    /**
     * Show categories list ( based on parent_id ). if no sub category found - will forward to products list
     *
     * @var integer $pcategory Parent category
     */
    public function indexAction() {
        $categoriesModel = new Model_DbTable_Categories();

        $select = $categoriesModel -> select();

        if (NULL != ($parent_id = $this -> getRequest() -> getParam("category"))) {
            $category = $categoriesModel -> find($parent_id) -> current();
            $this -> view -> category = $category;
            $select -> where("`parent_id` = ?", $parent_id);
            $this->view->headTitle()->prepend ("Альфа-Гидро: Каталог продукции - ".$category['name']);
        } else{
            $select -> where("`parent_id` IS NULL");
            $this->view->headTitle()->prepend ("Альфа-Гидро: Продукция");
        }
      
        $select -> order(new Zend_Db_Expr('`order`<=-100'))-> order("order");
        if ( !Zend_Auth::getInstance()->hasIdentity() ){
        	$select -> where("`order` != -100 OR `order` IS NULL");
        }

        $this -> view -> rowset = $categoriesModel -> fetchAll($select);
        
        if ( $this -> getRequest() -> isXmlHttpRequest() ){
        	$this -> view -> curRowId = $this -> getRequest() -> getParam("currentCategory");
        	$this->_helper->viewRenderer->setRender('index-ajax');
        	Zend_Layout :: getMvcInstance() -> disableLayout();
        	return;
        }
        
        if (count($this -> view -> rowset) == 0) {
            $this -> forward("index", "products");
            return;
        }
		
    }

    /**
     * Edit selected category
     *
     * @var int $pcategory Selected category
     */
    public function editAction() {
    	if ( !Zend_Auth::getInstance() -> hasIdentity())
    		 throw new Zend_Exception("Page not found", 404);
    		 

        $id = $this -> getRequest() -> getParam("id");
        
        $categoriesModel = new Model_DbTable_Categories();
        $category = $categoriesModel -> find($id) -> current();

        if (!$category && $id)
            throw new Zend_Exception("Selected category not found", 404);
        elseif ( !$id ){
        	$category = $categoriesModel->createRow();
        }
        
		$form = Model_Static_Loader::loadForm("category");		
				
        if ($this -> getRequest() -> isPost() && $form -> isValid($_POST)) {
			$file = $form -> image -> getFileInfo();		
			$ext = pathinfo($file['image']['name'], PATHINFO_EXTENSION);
			$name = pathinfo($file['image']['name'], PATHINFO_FILENAME);				
			$newName = time().'_'.$name.'.'.$ext; 				
	
	    	$form -> image -> addFilter('Rename', APPLICATION_ROOT."/public/files/images/category/".$newName);		
			$form -> image -> receive();        	
			
            $data = $form -> getValues();
            
            $category -> parent_id = $data["parent_id"] ? $data["parent_id"] : NULL;
            $category -> image = $data["image"] ? $data["image"] : $category -> image;			
            unset($data['image']);
            unset($data['parent_id']);
            
            foreach ( $data as $key => $val )
            	$category -> $key = $val;
            
        	$category -> save();
        }

        $form -> setDefaults($category -> toArray());
        $this -> view -> form = $form;
        $this -> view -> row = $category;
    }
    
    /**
     * Delete category with confirmation
     */
    public function deleteAction() {
        if (Zend_Auth::getInstance() -> hasIdentity()) {

            $id = $this -> getRequest() -> getParam("id");

            $categoriesModel = new Model_DbTable_Categories();
            $category = $categoriesModel -> find($id) -> current();

            if (!$category)
                throw new Zend_Exception("No category found", 404);

            if ($this -> getRequest() -> getParam("confirmed") == 'true') {
                $category -> delete();
                $this -> _forward("index");
            } else {
                $this -> view -> category = $category;
            }

        } else
            throw new Zend_Exception("Page not found", 404);
    }

}
?>