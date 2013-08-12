<?php
use Zend\EventManager\Event;

namespace Module\Page\Installer\Resource;

use Pi;
use Pi\Application\Installer\Resource\Page as BasicPage;

class Page extends BasicPage
{
    /**
     * Overwrite regular page updater to avoid page deletion
     *
     * @return bool
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('page')->clear($module);

        return true;
    }
}
