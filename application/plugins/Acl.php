<?php
class Plugin_Acl extends Zend_Controller_Plugin_Abstract {
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
    	$acl = new Zend_Acl();
    	
    	$acl -> addResource("page");
    	$acl -> addResource("forum");
    	$acl -> addResource("catalog");
    	
    	$acl -> addRole("administrator");
    	$acl -> addRole("moderator");
    	
    	$acl -> allow("administrator");
    	
    	$acl -> deny("moderator");
    	$acl -> allow("moderator", "forum", array("answer", "edit-own"));

    	Zend_Registry::set('acl', $acl);
    }

}
