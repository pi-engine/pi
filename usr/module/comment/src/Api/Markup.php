<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * Comment markup renderer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Markup extends AbstractApi
{
    /**
     * Render post content
     *
     * @param string $content   Raw content
     * @param array|string $options Source type: `markdown`, `html`, `text`; array for options
     *
     * @return string
     */
    public function render($content, $options = array())
    {
        if (is_string($options)) {
            $options = array('format' => $options);
        }
        if (isset($options['format'])) {
            $format = $options['format'];
        } else {
            $format = Pi::config('markup_format', $this->module);
        }

        $options = array();
        switch ($format) {
            case 'javascript':
                $options = array(
                    'filters'   => array(
                        'xss_sanitizer'    => false,
                    )
                );
                break;
            case 'html':
                $options = array(
                    'filters'   => array(
                        'xss_sanitizer'    => false,
                    )
                );
                break;
            case 'markdown':
                break;
            case 'text':
            default:
                if (isset($options['filters'])) {
                    $filters = $options['filters'];
                } else {
                    $filters = Pi::config('markup_filters', $this->module);
                    if (!empty($filters)) {
                        $filters = array_fill_keys($filters, array());
                    }
                }
                $options['filters'] = $filters;
                break;
        }
        $markup = $format ?: 'text';
        $result = Pi::service('markup')->compile(
            $content,
            $markup,
            $options
        );

        return $result;
    }
}
