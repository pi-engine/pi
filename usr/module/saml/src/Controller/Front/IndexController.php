<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */
namespace Module\Saml\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class IndexController extends ActionController
{
    /**
     * Do nothing
     *
     * @return void
     */
    public function indexAction()
    {
        $this->redirect()->toRoute('', array('action' => 'login'));
    }

    /**
     * Endpoint for SSO login
     */
    public function loginAction()
    {
        $redirect = $this->params('redirect', '');
        Pi::service('authentication')->login(array('redirect' => $redirect));
    }

    /**
     * Endpoint for SSO logout
     */
    public function logoutAction()
    {
        $redirect = $this->params('redirect', '');
        Pi::service('authentication')->logout(array('redirect' => $redirect));
    }

    /**
     * Endpoint for SSO ACS
     */
    public function acsAction()
    {
        $_SERVER['SCRIPT_NAME'] = $this->url('',array('action'=>'acs')).'sid';
        $_SERVER['PATH_INFO']   = '-'._get('sid');

        require_once Pi::path('vendor') . '/simplesamlphp/lib/_autoload.php';
        require_once Pi::path('vendor') . '/simplesamlphp/modules/saml/www/sp/saml2-acs.php';
    }

    /**
     * Endpoint for SSO ACS logout
     */
    public function acslogoutAction()
    {
        $_SERVER['SCRIPT_NAME'] = $this->url('',array('action'=>'acslogout')).'sid';
        $_SERVER['PATH_INFO']   = '-'._get('sid');

        require_once Pi::path('vendor') . '/simplesamlphp/lib/_autoload.php';
        require_once Pi::path('vendor') . '/simplesamlphp/modules/saml/www/sp/saml2-logout.php';
    }

    /**
     * Test for get data
     */
    public function getdataAction()
    {
        $data = Pi::service('authentication')->getData();
        var_dump($data);
        exit;
    }
}