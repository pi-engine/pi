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
 * Import data for test controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class ImportDataController extends ActionController
{
    public function indexAction()
    {
        $this->view()->setTemplate(false);

        $this->timelineLogAction();
        $this->activity();
        $this->group();
        $this->quickLink();
    }


    protected function timelineLogAction()
    {
        $timeline = 'user_action';
        $module   = $this->getModule();
        $prefix   = _get('prefix') ?: 'pi';
        $uid = 7;
        $model = $this->getModel('timeline_log');
        $model->delete(array());
        $messge = <<<'EOT'
this is test timeline message,this is test timeline message,
this is test timeline message,this is test timeline message,
this is test timeline message,this is test timeline message,
this is test timeline message,this is test timeline message,
EOT;
        for ($i = 1; $i < 40; $i++ ) {
            $data = array(
                'uid' => $uid,
                'timeline' => $timeline,
                'module'   => $module,
                'message'  => $prefix . $i . $messge,
                'time'     => time()- $i * 3600 * 12,
                'link'     => 'www.baidu.com',
            );

            $row = $model->createRow($data);
            $result[] = $row->save();
        }
        vd($result);
    }

    protected function activity()
    {
        $model = $this->getModel('activity');
        $model->delete(array());
        for ($i = 1; $i < 8; $i++) {
            $data = array(
                'name'     => 'user_activity' . $i,
                'title'    => 'Activity' . $i,
                'module'   => 'user',
                'link'     => 'www.google.com',
                'active'   => 1,
                'display'  => $i,
                'callback' => 'www.ggogle.com',
            );

            $model->createRow($data)->save();
        }
    }

    protected function group()
    {
        $group = array(
            array(
                'name'     => 'basic_info',
                'title'    => 'Basic info',
                'order'    => '1',
                'compound' => '',
            ),
            array(
                'name'     => 'address',
                'title'    => 'Address',
                'order'    => '2',
                'compound' => 'address',
            ),
            array(
                'name'     => 'work',
                'title'    => 'Work',
                'order'    => '3',
                'compound' => 'work',
            ),
            array(
                'name'     => 'education',
                'title'    => 'Education',
                'order'    => '4',
                'compound' => 'education',
            ),

        );

        $field = array(
            array(
                'field' => 'fullname',
                'group' => 'basic_info',
                'order' => '1',
            ),
            array(
                'field' => 'gender',
                'group' => 'basic_info',
                'order' => '2',
            ),
            array(
                'field' => 'birthdate',
                'group' => 'basic_info',
                'order' => '3',
            ),

            // Address
            array(
                'field' => 'country',
                'group' => 'address',
                'order' => '1',
            ),
            array(
                'field' => 'province',
                'group' => 'address',
                'order' => '2',
            ),
            array(
                'field' => 'city',
                'group' => 'address',
                'order' => '3',
            ),
            array(
                'field' => 'street',
                'group' => 'address',
                'order' => '4',
            ),
            array(
                'field' => 'room',
                'group' => 'address',
                'order' => '5',
            ),
            array(
                'field' => 'postcode',
                'group' => 'address',
                'order' => '6',
            ),

            // Work
            array(
                'field' => 'company',
                'group' => 'work',
                'order' => '1',
            ),
            array(
                'field' => 'department',
                'group' => 'work',
                'order' => '2',
            ),
            array(
                'field' => 'title',
                'group' => 'work',
                'order' => '3',
            ),
            array(
                'field' => 'description',
                'group' => 'work',
                'order' => '4',
            ),
            array(
                'field' => 'start',
                'group' => 'work',
                'order' => '5',
            ),
            array(
                'field' => 'end',
                'group' => 'work',
                'order' => '6',
            ),

            // Education
            array(
                'field' => 'school',
                'group' => 'education',
                'order' => '1',
            ),
            array(
                'field' => 'major',
                'group' => 'education',
                'order' => '2',
            ),
            array(
                'field' => 'degree',
                'group' => 'education',
                'order' => '3',
            ),
            array(
                'field' => 'class',
                'group' => 'education',
                'order' => '4',
            ),
            array(
                'field' => 'start',
                'group' => 'education',
                'order' => '5',
            ),
            array(
                'field' => 'end',
                'group' => 'education',
                'order' => '6',
            ),
        );

        $displayGroupModel = $this->getModel('display_group');
        $fieldDisplayModel = $this->getModel('field_display');

        $displayGroupModel->delete(array());
        $fieldDisplayModel->delete(array());

        foreach ($group as $row) {
            $groupRow = $displayGroupModel->createRow($row);
            $status[] = $groupRow->save();
        }
        vd($status);
        unset($status);

        foreach ($field as $row) {
            $fieldRow = $fieldDisplayModel->createRow($row);
            $status[] = $fieldRow->save();
        }

        vd($status);
    }

    protected function quickLink()
    {
        $count  = _get('count') ? : 10;
        $prefix = 'quicklink';

        $model = $this->getModel('quicklink');
        for ($i = 0; $i < $count; $i++) {
            $data = array(
                'name'    => $prefix . $i . 'name',
                'title'   => $prefix .$i . 'title',
                'module'  => $prefix .$i . 'module',
                'link'    => 'www.google.com/' . $i,
                'icon'    => '',
                'active'  => 1,
                'display' => 1,
            );

            $row = $model->createRow($data);
            $row->save();
            $ids = $row['id'] ? : '';
        }

        vd($ids);
    }
}