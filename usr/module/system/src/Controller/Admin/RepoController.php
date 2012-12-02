<?php
/**
 * System admin module controller
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
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Rest\Client\RestClient;

class RepoController extends ActionController
{
    protected $repoClient;
    protected $repoUrl = 'http://repo.xoopsengine.org/module';
    protected $repoApi = 'http://api.xoopsengine.org/module';

    /**
     * Check update availability
     *
     * parameters: type, name[s]
     */
    public function checkAction()
    {
        if ($this->request->isPost()) {
            // Type: module, theme
            $type = $this->params()->fromPost('type', 'module');
            // Names: name of modules to check
            $name = $this->params()->fromPost('name');
        } else {
            // Type: module, theme
            $type = $this->params('type', 'module');
            // Names: name of modules to check
            $name = $this->params('name');
        }
        if (is_scalar($name)) {
            $status = rand(-1, 1);      // 1 - update available; 0 - no update; -1 - error occurred
            $version = '1.2.3';
            switch ($status) {
                case 1:
                    $message = sprintf(__('A new version %s is available'), $version);
                    break;
                case 0:
                    $message = __('No update available');
                    break;
                case -1:
                default:
                    $message = __('Error occurred, check later.');
                    break;
            }
            $result = array(
                'status'    => $status,
                'message'   => $message,
            );
        } else {
            foreach ($name as $key) {
                $status = rand(-1, 1);      // 1 - update available; 0 - no update; -1 - error occurred
                $version = '1.2.3';
                switch ($status) {
                    case 1:
                        $message = sprintf(__('A new version %s is available'), $version);
                        break;
                    case 0:
                        $message = __('No update available');
                        break;
                    case -1:
                    default:
                        $message = __('Error occurred, check later.');
                        break;
                }
                $result[$key] = array(
                    'status'    => $status,
                    'message'   => $message,
                );
            }
        }

        return $result;
    }
}
