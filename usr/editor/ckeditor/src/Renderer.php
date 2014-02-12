<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Editor\Ckeditor;

use Pi;
use Pi\Editor\AbstractRenderer;
use Zend\Form\ElementInterface;
use CKFinder;
use Ckeditor;

/**
 * CKeditor renderer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Renderer extends AbstractRenderer
{
    protected $configFile = 'editor.ckeditor.php';
    protected $uploadConfig = array(
        'enabled'   => true,
        'path'      => '',
        'url'       => '',
    );

    /**
     * Renders editor contents
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $options = array_merge($this->getOptions(), $element->getOptions());
        $attributes = array_merge(
            $this->getAttributes(),
            $element->getAttributes()
        );

        // Set up language
        if (!isset($options['language'])) {
            $options['language'] = Pi::service('i18n')->locale;
        }
        $options['language'] = strtolower(str_replace('_', '-',
                                                      $options['language']));

        $basePath = isset($options['base_path'])
            ? $options['base_path']
            : Pi::path('www') . '/script/editor/ckeditor';
        include_once $basePath . '/ckeditor.php';
        include_once __DIR__ . '/Ckeditor.php';
        $baseUrl = isset($options['base_url'])
            ? $options['base_url']
            : Pi::url('www') . '/script/editor/ckeditor/';
        $ckEditor = new Ckeditor($baseUrl);
        $ckEditor->returnOutput = true;
        $ckEditor->textareaAttributes = array_merge(
            $ckEditor->textareaAttributes,
            $attributes
        );

        $this->setupFinder($ckEditor, $options);

        return $ckEditor->editor($element->getName(), $element->getValue(),
                                 $options);
    }

    protected function setupFinder($ckEditor)
    {
        $uploadConfig = isset($options['upload'])
            ? $options['upload'] : $this->uploadConfig;
        if (false === $uploadConfig || false === $uploadConfig['enabled']) {
            return;
        }

        if (empty($uploadConfig['path'])) {
            $uploadPath = Pi::path('upload') . '/ckeditor';
            $uploadUrl = Pi::url('upload') . '/ckeditor';
        } elseif (false === strpos($uploadConfig['path'], ':')
            && $uploadConfig['path']{0} !== '/'
        )  {
            $uploadPath = Pi::path('upload') . '/'
                        . Pi::service('module')->current() . '/'
                        . $uploadConfig['path'];
            $uploadUrl = Pi::url('upload') . '/'
                       . Pi::service('module')->current() . '/'
                       . $uploadConfig['path'];
        } else {
            $uploadPath = $uploadConfig['path'];
            $uploadUrl = $uploadConfig['url'];
        }

        //$width = $this->getOption('finder_width') ?: '100%';
        //$height = $this->getOption('finder_height') ?: 400;

        //$session =& Pi::service('session')->ckfinder;
        if (Pi::service('user')->getUser()->isAdmin()) {
            $role = 'admin';
        } elseif (Pi::service('user')->getUser()->isGuest()) {
            $role = '*';
        } else {
            $role = 'user';
        }
        $_SESSION['PI_CKFINDER'] = array(
            'role'  => $role,
            'path'  => $uploadPath,
            'url'   => $uploadUrl,
        );

        $basePath = isset($options['finder_path'])
            ? $options['finder_path']
            : Pi::path('www') . '/script/editor/ckfinder';
        include_once $basePath . '/ckfinder.php';

        $baseUrl = isset($options['finder_url'])
            ? $options['finder_url']
            : Pi::url('www') . '/script/editor/ckfinder/';
        CKFinder::SetupCKEditor($ckEditor, $baseUrl);
    }
}
