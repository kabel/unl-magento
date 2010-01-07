<?php
/**
 * PEAR Auth compatible container for CAS
 *
 * PHP version 5
 * 
 * @category  Default 
 * @package   UNL_Auth
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2008 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://pear.unl.edu/package/UNL_Auth
 */

include_once 'Auth/Container.php';

class UNL_Auth_CAS_PEARAuth extends Auth_Container
{
    /**
     * 
     * @var UNL_Auth_CAS
     */
    protected $cas;
    
    public function __construct($options)
    {
        $this->cas = UNL_Auth::factory('CAS', $options);
    }
    
    public function getPEARAuth($options = null, $loginFunction = null, $showLogin = true)
    {
        if (!isset($loginFunction)) {
            $loginFunction = array($this, 'login');
        }
        $auth = new Auth($this, $options, $loginFunction, $showLogin);
        if ($this->checkAuth()) {
            $auth->setAuth($this->getUsername());
        }
        $auth->setLogoutCallback(array($this,'logout'));
        return $auth;
    }
    
    public function login()
    {
        $this->cas->login();
    }
    
    public function logout()
    {
        return $this->cas->logout();
    }
    
    public function getAuth()
    {
        return $this->cas->isLoggedIn();
    }
    
    public function checkAuth()
    {
        return $this->cas->isLoggedIn();
    }
    
    public function getUsername()
    {
        return $this->cas->getUser();
    }
    
}
