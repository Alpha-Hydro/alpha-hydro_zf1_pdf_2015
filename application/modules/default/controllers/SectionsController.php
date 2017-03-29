<?php
/**
 * Sections controller
 *
 * @author Bakin Vlad
 * @package Default
 * @category Site
 * @subpackage Controllers
 */

/**
 * Sections controller
 *
 * @package Default
 * @author Bakin Vlad
 * @subpackage Controllers
 */
class SectionsController extends Zend_Controller_Action {
	
	/**
	 * Production page
	 */
	public function productionAction() {
		Zend_Layout::getMvcInstance ()->setLayout ( "production" );
		$pagesModel = new Model_DbTable_PagesCategories ();
		$this->view->page = $pagesModel->fetchRow ( $pagesModel->select ()->where ( "parent_id IS NULL" )->where ( "name = ?", "Производство" ) );
	}
	public function productionEditAction() {
		Zend_Layout::getMvcInstance ()->setLayout ( "production" );
		
		if (! Zend_Auth::getInstance ()->hasIdentity ())
			throw new Zend_Exception ( "You can't edit this item" );
		
		$id = $this->getRequest ()->getParam ( 'id' );
		
		if (! $id) {
			$pagesModel = new Model_DbTable_PagesCategories ();
			$this->view->page = $pagesModel->fetchRow ( $pagesModel->select ()->where ( "parent_id IS NULL" )->where ( "name = ?", "Производство" ) );
			
			if ($this->getRequest ()->isPost ()) {
				
				$this->view->page->description = $this->getRequest ()->getParam ( "content" );
				$this->view->page->save ();
				
				$this->_redirect ( "/sections/production" );
				exit ();
			}
		} else {
			$pagesModel = new Model_DbTable_Pages ();
			$this->view->page = $pagesModel->fetchRow ( $pagesModel->select ()->where ( "id = ?", $id ) );
			
			if ($this->getRequest ()->isPost ()) {
				
				$this->view->page->content = $this->getRequest ()->getParam ( "content" );
				$this->view->page->save ();
				
				$this->_redirect ( "/sections/production-post/id/" . $this->view->page->id );
				exit ();
			}
		}
	}
	public function productionPostAction() {
		Zend_Layout::getMvcInstance ()->setLayout ( "production" );
		
		$pagesModel = new Model_DbTable_Pages ();
		
		$id = $this->getRequest ()->getParam ( 'id' );
		
		$this->view->page = $pagesModel->find ( $id )->current ();
		
		if (! $this->view->page || $this->view->page->findParentRow ( 'Model_DbTable_PagesCategories' )->name != 'Производство') {
			throw new Zend_Exception ( "Page not found", 404 );
		}
	}
	/**
	 * Contacts section.
	 * Display maps and some information
	 */
	/**
	 * Oil page
	 */
	public function oilAction() {
		Zend_Layout::getMvcInstance ()->setLayout ( "oil" );
		$pagesModel = new Model_DbTable_PagesCategories ();
		$this->view->page = $pagesModel->fetchRow ( $pagesModel->select ()->where ( "parent_id IS NULL" )->where ( "name = ?", "Масла" ) );
	}
	public function oilEditAction() {
		Zend_Layout::getMvcInstance ()->setLayout ( "oil" );
		
		if (! Zend_Auth::getInstance ()->hasIdentity ())
			throw new Zend_Exception ( "You can't edit this item" );
		
		$id = $this->getRequest ()->getParam ( 'id' );
		
		if (! $id) {
			$pagesModel = new Model_DbTable_PagesCategories ();
			$this->view->page = $pagesModel->fetchRow ( $pagesModel->select ()->where ( "parent_id IS NULL" )->where ( "name = ?", "Масла" ) );
			
			if ($this->getRequest ()->isPost ()) {
				
				$this->view->page->description = $this->getRequest ()->getParam ( "content" );
				$this->view->page->save ();
				
				$this->_redirect ( "/sections/oil" );
				exit ();
			}
		} else {
			$pagesModel = new Model_DbTable_Pages ();
			$this->view->page = $pagesModel->fetchRow ( $pagesModel->select ()->where ( "id = ?", $id ) );
			
			if ($this->getRequest ()->isPost ()) {
				
				$this->view->page->content = $this->getRequest ()->getParam ( "content" );
				$this->view->page->save ();
				
				$this->_redirect ( "/sections/oil-post/id/" . $this->view->page->id );
				exit ();
			}
		}
	}
	public function oilPostAction() {
		//Zend_Layout::getMvcInstance ()->setLayout ( "production" );
		
		$pagesModel = new Model_DbTable_Pages ();
		
		$id = $this->getRequest ()->getParam ( 'id' );
		
		$this->view->page = $pagesModel->find ( $id )->current ();
		
		if (! $this->view->page || $this->view->page->findParentRow ( 'Model_DbTable_PagesCategories' )->name != 'Производство') {
			throw new Zend_Exception ( "Page not found", 404 );
		}
	}
	/**
	 * Pipeline page
	 */
	public function pipelineAction() {
			
		$id = $this -> getRequest() -> getParam('id');
		$this -> view -> page_id = $id;
						
		Zend_Layout::getMvcInstance ()->setLayout ( "pipeline" );
				
		$pagesModel = new Model_DbTable_PagesCategories ();
		$this->view->page = $pagesModel->fetchRow ( $pagesModel->select ()->where ( "parent_id IS NULL" )->where ( "name = ?", "Трубопроводная арматура" ) );
				
		$where = $id ? "id = ".$id : "category_id = ".$this -> view -> page -> id; 		
				
		$section_pages_model = new Model_DbTable_Pages();		
		$this->view->section_pages = $section_pages_model -> fetchAll( $section_pages_model->select ()->where ( $where ));
		
		$pageTitle = $id ? $this -> view -> section_pages[0] -> name : 'Трубопроводная арматура, трубы и детали трубопроводов';
		
		Zend_Registry::set("pipeline", $pageTitle);		
		
	}
	public function pipelineEditAction() {
		Zend_Layout::getMvcInstance ()->setLayout ( "pipeline" );
		
		if (! Zend_Auth::getInstance ()->hasIdentity ())
			throw new Zend_Exception ( "You can't edit this item" );
		
		$id = $this->getRequest ()->getParam ( 'id' );
		
		if (! $id) {
			$pagesModel = new Model_DbTable_PagesCategories ();
			$this->view->page = $pagesModel->fetchRow ( $pagesModel->select ()->where ( "parent_id IS NULL" )->where ( "name = ?", "Трубопроводная арматура" ) );
			
			if ($this->getRequest ()->isPost ()) {
				
				$this->view->page->description = $this->getRequest ()->getParam ( "content" );
				$this->view->page->save ();
				
				$this->_redirect ( "/sections/pipeline" );
				exit ();
			}
		} else {
			$pagesModel = new Model_DbTable_Pages ();

			$this->view->page = $pagesModel->fetchRow ( $pagesModel->select ()->where ( "id = ?", $id ) );								
			
			if ($this->getRequest ()->isPost ()) {
				
				$this->view->page->content = $this->getRequest ()->getParam ( "content" );
				$this->view->page->save ();
				
				$this->_redirect ( "/sections/pipeline/");
				exit ();
			}
		}
	}
	public function pipelinePostAction() {
		Zend_Layout::getMvcInstance ()->setLayout ( "pipeline" );
		
		$pagesModel = new Model_DbTable_Pages ();
		
		$id = $this->getRequest ()->getParam ( 'id' );
		
		$this->view->page = $pagesModel->find ( $id )->current ();
		
		if (! $this->view->page || $this->view->page->findParentRow ( 'Model_DbTable_PagesCategories' )->name != 'Трубопроводная арматура') {
			throw new Zend_Exception ( "Page not found", 404 );
		}
	}	
	/**
	 * Contacts section.
	 * Display maps and some information
	 */	 
	public function contactsAction() {
		$this->view->company = new Zend_Config_Xml ( APPLICATION_PATH . "/config/information.xml" );
	}
	
	/**
	 * Media systems
	 * Display news, posts and stocks
	 */
	public function mediaAction() {
		$categoriesModel = new Model_DbTable_PagesCategories ();
		$pagesModel = new Model_DbTable_Pages ();
		
		$categories = $categoriesModel->getMedia ();
		
		if (isset ( $categories ["news"] )) {
			$this->view->news = $categories ["news"]->findDependentRowset ( "Model_DbTable_Pages", NULL, $pagesModel->select ()->order ( "timestamp DESC" ) );
		}
		if (isset ( $categories ["stocks"] )) {
			$this->view->stocks = $categories ["stocks"]->findDependentRowset ( "Model_DbTable_Pages", NULL, $pagesModel->select ()->order ( "timestamp DESC" )  );
		}
		
		if (isset ( $categories ["posts"] )) {
			$this->view->posts = $categories ["posts"]->findDependentRowset ( "Model_DbTable_Pages", NULL, $pagesModel->select ()->order ( "name" ) );
		}
	}
	
	/**
	 * Show selected post
	 */
	public function postAction() {
		$categoriesModel = new Model_DbTable_PagesCategories ();
		$pagesModel = new Model_DbTable_Pages ();
		
		$category = $categoriesModel->fetchRow ( $categoriesModel->select ()->where ( "name = ?", "Пресса" )->where ( "parent_id IS NULL" ) );
		$categories = $category->findDependentRowset ( "Model_DbTable_PagesCategories" );
		
		foreach ( $categories as $category ) {
			switch ($category->name) {
				case 'Статьи' :
					$posts = $category;
					break;
			}
		}
		
		$id = $this->getRequest ()->getParam ( 'id' );
		
		$this->view->post = $posts->findDependentRowset ( "Model_DbTable_Pages", NULL, $pagesModel->select ()->where ( 'id = ?', $id )->order ( "name" ) )->current ();
	}
	
	/**
	 * Show selected post
	 */
	public function newsAction() {
		$pagesModel = new Model_DbTable_Pages ();
		
		$id = $this->getRequest ()->getParam ( 'id' );
		
		$this->view->post = $pagesModel->find ( $id )->current ();
	}
	public function editAction() {
		if (! Zend_Auth::getInstance ()->hasIdentity ())
			throw new Zend_Exception ( "You cant edit this item" );
		
		$id = $this->getRequest ()->getParam ( "id" );
		$type = $this->getRequest ()->getParam ( "type" );
		$from = $this->getRequest ()->getParam ( "from" );
		$this->view->type = $type;		
		$this -> view -> from = $from;
		
		$categoriesModel = new Model_DbTable_PagesCategories ();
		$categories = $categoriesModel->getMedia ();
		
		$pagesModel = new Model_DbTable_Pages ();
		if ($id)
			$page = $pagesModel->find ( $id )->current ();
		elseif (($this->getRequest ()->isPost () && isset ( $categories [$type] )) || (! $this->getRequest ()->isPost ())) {
			$page = $pagesModel->createRow ();
			$page->category_id = $categories [$type]->id;
		} elseif($this -> getRequest() -> getParam("parent_id") ) {
			$page = $pagesModel->createRow ();
			$page->category_id = $this -> getRequest() -> getParam("parent_id");
		} else {
			throw new Zend_Exception ( "Selected type not available" );
		}
		
		if (! $page)
			throw new Zend_Exception ( "Page isn't found" );
		
		$form = Model_Static_Loader::loadForm ( "page" );
		if ($this->getRequest ()->isPost () && $form->isValid ( $_POST )) {
			$values = $form->getValues ();
			unset($values["thumb"]);
			foreach ( $values as $key => $val )
				$page->$key = $val;
			
			if ($type != "posts") {
				// upload files
				if ( isset ( $_FILES ["thumb"] ) &&  $_FILES ["thumb"]["size"] ) {
					$filename = date ( "Ymd_His" ) . $_FILES ["thumb"] ["name"];
					
					if(!$type) $type="common";
																									
					move_uploaded_file ( $_FILES ['thumb'] ['tmp_name'], $_SERVER ["DOCUMENT_ROOT"] . "/files/" . $type . "/" . $filename );									
					
					$page->thumb = "/files/" . $type . "/" . $filename;					
				}
			}
			
			$page->save ();
			
			switch($page->category_id){
				case 4:
					$url = "/sections/post?id=$page->id";
					break;
				case 3: case 2:
					$url = "/sections/news?id=$page->id";
					break;
				case 7:
					$url = "/sections/pipeline/";
					break;					
			}

			$this->_redirect ( $url );
			exit ();
		}
		
		$this->view->page = $page;
	}
	public function deleteAction() {
		if (! Zend_Auth::getInstance ()->hasIdentity ())
			throw new Zend_Exception ( "You can't edit this item" );
		
		$id = $this->getRequest ()->getParam ( "id" );
		
		$pagesModel = new Model_DbTable_Pages ();
		$page = $pagesModel->find ( $id )->current ();
		
		if (! $page)
			throw new Zend_Exception ( "Selected page not found" );
		
		if ($this->getRequest ()->getParam ( "accept" ) == "accepted") {
			$page->delete ();
			$this->_redirect ( "/sections/media" );
			exit ();
		} else {
			$this->view->page = $page;
		}
	}
}
?>