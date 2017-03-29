<?php

/**
 * BasketController
 * 
 * @author
 * @version 
 */
class Catalog_BasketController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$form = Model_Static_Loader::loadForm ( "basket" );
		
		$session = new Zend_Session_Namespace ( "basket" );
		
		$model = new Model_DbTable_Products ();
		$items = array ();
		foreach ( $session->items as $item_id => $count )
			$items [$item_id] = ( object ) array (
					"item" => $model->find ( $item_id )->current (),
					"count" => $count 
			);
		
		if ($this->getRequest ()->isPost () && $form->isValid ( $_POST ) && $items ) {
			$data = $form->getValues ();
			
			$orderItems = $this->getRequest ()->getParam ( "items" );
			
			/* ******************************************
			 * EMAIL MESSAGE
			 * ******************************************/			
			ob_start();
			?>Новый заказ на сайте альфа гидро:
			<ul><?php
			foreach ( $orderItems as $item => $count ) : $item = $items[$item]->item;
			?><li><?=$item->sku?>, кол-во: <?=$count?>шт.</li>
			<?php 
			endforeach;
			?></ul>
			
			Имя: <?=$data['name']?><br/>
			Email: <?=$data['email']?><br/>
			Телефон: <?=$data['phone']?><br/>
			Примечание:<br/><pre><?=strip_tags($data['content'])?></pre><br/>
			
			<?php
			$messageHtml = ob_get_clean();
			/* ************************************************
			 * MESSAGE END
			 * ************************************************/
			

			$message = new Zend_Mail("UTF-8");
			$message -> setFrom("info@alpha-hydro.com", "Альфа-Гидро");
			$message -> setSubject("Новый заказ на сайта Альфа-Гидро");
			$message -> setBodyHtml($messageHtml, "utf8", "UTF-8");
			
			$users = new Zend_Config_Xml ( APPLICATION_PATH . "/config/admins.xml" );
			$users = $users->toArray ();
			$message -> addTo ("info@alpha-hydro.com", "Альфа-Гидро");
			$message -> addTo ("daemon007@mail.ru", "Быков Дмитрий Владимирович");
			foreach ( $users as $user ) if ( $user["role"] == "administrator" )
				$message->addTo ( $user ['email'], $user ['name'] );
			
			$message -> send();
			
			$session->items = array ();
			$this->_helper->viewRenderer ( "index-success" );
		}
		
		$this->view->items = $items;
		
		$this->_helper->layout ()->setLayout ( "basket" );
	}
	
	/**
	 * Lookup count of basket items
	 * 
	 * @throws Zend_Exception
	 */
	public function countAction() {
		if (! $this->getRequest ()->isPost () && $this->getRequest ()->isXmlHttpRequest ())
			throw new Zend_Exception ( "Invalid HTTP Request" );
		
		$this->_helper->layout ()->disableLayout ();
		
		$session = new Zend_Session_Namespace ( "basket" );
		$this->view->count = array_sum ( isset ( $session->items ) ? $session->items : array () );
	}
	public function putAction() {
		if (! $this->getRequest ()->isPost () && $this->getRequest ()->isXmlHttpRequest ())
			throw new Zend_Exception ( "Invalid HTTP Request" );
		$this->_helper->layout ()->disableLayout ();
		
		$session = new Zend_Session_Namespace ( "basket" );
		
		if (! $session->items)
			$session->items = array ();
		
		$id = $this->getRequest ()->getParam ( "id" );
		$count = $this->getRequest ()->getParam ( "count" );
		if ($count < 0)
			exit ( "wrong" );
		
		$session->items [$id] += $count;
		
		exit ( 'ok' );
	}
	public function clearAction() {
		$session = new Zend_Session_Namespace ( "basket" );
		$session->items = array ();
		exit ( "ok" );
	}
	public function removeAction() {
		$id = $this->getRequest ()->getParam ( "id" );
	}
}
