<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Admin;

use Pi;

/**
 * For list group block
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ListController extends WidgetController
{
    /**
     * {@inheritDoc}
     */
    protected $type = 'list';

    /**
     * {@inheritDoc}
     */
    protected $editTemplate = 'widget-list';

    /**
     * {@inheritDoc}
     */
    protected $formClass = 'BlockListForm';

    /**
     * {@inheritDoc}
     *
     * Canonize link URLs
     */
    protected function canonizePost(array $values)
    {
        $items = json_decode($values['content'], true);
        $items = $this->canonizeUrls($items);
        $values['content'] = json_encode($items);
        $values = parent::canonizePost($values);

        return $values;
    }

    /**
     * Canonize item URLs
     *
     * @param array $list
     *
     * @return array
     */
    protected function canonizeUrls(array $list)
    {
        array_walk($list, function (&$item) {
            if (!empty($item['link'])) {
                if (!preg_match('|^http[s]?://|i', $item['link'])) {
                    $item['link'] = Pi::url('www') . '/' . ltrim($item['link'], '/');
                }
            }
        });

        return $list;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareContent($content)
    {
        $items = $content ? json_decode($content, true) : array();
        $items = array_filter($items);
        $content = json_encode($items);

        return $content;
    }
}
