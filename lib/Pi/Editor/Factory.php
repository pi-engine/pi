<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Editor;

use Pi;

/**
 * Editor factory
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
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
            $type = Pi::config('editor') ?: 'pi';
        }
        switch ($type) {
            case 'html':
                $editor = Pi::config('editor') ?: 'ckeditor';
                break;
            /*
            case 'markitup':
            case 'markdown':
            case 'wiki':
            case 'bbcode':
                $options['set'] = $type;
                $editor = Pi::config('editor') ?: 'ckeditor';
                break;
            */
            default:
                $editor = $type;
                break;
        }
        $editorFile = sprintf(
            '%s/editor/%s/src/Renderer.php',
            Pi::path('usr'),
            $editor
        );
        if (file_exists($editorFile)) {
            include $editorFile;
        }
        $rendererClass =  'Editor\\' . ucfirst($editor) . '\Renderer';
        if (!class_exists($rendererClass)
            || !is_subclass_of($rendererClass, 'Pi\Editor\AbstractRenderer')
        ) {
            $rendererClass = __NAMESPACE__ . '\Pi\Renderer';
        }

        $renderer = new $rendererClass($options);

        return $renderer;
    }

    /**
     * Get available editor list
     *
     * @return array
     */
    public static function getList()
    {
        $list = array('pi' => __('Pi Default Editor'));

        $filter = function ($fileinfo) use (&$list) {
            if (!$fileinfo->isDir()) {
                return false;
            }
            $name = $fileinfo->getFilename();
            if (preg_match('/[^a-z0-9_]/i', $name)) {
                return false;
            }
            $configFile = $fileinfo->getPathname() . '/config.php';
            if (!file_exists($configFile)) {
                $list[$name] = $name;
                return;
            }
            $info = include $configFile;
            if (!empty($info['disable'])) {
                return;
            }
            if (!empty($info['name'])) {
                $list[$name] = $info['name'];
            }
        };
        Pi::service('file')->getList(
            'usr/editor',
            $filter
        );

        return $list;
    }
}
