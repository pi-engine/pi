<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Activity controller
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class ConditionController extends ActionController
{
    /**
     * Redirect to download action
     *
     * @return array|void
     */
    public function indexAction()
    {
        $this->redirect()->toRoute(
            '',
            array('action' => 'download')
        );

        return;
    }

    /**
     * Download last version of Term and condition file
     */
    public function downloadAction()
    {
        Pi::service('log')->mute();

        // Get condition list
        $condition = Pi::api('condition', 'user')->getLastEligibleCondition();

        if($condition){
            $destinationPath = Pi::url('upload/condition');
            $finalUrl = $destinationPath . '/' . $condition->filename;
            Pi::service('url')->redirect($finalUrl);
        } else {
            die(__("No active condition file"));
        }
        exit;
    }

    /**
     * Accept last version of Term and condition
     */
    public function acceptAction()
    {
        Pi::service('log')->mute();

        // Get condition list
        $condition = Pi::api('condition', 'user')->getLastEligibleCondition();
        $uid = Pi::user()->getId();

        if($condition && $uid){
            $log = array(
                'uid' => $uid,
                'data' => $condition->version,
                'action' => 'accept_conditions_from_bar',
            );

            Pi::api('log', 'user')->add(null, null, $log);
        }

        exit;
    }
}
