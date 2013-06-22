<?php
/**
 * Pi module installer action
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
 * @package         Module\Page
 * @subpackage      Installer
 * @version         $Id$
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
     * Pre-install pages for: Terms of service, Privacy, About us, Contact us, Join us, Help, Sitemap
     *
     * @param Event $e
     */
    public function postInstall(Event $e)
    {
        $module = $e->getParam('module');
        $path = Pi::service('i18n')->getPath(array('module/page', '')) . '/install';
        if (!is_dir($path)) {
            $path = Pi::service('i18n')->getPath(array('module/page', ''), 'en') . '/install';
        }
        $metaFile = $path . '/meta.txt';

        $meta = array_map('trim', file($metaFile));
        $meta = array_filter($meta);

        $apiHandler = Pi::service('api')->handler('page')->setModule($module);
        $pages = array_chunk($meta, 3);
        foreach ($pages as $page) {
            if (count($page) < 3) {
                break;
            }
            list($name, $markup, $title) = $page;
            $file = sprintf('%s/%s.txt', $path, $name);
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $content = str_replace(array('SITE_URL', 'SITE_NAME'), array(Pi::url('www'), Pi::config('sitename')), $content);
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
