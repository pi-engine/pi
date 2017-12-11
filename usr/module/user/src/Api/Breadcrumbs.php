<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        $result     = [
            [
                'label' => $moduleData['title'],
                'href'  => Pi::url(Pi::service('url')->assemble('user', [
                    'module' => $this->getModule(),
                ])),
            ],
        ];

        switch ($params['controller']) {
            case 'login':
                $result[] = [
                    'label' => __('Login'),
                ];
                break;

            case 'register':
                $result[] = [
                    'label' => __('Register'),
                ];
                break;

            case 'home':
                $result[] = [
                    'label' => __('Feed'),
                ];
                break;

            case 'account':
                $result[] = [
                    'label' => __('Dashboard'),
                    'href'  => Pi::url(Pi::service('url')->assemble('user', [
                        'module'     => $this->getModule(),
                        'controller' => 'dashboard',
                    ])),
                ];
                $result[] = [
                    'label' => __('Account settings'),
                ];
                break;

            case 'avatar':
                $result[] = [
                    'label' => __('Dashboard'),
                    'href'  => Pi::url(Pi::service('url')->assemble('user', [
                        'module'     => $this->getModule(),
                        'controller' => 'dashboard',
                    ])),
                ];
                $result[] = [
                    'label' => __('Change avatar'),
                ];
                break;

            case 'privacy':
                $result[] = [
                    'label' => __('Dashboard'),
                    'href'  => Pi::url(Pi::service('url')->assemble('user', [
                        'module'     => $this->getModule(),
                        'controller' => 'dashboard',
                    ])),
                ];
                $result[] = [
                    'label' => __('Privacy'),
                ];
                break;

            case 'password':
                $result[] = [
                    'label' => __('Dashboard'),
                    'href'  => Pi::url(Pi::service('url')->assemble('user', [
                        'module'     => $this->getModule(),
                        'controller' => 'dashboard',
                    ])),
                ];
                switch ($params['action']) {
                    case 'index':
                        $result[] = [
                            'label' => __('Change password'),
                        ];
                        break;

                    case 'find':
                        $result = [
                            [
                                'label' => __('Find password'),
                            ]
                        ];
                        break;
                }
                break;

            case 'profile':
                switch ($params['action']) {
                    case 'index':
                        $result[] = [
                            'label' => __('Profile'),
                        ];
                        break;

                    case 'edit.profile':
                        $result[] = [
                            'label' => __('Dashboard'),
                            'href'  => Pi::url(Pi::service('url')->assemble('user', [
                                'module'     => $this->getModule(),
                                'controller' => 'dashboard',
                            ])),
                        ];
                        $result[] = [
                            'label' => __('Edit profile'),
                        ];
                        break;

                    case 'edit.compound':
                        $result[] = [
                            'label' => __('Dashboard'),
                            'href'  => Pi::url(Pi::service('url')->assemble('user', [
                                'module'     => $this->getModule(),
                                'controller' => 'dashboard',
                            ])),
                        ];
                        $result[] = [
                            'label' => __('Edit profile'),
                        ];
                        break;
                }
                break;

            case 'dashboard':
                $result[] = [
                    'label' => __('Dashboard'),
                ];
                break;
        }

        return $result;
    }
}