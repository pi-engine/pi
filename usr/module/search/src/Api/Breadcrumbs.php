<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Search\Api;

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
               	'href'  => Pi::url(Pi::service('url')->assemble('search', array(
                    'module' => $this->getModule(),
               	))),
           	),
        );

        switch ($params['action']) {
            case 'service':
                $result[] = array(
                    'label' => __(ucfirst($params['service'])),
                    'href'  => Pi::url(Pi::service('url')->assemble('search', array(
                       	'module' => $this->getModule(),
                       	'service' => $params['service'],
               	   	))),
                );
                break;

            case 'module':
        	      $moduleData = Pi::registry('module')->read($params['m']);
                $result[] = array(
                    'label' => $moduleData['title'],
                    'href'  => Pi::url(Pi::service('url')->assemble('search', array(
                        'module' => $this->getModule(),
                        'm' => $moduleData['name'],
                    ))),
                );
                break;
        }

        $query = _get('q');
        if (empty($query)) {
        	end($result);
        	$key = key($result);
        	unset($result[$key]['href']);
        } else {
            $result[] = array(
                'label' => $query,
            );
        }

        return $result;
    }
}