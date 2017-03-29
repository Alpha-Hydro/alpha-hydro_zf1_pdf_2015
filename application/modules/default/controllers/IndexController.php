<?php
/**
 * Default controller
 *
 * @author Bakin Vlad
 * @package Default
 * @category Site
 * @subpackage Controllers
 */

/**
 * Default controller
 *
 * @package Default
 * @author Bakin Vlad
 * @subpackage Controllers
 */
class IndexController extends Zend_Controller_Action {
	
	/**
	 * Default action, will forward, or show start page
	 */
	public function indexAction() {
		Zend_Layout::getMvcInstance ()->setLayout ( "index" );
		
		$categoriesModel = new Model_DbTable_PagesCategories ();
		$pagesModel = new Model_DbTable_Pages ();
		
		$category = $categoriesModel->fetchRow ( $categoriesModel->select ()->where ( "name = ?", "Пресса" )->where ( "parent_id IS NULL" ) );
		
		$categories = $category->findDependentRowset ( "Model_DbTable_PagesCategories" );
		
		foreach ( $categories as $category ) {
			switch ($category->name) {
				case 'Новости' :
					$news = $category;
					break;
				case 'Акции' :
					$stocks = $category;
					break;
				case 'Статьи' :
					$posts = $category;
					break;
			}
		}
		
		if ( isset($posts) ){
			$this -> view -> post = $pagesModel->fetchRow($pagesModel->random()->where("category_id = ?", $posts->id));
		}
		
		if ( isset($news) ){
			$this -> view -> news = $pagesModel->fetchRow($pagesModel->select()->where("category_id = ?", $news->id)->order("timestamp DESC"));
		}
		
		if ( isset($stocks) ){
			$this -> view -> stocks = $pagesModel->fetchRow($pagesModel->select()->where("category_id = ?", $stocks->id)->order("timestamp DESC"));
		}		
		
		$forumModel = new Model_DbTable_Forum();
		
		$this->view->forum = new StdClass;
		$this->view->forum->answer = $forumModel->fetchRow( $forumModel->select()->order("timestamp DESC") ->where ( "parent_id IS NOT NULL " ) );
		$this->view->forum->question = $this->view->forum->answer->findParentRow("Model_DbTable_Forum");
	}
	
	/**
	 * Login action
	 */
	public function loginAction() {
		$zend_auth = Zend_Auth::getInstance ();
		$form = Model_Static_Loader::loadForm ( "login" );
		
		if ($this->getRequest ()->isPost () && $form->isValid ( $_POST ) && ! $zend_auth->hasIdentity ()) {
			$adapter = new Model_Static_Auth ( $form->getValue ( "username" ), $form->getValue ( "password" ) );
			
			$result = $zend_auth->authenticate ( $adapter );
			
			if ($result->isValid ()) {
				$zend_auth->getStorage ()->write ( $result->getIdentity () );
				$this->_forward ( "index" );
			}
		} elseif ($zend_auth->hasIdentity ()) {
			$this->_forward ( "index" );
		}
		
		$this->view->form = $form;
	}
	
	/**
	 * Logout action
	 */
	public function logoutAction() {
		Zend_Auth::getInstance ()->clearIdentity ();
	}
	
	public function aboutAction(){
	}
}
?>