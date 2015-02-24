<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractBreadcrumbs;

class Breadcrumbs extends AbstractBreadcrumbs
{
    /**
     * {@inheritDoc}
     */
    public function load()
    {
        // Get params
        $params = Pi::service('url')->getRouteMatch()->getParams();

        // Set module link
        $moduleData = Pi::registry('module')->read($this->getModule());
        $result = array(
            array(
               'label' => $moduleData['title'],
               'href'  => Pi::url(Pi::service('url')->assemble('user', array(
                   'module' => $this->getModule(),
               ))),
            ),
        );

        switch ($params['controller']) {
            case 'login':
                $result[] = array(
                    'label' => __('Login'),
                );
                break;

            case 'register':
                $result[] = array(
                    'label' => __('Register'),
                );
                break;

            case 'home':
                $result[] = array(
                    'label' => __('Feed'),
                );
                break;

            case 'account':
                $result[] = array(
                    'label' => __('Account settings'),
                );
                break;

            case 'avatar':
                $result[] = array(
                    'label' => __('Change avatar'),
                );
                break;

            case 'privacy':
                $result[] = array(
                    'label' => __('Privacy'),
                );
                break;

            case 'password':
                switch ($params['action']) {
                    case 'index':
                        $result[] = array(
                            'label' => __('Change password'),
                        );
                        break;

                    case 'find':
                        $result[] = array(
                            'label' => __('Find password'),
                        );
                        break;
                }
                break;

            case 'profile':
                switch ($params['action']) {
                    case 'index':
                        $result[] = array(
                            'label' => __('Profile'),
                        );
                        break;

                    case 'edit.profile':
                        $result[] = array(
                            'label' => __('Edit profile'),
                        );
                        break;

                    case 'edit.compound':
                        $result[] = array(
                            'label' => __('Edit profile'),
                        );
                        break;
                }
                break;    
        }

        return $result;
    }
}