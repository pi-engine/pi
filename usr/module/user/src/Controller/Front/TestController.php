<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Test cases controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class TestController extends ActionController
{
    /**
     * Default action if none provided
     *
     * @return string
     */
    public function indexAction()
    {
        $this->view()->setTemplate(false);

        $fields = Pi::registry('profile', 'user')->read('compound');
        vd($fields);
        $fields = Pi::registry('compound', 'user')->read('education');
        vd($fields);

        vd(Pi::user()->hasIdentity());
        vd(Pi::user()->getUrl('register'));
    }

    protected function flushUsers()
    {
        Pi::model('account', 'user')->delete(array());
        Pi::model('profile', 'user')->delete(array());
        //Pi::model('custom', 'user')->delete(array());
        Pi::model('compound', 'user')->delete(array());
    }

    public function addAction()
    {
        $this->view()->setTemplate(false);

        //$this->flushUsers();

        $users = array();

        $prefix = _get('prefix') ?: 'pi';
        $count  = _get('count') ?: 50;


        $genderMap      = array('male', 'female', 'unknown');
        $languageMap    = array('en', 'fa', 'fr', 'zh-cn');
        $countryMap     = array('China', 'England', 'France', 'Iran');
        $degreeMap      = array('Ph.D', 'Master', 'Bachelor', 'College',
                                'High school', 'Middle school',
                                'Preliminary school');

        for ($i = 1; $i <= $count; $i++) {
            $user = array(
                'identity'      => $prefix . '_' . $i,
                'credential'    => $prefix . '_' . $i,
                'name'          => ucfirst($prefix) . ' ' . $i,
                'email'         => $prefix . '_' . $i . '@pialog.org',

                'fullname'      => ucfirst($prefix) . ' User ' . $i,
                'gender'        => $genderMap[$i % 3],
                'birthdate'     => (1900 + $i % 100) . '-'
                                   . ($i % 12 + 1) . '-' . ($i % 30 + 1),
                'location'      => 'From ' . $i,
                'signature'     => 'Signature of user ' . $i,
                'bio'           => 'User bio: ' . $i,

                'language'      => $languageMap[$i % 4],
                'demo_sample'   => 'Demo Sample: ' . $i,

                'address'       => array(
                    'country'   => $countryMap[$i % 4],
                    'province'  => 'Province ' . $i,
                    'city'      => 'City ' . $i,
                    'street'    => 'Street ' . $i,
                    'room'      => 'Room ' . $i,
                    'postcode'  => 'Code ' . $i,
                ),

                'tool'          => array(
                    array(
                        'title'         => 'Google+',
                        'identifier'    => rand(),
                    ),
                    array(
                        'title'         => 'Twitter',
                        'identifier'    => 'twitter_' . $i,
                    ),
                    array(
                        'title'         => 'QQ',
                        'identifier'    => '88' . $i,
                    ),
                ),

                'education' => array(
                    array(
                        'school'    => 'School 1 ' . $i,
                        'major'     => 'Major 1 ' . $i,
                        'degree'    => $degreeMap[$i % 7],
                        'class'     => 'Class 1 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                    array(
                        'school'    => 'School 2 ' . $i,
                        'major'     => 'Major 2  ' . $i,
                        'degree'    => $degreeMap[$i % 7],
                        'class'     => 'Class 2 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                    array(
                        'school'    => 'School 3 ' . $i,
                        'major'     => 'Major 3 ' . $i,
                        'degree'    => $degreeMap[$i % 7],
                        'class'     => 'Class 3 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),

                ),

                'work'  => array(
                    array(
                        'company'       => 'Company 1 ' . $i,
                        'department'    => 'Dept 1  ' . $i,
                        'title'         => 'Title 1 ' . $i,
                        'description'   => 'Desc 1 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                    array(
                        'company'       => 'Company 2 ' . $i,
                        'department'    => 'Dept 2  ' . $i,
                        'title'         => 'Title 2 ' . $i,
                        'description'   => 'Desc 2 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                ),
            );
            list($uid, $result) = Pi::service('user')->addUser($user);
            if ($uid) {
                $users[$uid] = $user;
            }
        }

        vd($users);
    }

    public function getAction()
    {
        $this->view()->setTemplate(false);

        $field = explode(',', _get('field'));
        $uid = explode(',', _get('uid'));


        $conditions = array(
            'active'    => 0,
            'birthdate' => '1901-2-2',
        );
        $uids = Pi::user()->getUids($conditions);
        vd($uids);
        //$conditions = array('active' => 0);
        $count = Pi::user()->getCount($conditions);
        vd($count);


        $field[] = 'birthdate';
        //$field = Pi::user()->getMeta();
        vd($field);
        $fields = Pi::user()->get($uid, $field, true);
        d($fields);

    }

    public function activateAction()
    {
        $this->view()->setTemplate(false);

        $uid = _get('uid');
        Pi::user()->activateUser($uid);

        $row = Pi::model('account', 'user')->find($uid);
        //vd($row);
        //vd($row->active);
        $fields = Pi::user()->get($uid, array('active', 'time_activated'));
        d($fields);
    }

    // enableAction
    // disableAction
    // deleteAction
}
