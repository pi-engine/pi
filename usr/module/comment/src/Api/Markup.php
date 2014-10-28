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
     * @param array $options Source type: `markdown`, `html`, `text`; array for options
     *
     * @return string
     */
    public function render($content, $options = array()) {
        if (isset($options['format'])) {
            $format = $options['format'];
        } else {
            $format = Pi::config('markup_format', $this->module);
        }
        if (isset($options['filters'])) {
            $filters = $options['filters'];
        } else {
            $filters = Pi::config('markup_filters', $this->module);
        }

        $options = array();
        switch ($format) {
            case 'javascript':
                $options = array(
                    'xss_filter'    => false,
                );
                $renderer = 'html';
                $parser = 'html';
                break;
            case 'html':
                $renderer = 'html';
                $parser = 'html';
                $options = array(
                    'xss_filter'    => false,
                );
                break;
            case 'markdown':
                $renderer = 'html';
                $parser = 'markdown';
                break;
            case 'text':
            default:
                $renderer = 'text';
                $parser = 'text';
                if (!empty($filters)) {
                    $options['filters'] = array_fill_keys($filters, array());
                    if (isset($options['filters']['linebreak'])) {
                        $options['newline'] = true;
                        unset($options['filters']['linebreak']);
                    }
                }
                break;
        }
        $result = Pi::service('markup')->render(
            $content,
            $renderer,
            $parser,
            $options
        );

        return $result;
    }
}
