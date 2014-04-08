<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $urlRedirect    = Pi::url($this->params('redirect', Pi::url('www')), true);
        $urlTrigger     = Pi::service('url')->assemble(
            '',
            array(
                'module'     => $this->getModule(),
                'controller' => 'index',
                'action'     => 'trigger',
            ),
            array(
                'query'      => array(
                    'redirect'   => $urlRedirect,
                ),
            )
        );

        Pi::service('authentication')->login(array('redirect' => $urlTrigger));
    }

    /**
     * Endpoint for SSO logout
     */
    public function logoutAction()
    {
        $redirect = Pi::url($this->params('redirect', Pi::url('www')), true);
        Pi::service('authentication')->logout(array('redirect' => $redirect));
    }

    public function initAction()
    {
        Pi::service('log')->mute();
        
        $isAuthenticated  = Pi::service('authentication')->getData();

        if (!$isAuthenticated) {
            $checkUrl = $this->url('', array('action' => 'check'));
            $load =<<<EOT
document.write(
    "<iframe id='check-sso' src='{$checkUrl}' border='0' frameborder='0' width='0' height='0' style='position:absolute;'></iframe>"
);
EOT;
            echo $load;
        }

        exit;
    }

    public function checkAction()
    {
        $isAuthenticated  = Pi::service('authentication')->getData();

        if (!$isAuthenticated) {
            $redirect   = $this->url('', array(
                'action'    => 'check',
                'update'    =>'yes'
            ));
            Pi::service('authentication')->login(array('redirect' => $redirect));

            exit();
        }

        $update = $this->params('update');
        if ($update == 'yes') {
            $load =<<<'EOT'
<html><head></head><body>
<script type="text/javascript">top.location.reload();</script>
</body></html>
EOT;
            echo $load;
        }

        exit;
    }


    /**
     * Endpoint for SSO ACS
     */
    public function acsAction()
    {
        $this->canonizeRequest();
        //$_SERVER['SCRIPT_NAME'] = $this->url('', array('action' => 'acs')) . 'sid';
        //$_SERVER['PATH_INFO']   = '-' . _get('sid');

        require_once Pi::path('vendor') . '/simplesamlphp/lib/_autoload.php';
        require_once Pi::path('vendor') . '/simplesamlphp/modules/saml/www/sp/saml2-acs.php';
    }

    /**
     * Endpoint for SSO ACS logout
     */
    public function acslogoutAction()
    {
        $this->canonizeRequest();
        //$_SERVER['SCRIPT_NAME'] = $this->url('', array('action' => 'acslogout')) . 'sid';
        //$_SERVER['PATH_INFO']   = '-' . _get('sid');

        require_once Pi::path('vendor') . '/simplesamlphp/lib/_autoload.php';
        require_once Pi::path('vendor') . '/simplesamlphp/modules/saml/www/sp/saml2-logout.php';
    }

    /**
     * trigger user-login event
     */
    protected function triggerAction()
    {
        $data = Pi::service('authentication')->getData();
        if ($data['id']) {
            Pi::service('event')->trigger('user-user_login', array('uid'=>$data['id']));
        }
        Pi::service('url')->redirect($this->params('redirect'));
    }

    /**
     * Canonize `SCRIPT_NAME` and `PATH_INFO` for SSP URL check
     *
     * @return void
     * @see lib/vendor/simplesamlphp/modules/saml/www/sp/saml2-acs.php
     * @see lib/vendor/simplesamlphp/modules/saml/www/sp/saml2-logout.php
     */
    protected function canonizeRequest()
    {
        $requestUri = Pi::service('url')->getRequestUri();
        if (($qpos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $qpos);
        }

        $sourceId = $this->params('sid');
        $sidPos = -1 * strlen($sourceId) - 1;
        $_SERVER['SCRIPT_NAME'] = substr($requestUri, 0, $sidPos);
        $_SERVER['PATH_INFO'] = substr($requestUri, $sidPos);
    }

    /**
     * Test for get data
     */
    public function getdataAction()
    {
        $data = Pi::service('authentication')->getData();
        //return $data;
        echo json_encode($data);
        exit;
    }
}