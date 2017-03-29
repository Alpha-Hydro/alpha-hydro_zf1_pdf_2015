<?php

/**
 * ForumController
 * 
 * @author Bakin Vlad
 * @version 
 */
class ForumController extends Zend_Controller_Action {
	
	const PAGE_NUM = 10;
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		$forumModel = new Model_DbTable_Forum ();
		
		$categories = array (
				"faq" 		 => "Вопросы и запросы",
				"reviews" 	 => "Отзывы и предложения",
				"complaints" => "Книга жалоб",
				"all"		 => "all"
		);
		
		$pages = array (
				"Вопросы и запросы" => 1,
				"Отзывы и предложения" => 1,
				"Книга жалоб" => 1,
				"all"		  => 1
		);
		
		
		foreach ( $categories as $key => $tab )
			if (NULL != ($page = $this->getRequest ()->getParam ( $key ))) 
				$pages[$tab] = intval($page, 10);
			
				
		$questions = $forumModel->fetchAll ( $forumModel->select ()->where ( "parent_id IS NULL" )->order ( "timestamp DESC" ) );
		$db = $forumModel -> getAdapter();
		
		
		$this -> view -> lasts = array (
				"Вопросы и запросы" => $db-> fetchOne("SELECT COUNT(*) FROM forum WHERE category='Вопросы и запросы' AND parent_id IS NULL") / $this::PAGE_NUM,
				"Отзывы и предложения" => $db-> fetchOne("SELECT COUNT(*) FROM forum WHERE category='Отзывы и предложения' AND parent_id IS NULL") / $this::PAGE_NUM,
				"Книга жалоб" => $db-> fetchOne("SELECT COUNT(*) FROM forum WHERE category='Книга жалоб' AND parent_id IS NULL") / $this::PAGE_NUM,
				"all"		  => count($questions) / $this::PAGE_NUM, 
		);
		
		$topics = array ();
		$admin_topics = array ();
				
		$i = array("all" => 0);
		foreach ( $questions as $question ) {
			$i[$question->category] = 0;
		}
		foreach ( $questions as $question ) {
			$answer = $question->findDependentRowset ( 'Model_DbTable_Forum' );
			
			if (count ( $answer ) > 0) {
				
				if ( $i["all"] >= ($pages["all"]-1) * $this::PAGE_NUM && $i["all"] <= $pages["all"] * $this::PAGE_NUM -1 ) {
					$topics ['all'] [] = array (
							'question' => $question,
							'answers' => $answer 
					);								
				}

				if ( $i[$question->category] >= ($pages[$question->category]-1) * $this::PAGE_NUM && $i[$question->category] <= $pages[$question->category] * $this::PAGE_NUM -1 )
					$topics [$question->category] [] = array (
							'question' => $question,
							'answers' => $answer 
					);
				
			} elseif (Zend_Auth::getInstance ()->hasIdentity ()) {
				if ( $i["all"] >= ($pages["all"]-1) * $this::PAGE_NUM && $i["all"] <= $pages["all"] * $this::PAGE_NUM -1 )
					$admin_topics ['all'] [] = $question;
				
				if ( $i[$question->category] >= ($pages[$question->category]-1) * $this::PAGE_NUM && $i[$question->category] <= $pages[$question->category] * $this::PAGE_NUM -1 )
					$admin_topics [$question->category] [] = $question;
			}
			$i[$question->category] += 1;
			$i["all"] += 1;
		}
		
		$this -> view -> admin_topics = $admin_topics;
		$this -> view -> topics = $topics;
		$this -> view -> pages = $pages;
	}
	public function askAction() {
		$form = Model_Static_Loader::loadForm ( 'forum' );
		$forumModel = new Model_DbTable_Forum ();
		if ($this->getRequest ()->isPost () && $form->isValid ( $_POST )) {
			$question = $forumModel->createRow ( $form->getValues () );
			if(preg_match( '/^\w+[\w-\.]*\@\w+((-\w+)|(\w*))\.[a-z]{2,3}$/', $question->email) && (
                                        $question->category == 'Вопросы и запросы' || $question->category == 'Отзывы и предложения' || $question->category == 'Книга жалоб')){
                            $question -> content = str_replace("\n", "<br/>\n", $question -> content);
                            $question->save ();
			
                            $users = new Zend_Config_Xml ( APPLICATION_PATH . "/config/admins.xml" );
                            $users = $users->toArray ();
                            $mailer = new Zend_Mail ("UTF-8");
                            $mailer->setFrom (  $question->email,  $question->author );
                            $mailer->setSubject ( "форум" );
                            // wdaemon 2013-02-08  $mailer->setBodyHtml ( "Новый вопрос: " . $question->content, "utf8", "UTF-8");
                            $mailer->setBodyHtml ( "Новый вопрос: " . $question->content);
                            $mailer -> addTo ("info@alpha-hydro.com", "ALPHA-HYDRO info");
                            $mailer -> addBcc("fra@alpha-hydro.com", "Fedonov Roman A.");
                            $mailer -> addBcc("daemon007@mail.ru", "Быков Дмитрий Владимирович");			

                            foreach ( $users as $user ) if ( $user["role"] == "administrator" )
                                    $mailer->addTo ( $user ['email'], $user ['name'] );

                            $mailer->send ();
                            $this->view->error = 0;
                        }else{
                            $this->view->error = 1;
                        }    

                       
		} else {
			$this->_redirect ( $this->view->url ( array (
					"action" => "index" 
			) ) );
			return;
		}
	}
	public function answerAction() {
		if (! Zend_Auth::getInstance ()->hasIdentity ())
			throw new Zend_Exception ( "Вы не можете редактировать этот элемент" );
		
		$answer_content = $this->getRequest ()->getParam ( "answer" );
		$question = $this->getRequest ()->getParam ( "question" );
		
		$forumModel = new Model_DbTable_Forum ();
		$question = $forumModel->find ( $question )->current ();
		
		if (! $question)
			throw new Zend_Exception ( "Вопрос не найден" );
		
		$answer = $forumModel->createRow ();
		$answer->parent_id = $question->id;
		$answer->author = Zend_Auth::getInstance ()->getIdentity ()->name;
		$answer->email = Zend_Auth::getInstance ()->getIdentity ()->email;
		$answer->content = $answer_content;
		$answer->timestamp = date ( "Y-m-d H:i:s" );
		
		$answer->save ();
		
		// send mail
		$mailer = new Zend_Mail ("UTF-8");
		$mailer->setFrom ( "info@alpha-hydro.com", "Альфа Гидро" );
		$mailer->setSubject ( "Вам ответили на форуме Альфа Гидро" );
		$mailer->setBodyHtml ( "На ваш вопрос ответили!" );
		$mailer->addTo ( $question->email, $question->author );
		$mailer->send ();
		$tab = $_GET["tabName"];
		unset($_GET["tabName"]);
		$this->_redirect ( "/forum?".http_build_query($_GET)."#$tab" );
		exit ();
	}
	public function deleteAction() {
		if (! Zend_Auth::getInstance ()->hasIdentity ())
			throw new Zend_Exception ( "Вы не можете редактировать этот элемент" );
		
		$topic = $this->getRequest ()->getParam ( "id" );
		$accept = $this->getRequest ()->getParam ( "accept" );
		
		$forumModel = new Model_DbTable_Forum ();
		$topic = $forumModel->find ( $topic )->current ();
		
		if (! $topic)
			throw new Zend_Exception ( "Вопрос или ответ не найден" );
		
		if ($accept == "accepted") {
//			$topics = $topic->findDependentRowset ( "Model_DbTable_Forum" );
			$topic->delete ();
			
			$tab = $_GET["tabName"];
			unset($_GET["tabName"]);
			unset($_GET["id"]);
			unset($_GET["accept"]);
			$this->_redirect ( "/forum?".http_build_query($_GET)."#$tab" );
			exit ();
		} else
			$this->view->topic = $topic;
	}
	public function editAction() {
		if (! Zend_Auth::getInstance ()->hasIdentity ())
			throw new Zend_Exception ( "Вы не можете редактировать этот элемент" );
		
		$forumModel = new Model_DbTable_Forum ();
		$answer = $forumModel->find ( $this->getRequest ()->getParam ( 'id' ) )->current ();
		Zend_Layout::getMvcInstance ()->disableLayout ();
		
		$answer -> content = strip_tags($answer -> content);
		
		if (! $answer)
			throw new Zend_Exception ( "Ответ не найден" );
		
		if ($this->getRequest ()->isPost ()) {
			$content = $this->getRequest ()->getParam ( "answer" );
			$timestamp = $this->getRequest ()->getParam ( "timestamp" );
			if ($timestamp) {
				$timestamp = implode ( "-", array_reverse ( explode ( ".", $timestamp ) ) );
				$timestamp = strtotime ( $timestamp );
			} else
				$timestamp = time ();
			
			$user = Zend_Auth::getInstance ()->getIdentity ();
			
			$content = str_replace("\n", "<br/>\n", $content);
			
			$answer->content = $content;
			
			if ( $user -> role != 'administrator' ){
				$answer->author = $user->name;
				$answer->email = $user->email;
			}
			
			$answer->timestamp = date ( "Y-m-d", $timestamp ) . date ( " H:i:s" );
			$answer->save ();
			$this->_redirect ( "/forum" );
			exit ();
		}
		
		$this->view->answer = $answer;
	}
	public function viewAction() {
		if (! Zend_Auth::getInstance ()->hasIdentity ())
			throw new Zend_Exception ( "Вы не можете редактировать этот элемент" );
		
		$forumModel = new Model_DbTable_Forum ();
		$answer = $forumModel->find ( $this->getRequest ()->getParam ( 'id' ) )->current ();
		
		Zend_Layout::getMvcInstance ()->disableLayout ();
		
		if (! $answer)
			throw new Zend_Exception ( "Ответ не найден" );
		
		$this->view->answer = $answer;
	}
}
