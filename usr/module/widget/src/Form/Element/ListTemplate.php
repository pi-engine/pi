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

/**
 * Class for listing templates
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ListTemplate extends Select
{
    /**
     * Get templates
     * 
     * @return array
     */
    protected function getStyles()
    {
        $styles = array(
            'list/common'   => __('Title only'),
            'list/featured' => __('All with feature image'),
            'list/summary'  => __('All with summary'),
            'list/compound' => __('Compound'),
        );
        // Load custom templates
        $customPath = sprintf(
            '%s/module/widget/template/block/list',
            Pi::path('custom')
        );
        if (is_dir($customPath)) {
            $iterator = new \DirectoryIterator($customPath);
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isFile()) {
                    continue;
                }
                $filename = $fileinfo->getFilename();
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                if ('phtml' != $extension) {
                    continue;
                }
                $name = pathinfo($filename, PATHINFO_FILENAME);
                if (preg_match('/[^a-z0-9_\-]/', $name)) {
                    continue;
                }
                $styles['list/' . $name] = __('Custom: ') . $name;
            }
        }

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
