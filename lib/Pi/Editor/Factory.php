<?php
/**
 * Pi Editor Factory
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
 * @package         Pi\Editor
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Editor;

use Pi;

class Factory
{
    /**
     * Loads an editor handler with configs
     *
     * @param string $type
     * @param array $options
     * @return AbstractRenderer
     */
    public static function load($type = null, $options = array())
    {
        if (empty($type)) {
            //$type = Pi::config()->loadDomain('text')->get('editor', 'text') ?: 'pi';
            $type = Pi::config('editor', 'text') ?: 'pi';
        }
        $editor = '';
        switch ($type) {
            case 'html':
                //$editor = Pi::config()->loadDomain('text')->get('editor', 'text') ?: 'ckeditor';
                $editor = Pi::config('editor', 'text') ?: 'ckeditor';
                break;
            /*
            case 'markitup':
            case 'markdown':
            case 'wiki':
            case 'bbcode':
                $options['set'] = $type;
                //$editor = Pi::config()->loadDomain('text')->get('editor', 'text') ?: 'ckeditor';
                $editor = Pi::config('editor', 'text') ?: 'ckeditor';
                break;
            */
            default:
                $editor = $type;
                break;
        }
        $editorFile = Pi::path('usr') . '/editor/' . $editor . '/src/Renderer.php';

        if (file_exists($editorFile)) {
            include $editorFile;
        }
        $rendererClass =  'Editor\\' . ucfirst($editor) . '\\Renderer';
        if (!class_exists($rendererClass) || !is_subclass_of($rendererClass, 'Pi\\Editor\\AbstractRenderer')) {
            $rendererClass = __NAMESPACE__ . '\\Pi\\Renderer';
        }

        $renderer = new $rendererClass($options);

        return $renderer;
    }

    public static function getList()
    {
        $list = array('pi' => __('Pi Default Editor'));
        $iterator = new \DirectoryIterator(Pi::path('usr') . '/editor');
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $name = $fileinfo->getFilename();
            if (preg_match("/[^a-z0-9_]/i", $name)) {
                continue;
            }
            $configFile = $fileinfo->getPathname() . "/config.php";
            if (!file_exists($configFile)) {
                $list[$name] = $name;
                continue;
            }
            $info = include $configFile;
            if (!empty($info["disable"])) continue;
            if (!empty($info["name"])) {
                $list[$name] = $info["name"];
            }
        }

        return $list;
    }
}
