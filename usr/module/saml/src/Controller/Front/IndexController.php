<?php
namespace Module\Saml\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class IndexController extends ActionController
{
    public function indexAction()
    {
    }

    public function loginAction()
    {
        Pi::service('authentication')->login(array('redirect'=>'/admin'));
    }

    public function acsAction()
    {
        /*
         * TODO
         */
        $_SERVER['PATH_INFO'] = '/sp';

        require_once Pi::path('vendor') . '/simplesamlphp/lib/_autoload.php';
        require_once Pi::path('vendor') . '/simplesamlphp/modules/saml/www/sp/saml2-acs.php';
    }

    public function logoutAction()
    {
        Pi::service('authentication')->logout(array('redirect'=>'/user'));
    }

    public function acslogoutAction()
    {
        /*
         * TODO
         */
        $_SERVER['PATH_INFO'] = '/sp';

        require_once Pi::path('vendor') . '/simplesamlphp/lib/_autoload.php';
        require_once Pi::path('vendor') . '/simplesamlphp/modules/saml/www/sp/saml2-logout.php';
    }

    public function getdataAction()
    {
        $data = Pi::service('authentication')->getData();
        var_dump($data);
        exit;
    }
}