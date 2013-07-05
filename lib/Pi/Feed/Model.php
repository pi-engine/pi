<?php
/**
 * Feed data model class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Feed
 */

namespace Pi\Feed;

use Pi;

class Model
{
    public function __construct(array $data = array())
    {
        $this->initialize();

        if ($data) {
            $this->assign($data);
        }
    }

    public function setType($type)
    {
        $this->feed_link['type'] = $type;
        return $this;
    }

    public function getType()
    {
        $type = isset($this->feed_link['type']) ? $this->feed_link['type'] : null;
        return $type;
    }

    public function assign(array $data)
    {
        foreach ($data as $key => $val) {
            $this->__set($key, $val);
        }
        return $this;
    }

    public function __unset($var)
    {
        if (array_key_exists($var, $this->container)) {
            unset($this->$var);
        }
    }

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

    public function initialize()
    {
        $this->assign(array(
            'copyright'     => Pi::config('copyright', 'meta') ?: Pi::config('sitename'),
            'description'   => Pi::config('description', 'meta') ?: Pi::config('slogan'),
            'authors'       => array(
                array(
                    'name'      => Pi::config('author', 'meta'),
                    'email'     => Pi::config('adminmail'),
                ),
            ),
            'generator'     => array(
                'name'      => 'Pi Engine',
                'version'   => Pi::config('version'),
                'uri'       => 'http://www.xoopsengine.org',
            ),
            'image'         => array(
                'uri'       => Pi::url('static', true) . '/image/logo.png',
                'title'     => Pi::config('sitename'),
                'link'      => Pi::url('www', true),
            ),

            'language'      => Pi::service('i18n')->locale,
            'link'          => Pi::url('www', true),
            'feed_link'     => array(
                'link'      => Pi::url('www', true),
                'type'      => $this->type,
            ),
            'title'         => sprintf(__('Feed of %s - %s'), Pi::config('sitename'), Pi::config('slogan')),
            'encoding'      => Pi::service('i18n')->charset,
            'base_url'      => Pi::url('www', true),
            'entries'       => array(),
        ));
    }
}
