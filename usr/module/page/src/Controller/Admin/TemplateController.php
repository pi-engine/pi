<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Index action controller
 */
class TemplateController extends ActionController
{
    /**
     * List of custom pages
     */
    public function indexAction()
    {
        $filter        = function ($fileinfo) {
            if (!$fileinfo->isFile()) {
                return false;
            }
            $name      = $fileinfo->getFilename();
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            if ('phtml' != $extension) {
                return false;
            }
            $name = pathinfo($name, PATHINFO_FILENAME);
            $file = $fileinfo->getPathname();
            return [
                'name' => $name,
                'time' => filemtime($file),
                'size' => filesize($file),
            ];
        };
        $baseTemplates = Pi::service('file')->getList(
            'custom/module/page/template/front',
            $filter
        );

        $customTemplates = Pi::service('file')->getList(
            Pi::path('theme') . '/' . Pi::config('theme') . '/custom/page',
            $filter
        );

        $templates = array_merge($baseTemplates, $customTemplates);
        $this->view()->assign('templates', $templates);
        $this->view()->assign('title', _a('Template list'));
        $this->view()->setTemplate('template-list');
    }

    /**
     * View a template file
     */
    public function viewAction()
    {
        Pi::service('log')->mute();
        $name = $this->params('name');

        $file = sprintf(
            '%s/' . Pi::config('theme') . '/custom/page/%s.phtml',
            Pi::path('theme'),
            $name
        );

        if (!is_readable($file)) {
            $file = sprintf(
                '%s/module/page/template/front/%s.phtml',
                Pi::path('custom'),
                $name
            );
        }

        if (is_readable($file)) {
            ob_start();
            highlight_file($file);
            $content = ob_get_clean();
        } else {
            $content = '';
        }
        $this->view()->assign([
            'content' => $content,
        ]);
        $this->view()->setLayout('layout-content')->setTemplate(false);
    }
}
