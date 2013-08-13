<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Page\Installer\Action;
use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Zend\EventManager\Event;

class Install extends BasicInstall
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', array($this, 'postInstall'), 1);
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
        $module = $e->getParam('module');
        $path = Pi::service('i18n')->getPath(array('module/page', ''))
              . '/install';
        if (!is_dir($path)) {
            $path = Pi::service('i18n')->getPath(
                array('module/page', ''),
                'en'
            ) . '/install';
        }
        $metaFile = $path . '/meta.txt';

        $meta = array_map('trim', file($metaFile));
        $meta = array_filter($meta);

        $apiHandler = Pi::api('page')->setModule($module);
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

        $result = array(
            'status'    => true,
            'message'   => 'Pages added.',
        );
        $this->setResult('post-install', $result);
    }
}
