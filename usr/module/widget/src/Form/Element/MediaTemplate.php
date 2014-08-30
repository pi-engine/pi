<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form\Element;

use Pi;
use Zend\Form\Element\Select;

class MediaTemplate extends Select
{
    /** @var string Dir name for custom templates */
    protected $templateDir = 'media';

    /**
     * Get custom template list
     *
     * @return array
     */
    protected function getList()
    {
        // Load custom templates
        $customPath = sprintf(
            '%s/module/widget/template/block/%s',
            Pi::path('custom'),
            $this->templateDir
        );
        $list =  array();
        $filter = function ($fileinfo) use (&$list) {
            if (!$fileinfo->isFile()) {
                return false;
            }
            $filename = $fileinfo->getFilename();
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if ('phtml' != $extension) {
                return false;
            }
            $name = $fileinfo->getBasename();
            if (!preg_match('/[^a-z0-9_\-]/', $name)) {
                return false;
            }

            $list[$this->templateDir . '/' . $name] =  _a('Custom: ') . substr($name, 0, -6);
        };
        Pi::service('file')->getList($customPath, $filter);

        return $list;
    }

    /**
     * Get full template list
     *
     * @return array
     */
    protected function getStyles()
    {
        $styles = array(
            $this->templateDir . '/image-left'  => _a('Image on left'),
            $this->templateDir . '/image-right' => _a('Image on right'),
        );
        $styles += $this->getList();

        return $styles;
    }

    /**
     * {@inheritDoc}
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $this->valueOptions = $this->getStyles();
        }

        return $this->valueOptions;
    }
}
