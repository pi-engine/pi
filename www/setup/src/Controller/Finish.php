<?php
/**
 * Pi Engine Setup Controller
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
 * @since           3.0
 * @package         Pi\Setup
 * @version         $Id$
 */

namespace Pi\Setup\Controller;
use Pi;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Finish extends AbstractController
{
    protected $hasBootstrap = true;

    public function init()
    {
        //$this->wizard->destroyPersist();
    }

    public function indexAction()
    {
        $wizard = $this->wizard;
        $vars = $wizard->getPersist('paths');
        //$this->normalizeHost($vars);
        $configs = array();

        /**#@+
         * htdocs/boot.php
         */
        $file = $vars['www']['path'] . '/boot.php';
        $file_dist = $wizard->getRoot() . '/dist/boot.php.dist';
        $content = file_get_contents($file_dist);
        foreach ($vars as $var => $val) {
            if (!empty($val['path'])) {
                $content = str_replace('%' . $var . '%', $val['path'], $content);
            }
        }
        $content = str_replace('%host%', $vars['config']['path'] . '/host.php', $content);
        /*
        $content = preg_replace('/(define\()([\'"])(PI_PATH_LIB)\\2,\s*([\'"])(.*?)\\4\s*\)/', 'define(\'PI_PATH_LIB\', \'' . $vars['lib']['path'] . '\')', $content);
        $content = preg_replace('/(define\()([\'"])(PI_PATH_WWW)\\2,\s*([\'"])(.*?)\\4\s*\)/', 'define(\'PI_PATH_WWW\', \'' . $vars['www']['path'] . '\')', $content);
        $content = preg_replace('/(define\()([\'"])(PI_PATH_VAR)\\2,\s*([\'"])(.*?)\\4\s*\)/', 'define(\'PI_PATH_VAR\', \'' . $vars['var']['path'] . '\')', $content);
        $content = preg_replace('/(define\()([\'"])(PI_PATH_HOST)\\2,\s*([\'"])(.*?)\\4\s*\)/', 'define(\'PI_PATH_HOST\', \'' . $vars['config']['path'] . '/host.php\')', $content);
        */
        $configs[] = array('file' => $file, 'content' => $content);
        /**#@-*/

        /**#@+
         * htdocs/.htaccess
         */
        $file = $vars['www']['path'] . '/.htaccess';
        $file_dist = $wizard->getRoot() . '/dist/.htaccess.dist';
        $content = file_get_contents($file_dist);
        $configs[] = array('file' => $file, 'content' => $content);
        /**#@-*/

        // Write content to files and record errors in case occured
        foreach ($configs as $config) {
            $error = false;
            if (!$file = fopen($config['file'], 'w')) {
                $error = true;
            } else {
                if (fwrite($file, $config['content']) == -1) {
                    $error = true;
                }
                fclose($file);
            }
        }

        $readPaths = "<ul>";
        $readonly = $this->wizard->getConfig('readonly');
        foreach ($readonly as $section => $list) {
            foreach ($list as $item) {
                $file = Pi::path($section . '/' . $item);
                @chmod($file, 0644);
                $readPaths .= '<li class="files">' . $section . '/' . $item . '</li>';
                if (is_dir($file)) {
                    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file), RecursiveIteratorIterator::CHILD_FIRST);
                    foreach ($objects as $object) {
                        @chmod($file, 0644);
                    }
                }
            }
        }
        $readPaths .= "</ul>";

        $message = <<<'HTML'
<h2>Congratulatons, everything is done! <a href='../index.php'>Ready to visit your site?</a></h2>
<h3>Security advisory</h3>
<ol>For security considerations you are strongly recommended to complete the following actions:
    <li>Remove the installation folder <strong>%s</strong> from your server.</li>
    <li>Set configuration directories and files to readonly: %s</li>
</ol>
<h3>Support</h3>
<p>Visit <a href='http://www.xoopsengine.org/' rel='external'>Pi Engine Development Site</a> in case you need any help.</p>
HTML;
        $this->content = sprintf(_t($message), basename(dirname(__DIR__)), $readPaths);

        $path = Pi::path('cache');
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $object) {
            if ($object->isFile() && 'index.html' != $object->getFilename()) {
                unlink($object->getPathname());
            }
        }

        Pi::persist()->flush();
    }
}
