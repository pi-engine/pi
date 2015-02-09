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
        // Get config
        $showBreadcrumbs = Pi::config('show_breadcrumbs', $this->getModule());
        if (!$showBreadcrumbs) {
            return '';
        }

        // Set module link
        $moduleData = Pi::registry('module')->read($this->getModule());
        $result = array(
            array(
                'label' => $moduleData['title'],
            ),
        );

        $model = Pi::model('page', $this->getModule());
        // Get row
        $row = null;
        if ($id = _get('id')) {
            $row = $model->find($id);
        } elseif ($name = _get('name')) {
            $row = $model->find($name, 'name');
        } elseif ($slug = _get('slug')) {
            $row = $model->find($slug, 'slug');
        }
        $title = $row ? $row->title : __('Page request');
        $result[] = array(
            'label' => $title,
        );

        return $result;
    }
}