<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
            $type = Pi::config('editor', 'text') ?: 'pi';
        }
        $editor = '';
        switch ($type) {
            case 'html':
                $editor = Pi::config('editor', 'text') ?: 'ckeditor';
                break;
            /*
            case 'markitup':
            case 'markdown':
            case 'wiki':
            case 'bbcode':
                $options['set'] = $type;
                $editor = Pi::config('editor', 'text') ?: 'ckeditor';
                break;
            */
            default:
                $editor = $type;
                break;
        }
        $editorFile = Pi::path('usr') . '/editor/' . $editor
                    . '/src/Renderer.php';

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
        $iterator = new \DirectoryIterator(Pi::path('usr') . '/editor');
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $name = $fileinfo->getFilename();
            if (preg_match('/[^a-z0-9_]/i', $name)) {
                continue;
            }
            $configFile = $fileinfo->getPathname() . '/config.php';
            if (!file_exists($configFile)) {
                $list[$name] = $name;
                continue;
            }
            $info = include $configFile;
            if (!empty($info['disable'])) continue;
            if (!empty($info['name'])) {
                $list[$name] = $info['name'];
            }
        }

        return $list;
    }
}
