<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Laminas\EventManager\Event;

class Install extends BasicInstall
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', [$this, 'postInstall'], 1);
        parent::attachDefaultListeners();
        return $this;
    }

    /**
     * Pre-install pages for: Terms of service, Privacy,
     * About us, Contact us, Join us, Help, Sitemap
     *
     * @param Event $e
     */
    public function postInstall(Event $e)
    {
        $module     = $e->getParam('module');
        $apiHandler = Pi::api('api', 'page')->setModule($module);

        // Add demo pages
        $path = Pi::service('i18n')->getPath(['module/page', ''])
            . '/install';
        if (!is_dir($path)) {
            $path = Pi::service('i18n')->getPath(
                ['module/page', ''],
                'en'
            ) . '/install';
        }
        $metaFile = $path . '/meta.ini';
        $metaList = parse_ini_file($metaFile, true);
        foreach ($metaList as $name => $meta) {
            if (!isset($meta['name'])) {
                $meta['name'] = $name;
            }
            $file = sprintf('%s/%s.txt', $path, $name);
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $content = str_replace(
                    ['SITE_URL', 'SITE_NAME'],
                    [Pi::url('www'), Pi::config('sitename')],
                    $content
                );
            } else {
                $content = '';
            }
            $meta['content']  = $content;
            $meta['template'] = 'page-view-simple';
            $apiHandler->add($meta);
        }

        /*
        $meta = array_map('trim', file($metaFile));
        $meta = array_filter($meta);

        $pages = array_chunk($meta, 3);
        foreach ($pages as $page) {
            if (count($page) < 3) {
                break;
            }
            list($name, $markup, $title) = $page;
            $file = sprintf('%s/%s.txt', $path, $name);
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $content = str_replace(
                    array('SITE_URL', 'SITE_NAME'),
                    array(Pi::url('www'), Pi::config('sitename')),
                    $content
                );
            } else {
                $content = '';
            }
            $page = compact('name', 'markup', 'title', 'content');
            $apiHandler->add($page);
        }
        */

        // Add pre-defined pages
        $pages = [
            [
                'name'    => 'demo',
                'slug'    => 'phtml-demo',
                'markup'  => 'phtml',
                'title'   => _a('Demo for PHTML page'),
                'content' => 'page-demo',
            ],
            [
                'name'    => 'feed',
                'slug'    => 'feed',
                'markup'  => 'phtml',
                'title'   => _a('RSS Feed'),
                'content' => 'page-feed',
            ],
            [
                'name'    => 'sitemap',
                'slug'    => 'sitemap',
                'markup'  => 'phtml',
                'title'   => _a('Sitemap'),
                'content' => 'page-sitemap',
            ],

            [
                'name'    => 'xml-sitemap',
                'slug'    => 'xml-sitemap',
                'markup'  => 'phtml',
                'title'   => _a('XML Sitemap'),
                'content' => 'page-xml-sitemap',
            ],

        ];
        foreach ($pages as $page) {
            $apiHandler->add($page);
        }

        $result = [
            'status'  => true,
            'message' => _a('Pages added.'),
        ];
        $this->setResult('post-install', $result);
    }
}
