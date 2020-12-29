<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Feed;

use Pi;

/**
 * Feed data container for FeedModel
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Model
{
    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->initialize();

        if ($data) {
            $this->assign($data);
        }
    }

    /**
     * Set feed type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->feed_link['type'] = $type;

        return $this;
    }

    /**
     * Get feed type
     *
     * @return string|null
     */
    public function getType()
    {
        $type = isset($this->feed_link['type'])
            ? $this->feed_link['type']
            : 'rss';

        return $type;
    }

    /**
     * Assign data to model
     *
     * @param array $data
     *
     * @return $this
     */
    public function assign(array $data)
    {
        foreach ($data as $key => $val) {
            $this->__set($key, $val);
        }

        return $this;
    }

    /**
     * Magic method for unset
     *
     * @param string $var
     *
     * @return void
     */
    public function __unset($var)
    {
        if (array_key_exists($var, $this->container)) {
            unset($this->$var);
        }
    }

    /**
     * Magic method for set
     *
     * @param string $var
     * @param mixed  $val
     *
     * @return mixed
     */
    public function __set($var, $val)
    {
        switch ($var) {
            case 'type':
                $this->setType($val);
                break;
            case 'entry':
                $this->entries[] = $val;
                break;
            case 'author':
                $this->authors[] = $val;
                break;
            default:
                $this->$var = $val;
                break;
        }
    }

    /**
     * Magic method for get
     *
     * @param string $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        $return = null;
        switch ($var) {
            case 'type':
                $return = $this->getType();
                break;
            default:
                if (isset($this->$var)) {
                    $return = $this->$var;
                }
                break;
        }

        return $return;
    }

    /**
     * Initialize data model
     *
     * Attributes
     *
     *  - copyright
     *  - description
     *  - authors
     *      - name
     *      - email
     *  - generator
     *      - name
     *      - version
     *      - uri
     *  - image
     *      - uri
     *      - title
     *      - link
     *  - language
     *  - link
     *  - feed_link
     *      - link
     *      - type
     *  - title
     *  - encoding
     *  - base_url
     *  - entries
     *
     * @return void
     */
    public function initialize()
    {
        $logoFile = Pi::service('asset')->logo();
        $logo     = Pi::url($logoFile, true);

        $this->assign(
            [
                'copyright'   => Pi::config('copyright')
                    ?: Pi::config('sitename'),
                'description' => Pi::config('description')
                    ?: Pi::config('slogan'),
                'authors'     => [[
                                      'name'  => Pi::config('author'),
                                      'email' => Pi::config('adminmail'),
                                  ]],
                'generator'   => [
                    'name'    => 'Pi Engine',
                    'version' => Pi::config('version'),
                    'uri'     => 'http://piengine.org',
                ],
                'image'       => [
                    'uri'   => $logo,
                    'title' => Pi::config('sitename'),
                    'link'  => Pi::url('www', true),
                ],

                'language'  => Pi::service('i18n')->locale,
                'link'      => Pi::url('www', true),
                'feed_link' => [
                    'link' => Pi::url('www', true),
                    'type' => $this->type,
                ],
                'title'     => sprintf(
                    __('Feed of %s'),
                    Pi::config('sitename')
                ),
                'encoding'  => Pi::service('i18n')->charset,
                'base_url'  => Pi::url('www', true),
                'entries'   => [],
            ]
        );
    }
}
