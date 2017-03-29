<?php
/**
 * 
 * @author Bakin Vladislav
 *
 */
class Model_Static_Auth implements Zend_Auth_Adapter_Interface {
	
	/**
	 * Username
	 *
	 * @var unknown
	 */
	protected $user = NULL;
	
	/**
	 * Password
	 *
	 * @var string
	 */
	protected $password = NULL;
	
	/**
	 * Create adapter
	 *
	 * @param string $user        	
	 * @param string $password        	
	 */
	function __construct($user, $password) {
		$this->user = trim($user);
		$this->password = $password;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see Zend_Auth_Adapter_Interface::authenticate()
	 */
	public function authenticate() {
		$users = new Zend_Config_Xml ( APPLICATION_PATH . "/config/admins.xml" );
		
		foreach ( $users->toArray() as $user )
			if ( $user['email'] == $this->user ){
				if ( $user['password'] == sha1 ( $this->password ) ){
					return new Zend_Auth_Result ( Zend_Auth_Result::SUCCESS, (object) $user );
				} else {
					return new Zend_Auth_Result ( Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $user );
				}
			}
		
		return new Zend_Auth_Result ( Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $user );
		
		
	}
}

?>