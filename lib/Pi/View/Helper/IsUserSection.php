<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Return is user section status
 *
 * Usage inside a phtml template
 * ```
 *  // Load helper for current request URI
 *  $userSection = $this->isUserSection();
 * ```
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class IsUserSection extends AbstractHelper
{
    /**
     * Invoke helper
     *
     * @param $module
     * @return bool
     */
    public function __invoke($module)
    {
        $uid         = Pi::user()->getId();
        $userSection = false;

        if (in_array($module, ['order', 'favourite', 'message', 'support', 'media']) && $uid > 0) {
            $userSection = true;
        } elseif ($module == 'user') {
            $d = (array)Pi::service('url')->getRouteMatch();
            foreach ($d as $value) {
                $a[] = $value;
            }

            if ($a[1]['controller'] == 'profile' && $a[1]['action'] != 'view') {
                $userSection = true;
            }

            if ($a[1]['controller'] == 'home' && $a[1]['action'] != 'view' && !isset($a[1]['uid'])) {
                $userSection = true;
            }

            if ($a[1]['controller'] == 'password' && $a[1]['action'] != 'find') {
                $userSection = true;
            }

            if ($a[1]['controller'] == 'activity' && !isset($a[1]['uid'])) {
                $userSection = true;
            }

            if (in_array($a[1]['controller'], ['dashboard', 'dashboardPro', 'account', 'avatar', 'privacy'])) {
                $userSection = true;
            }

        } elseif ($module == 'guide' && $uid > 0) {
            $d = (array)Pi::service('url')->getRouteMatch();
            foreach ($d as $value) {
                $a[] = $value;
            }
            if (($a[1]['controller'] == 'manage' || $a[1]['controller'] == 'request' || $a[1]['controller'] == 'planning') && $a[1]['action'] != 'preview') {
                $userSection = true;
            }
            if ($a[1]['controller'] == 'favourite' || $a[1]['controller'] == 'offer' || $a[1]['controller'] == 'stats') {
                $userSection = true;
            }
        } elseif ($module == 'event' && $uid > 0) {
            $d = (array)Pi::service('url')->getRouteMatch();
            foreach ($d as $value) {
                $a[] = $value;
            }
            if ($a[1]['controller'] == 'manage') {
                $userSection = true;
            }
        } elseif ($module == 'comment') {
            $d = (array)Pi::service('url')->getRouteMatch();
            foreach ($d as $value) {
                $a[] = $value;
            }
            if ($a[1]['controller'] == 'my') {
                $userSection = true;
            }
        }

        return $userSection;
    }
}