<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Setup\Controller;

use Pi;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Finish page controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Finish extends AbstractController
{
    protected $hasBootstrap = true;

    public function indexAction()
    {
        $wizard = $this->wizard;
        $vars = $wizard->getPersist(static::PERSIST_HOST);
        $configs = array();

        // `www/boot.php`
        $file = $vars['www']['path'] . '/boot.php';
        $file_dist = $wizard->getRoot() . '/dist/boot.php.dist';
        $content = file_get_contents($file_dist);
        foreach ($vars as $var => $val) {
            if (!empty($val['path'])) {
                $content = str_replace(
                    '%' . $var . '%',
                    $val['path'],
                    $content
                );
            }
        }
        $content = str_replace(
            '%host%',
            $vars['config']['path'] . '/host.php',
            $content
        );
        $configs[] = array('file' => $file, 'content' => $content);

        // `www/.htaccess`
        $file = $vars['www']['path'] . '/.htaccess';
        $file_dist = $wizard->getRoot() . '/dist/.htaccess.dist';
        $content = file_get_contents($file_dist);
        $configs[] = array('file' => $file, 'content' => $content);

        // Write content to files and record errors in case occurred
        foreach ($configs as $config) {
            //$error = false;
            if (!$file = fopen($config['file'], 'w')) {
                //$error = true;
            } else {
                if (fwrite($file, $config['content']) == -1) {
                    //$error = true;
                }
                fclose($file);
            }
        }

        $readPaths = '<ul>';
        $readonly = $this->wizard->getConfig('readonly');
        foreach ($readonly as $section => $list) {
            foreach ($list as $item) {
                $file = Pi::path($section . '/' . $item);
                @chmod($file, 0644);
                $readPaths .= '<li class="files">' . $section . '/' . $item . '</li>';
                if (is_dir($file)) {
                    $objects = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($file),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($objects as $object) {
                        @chmod($object, 0644);
                    }
                }
            }
        }
        $readPaths .= '</ul>';

        $messagePattern =<<<EOT
<div class="well alert alert-success">
<h3>%s</h3>
<p>%s <a href="../index.php">%s</a></p>

<h3>%s</h3>
<p>%s</p>
</div>

<div class="well alert alert-warning">
<h3>%s</h3>
<p>%s</p>
<ol>
    <li>%s</li>
    <li>%s%s</li>
</ol>
</div>

EOT;
        $this->content = sprintf(
            $messagePattern,
            _s('Congratulations!'),
            _s('The system is set up successfully.'),
            _s('Click to visit your website!'),
            _s('Support'),
            _s('Visit <a href="http://pialog.org/" rel="external">Pi Engine Development Site</a> in case you need any help.'),
            _s('Security advisory'),
            _s('For security considerations please make sure the following operations are done:'),
            _s('Remove the installation folder <strong>{www}/setup/</strong> from your server manually.'),
            _s('Set configuration directories and files to readonly: '),
            $readPaths
        );

        $path = Pi::path('cache');
        $objects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($objects as $object) {
            if ($object->isFile() && 'index.html' != $object->getFilename()) {
                unlink($object->getPathname());
            }
        }

        // Clear setup persistent data
        $this->wizard->destroyPersist();
        // Clear system persistent data
        Pi::persist()->flush();
    }
}
