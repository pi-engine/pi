<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Module\System\Controller\Front\LoginController as ActionController;
use Module\User\Form\LoginForm;
use Module\User\Form\LoginFilter;

/**
 * User login/logout controller
 */
class LoginController extends ActionController
{
    /**
     * Load system translations
     * {@inheritDoc}
     */
    protected function preAction($e)
    {
        Pi::service('i18n')->loadModule('default', 'system');
        return true;
    }

    /**
     * Load login form
     *
     * @param array $config
     *
     * @return LoginForm
     */
    protected function getForm(array $config)
    {
        $form = new LoginForm('login_page', $config);
        $form->setAttribute(
            'action',
            $this->url('', ['controller' => 'login', 'action' => 'process'])
        );

        return $form;
    }

    /**
     * Load login filter
     *
     * @param array $config
     *
     * @return LoginFilter
     */
    public function getInputFilter(array $config)
    {
        $filter = new LoginFilter($config);

        return $filter;
    }
}
