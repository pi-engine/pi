<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Api;

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
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Check breadcrumbs
        if ($config['view_breadcrumbs']) {
            // Set model
            $modle = Pi::model('page', $this->getModule());
            // Get row
            if (!empty($params['id'])) {
                $row = $modle->find($params['id'])->toArray();
            } elseif (!empty($params['name'])) {
                $row = $modle->find($params['name'], 'name')->toArray();
            } elseif (!empty($params['slug'])) {
                $row = $modle->find($params['slug'], 'slug')->toArray();
            } else {
                $row = array(
                    'title' => __('Page request'),
                );
            }
            // Set link
            $result[] = array(
                'label' => $row['title'],
            );
            // return
        	return $result;
        } else {
        	return '';
        }
    }
}