<?php
/**
 * Sidebar catalog controller
 *
 * @author Bakin Vlad
 * @package Catalog
 * @category Site
 * @subpackage Controllers
 */

/**
 * Sidebar catalog controller
 *
 * @package Catalog
 * @author Bakin Vlad
 * @subpackage Controllers
 */
class Catalog_SidebarController extends Zend_Controller_Action {
	
	/**
	 * Show sidebar
	 *
	 * @var int $pcategory Selected category
	 *     
	 * @throws Exception 404, Category not found
	 *        
	 */
	public function sidebarAction() {
		$categoriesModel = new Model_DbTable_Categories ();
		
		$select = $categoriesModel -> select();
		$select -> order(new Zend_Db_Expr('`order`<=-100')) -> order("order");
		
		if ( !Zend_Auth::getInstance()->hasIdentity() )
			$select -> where("`order` != -100 OR `order` IS NULL");
		
		
		if (NULL != ($category_id = $this->getRequest ()->getParam ( "category" ))) {
			if (! ($category = $categoriesModel->find ( $category_id )->current ()))
				throw new Exception ( "Category not found", 404 );
			
			$select -> where("parent_id = ?", $category->id);
			$categories = $categoriesModel -> fetchAll ( $select );

			if ( count($categories) == 0) {
				$category = $categoriesModel->find ( $category->parent_id )->current ();
				$categories = $category->findDependentRowset( "Model_DbTable_Categories" );

			}
		} else {
			
			$category = NULL;
			$categories = $categoriesModel->fetchAll ( $select->where ( "parent_id IS NULL" ) );
		}
		
		$this->view->categories = $categories;
		$this->view->category = $category;
		
		$this->view->current = $category_id;
		
		$this -> view -> catalogs = new Zend_Config_Xml(APPLICATION_PATH."/config/catalogs.xml");
	}
	
	
	public function navigationAction() {
		$bcn = new Zend_Navigation ();
		$categoriesModel = new Model_DbTable_Categories ();
		
		$category = $categoriesModel->find ( $this->getRequest ()->getParam ( "category" ) )->current ();
		
		if (! $category)
			return;
		
		$i = 0;
		$bcn->addPage ( array (
				"action" => "index",
				"controller" => "categories",
				"module" => "catalog",
				'order'	 => $i,
				"params" => array('category'=>$category->id),
				'label' => $category->name
		) );
		while ( $category->parent_id ) {
			$category = $category->findParentRow ( 'Model_DbTable_Categories' );
			
			$bcn->addPage ( array (
					"action" => "index",
					"controller" => "categories",
					"module" => "catalog",
					'order'	 => $i,
					"params" => array('category'=>$category->id),
					'label' => $category->name 
			) );
			
			$i--;
		}
		
		$bcn->addPage ( array (
				"action" => "index",
				"controller" => "index",
				"module" => "catalog",
				'label' => 'Каталог продукции',
				'order' => $i
		) );
		
		$bcn->addPage ( array (
				"action" => "index",
				"controller" => "index",
				"module" => "default",
				'label' => 'Главная',
				'order' => $i-1
		) );
		
		$this->view->navigation = $bcn;
	}
}
?>