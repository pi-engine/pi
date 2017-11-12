<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Items for social sharing rendering
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class SocialSharing extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'social-sharing';

    /**
     * Get item specifications
     *
     * @param string $name
     *
     * @return array|null
     */
    public function getItem($name)
    {
        return $this->getOption('items', $name);
    }

    /**
     * Get all items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->getOption('items');
    }

    /**
     * Get associative array of item list
     *
     * @return array
     */
    public function getList()
    {
        $items = $this->getItems();
        $list = array();
        foreach ($items as $key => $item) {
            $list[$key] = $item['title'];
        }

        return $list;
    }

    /**
     * Build items
     *
     * @param string $title
     * @param string $url
     * @param string $image
     *
     * @return array|null
     */
    public function buildItems($title, $url, $image)
    {
        $result = array();
        $items = $this->getItems();
        foreach (array_keys($items) as $name) {
            $result[$name] = $this->buildItem($name, $title, $url, $image);
        }

        return $result;
    }

    /**
     * Build item specifications
     *
     * @param string|array $item
     * @param string $title
     * @param string $url
     * @param string $image
     *
     * @return array|null
     */
    public function buildItem($item, $title, $url, $image)
    {
        if (is_string($item)) {
            $identifier = $item;
            $item = $this->getItem($item);
            $item['identifier'] = $identifier;
        }
        if ($item) {
            $item['url'] = str_replace(
                array('%title%', '%url%', '%image%'),
                array($title . ' ', $url, $image),
                $item['url']
            );
        }

        return $item;
    }
}
