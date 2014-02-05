<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Front;

use Module\System\Controller\Front\LoginController as ActionController;
use Module\User\Form\LoginForm;
use Module\User\Form\LoginFilter;

/**
 * User login/logout controller
 */
class LoginController extends ActionController
{
    /**
     * Load login form
     *
     * @param array $config
     *
     * @return LoginForm
     */
    protected function getForm(array $config)
    {
        $form = new LoginForm('login', $config);
        $form->setAttribute(
            'action',
            $this->url('', array('controller' => 'login', 'action' => 'process'))
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
