<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Laminas\Rest\Client\RestClient;

/**
 * Check module/theme updates against repos
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RepoController extends ActionController
{
    /**
     * Client to access module repos
     * @var object
     */
    protected $repoClient;

    /** @var string URL to module repos */
    protected $repoUrl = 'http://repo.piengine.org/module';

    /** @var string URL to module repo API */
    protected $repoApi = 'http://api.piengine.org/module';

    /**
     * AJAX: Check update availability
     *
     * @return array
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
            // 1 - update available; 0 - no update; -1 - error occurred
            $status  = rand(-1, 1);
            $version = '1.2.3';
            switch ($status) {
                case 1:
                    $message = sprintf(
                        _a('A new version %s is available'),
                        $version
                    );
                    break;
                case 0:
                    $message = _a('No update available');
                    break;
                case -1:
                default:
                    $message = _a('Error occurred, check later.');
                    break;
            }
            $result = [
                'status'  => $status,
                'message' => $message,
            ];
        } else {
            foreach ($name as $key) {
                // 1 - update available; 0 - no update; -1 - error occurred
                $status  = rand(-1, 1);
                $version = '1.2.3';
                switch ($status) {
                    case 1:
                        $message = sprintf(
                            _a('A new version %s is available'),
                            $version
                        );
                        break;
                    case 0:
                        $message = _a('No update available');
                        break;
                    case -1:
                    default:
                        $message = _a('Error occurred, check later.');
                        break;
                }
                $result[$key] = [
                    'status'  => $status,
                    'message' => $message,
                ];
            }
        }

        return $result;
    }
}
