<?php
/**
 * Account module ajax controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) http://www.eefocus.com
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Chuang Liu <liuchuang@eefocus.com>
 * @since           1.0
 * @package         Module\Account
 */

namespace Module\Account\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class AjaxController extends ActionController
{
    const AJAX_RESULT_TRUE  = 1;
    const AJAX_RESULT_FALSE = 0;

    public function indexAction()
    {

    }

    public function getProvinceAction()
    {
        $get         = $this->params()->fromQuery();
        $optionsFile = sprintf('%s/%s/config/countrylabel.php', Pi::path('module'), $this->getModule());
        $options     = include $optionsFile;
        $country     = $get['country'];
        $callback    = isset($get['callback']) ? $get['callback'] : '';

        $result = array();
        if (!empty($options[$country])) {
            foreach ($options[$country] as $key => $value) {
                $result[] = $key;
            }
        }
        $data =  json_encode($result);
        if ($callback) {

            $data = "{$callback}({$data})";
        }
        echo $data;
        exit;
    }

    public function getCityAction()
    {
        $get         = $this->params()->fromQuery();
        $optionsFile = sprintf('%s/%s/config/countrylabel.php', Pi::path('module'), $this->getModule());
        $options     = include $optionsFile;
        $country     = $get['country'];
        $province    = $get['province'];
        $callback    = isset($get['callback']) ? $get['callback'] : '';
        $result      = array();
        if (!empty($options[$country][$province])) {
            foreach ($options[$country][$province] as $value) {
                $result[] = $value;
            }
        }
        $data =  json_encode($result);
        if ($callback) {

            $data = "{$callback}({$data})";
        }
        echo $data;
        exit;
    }

    public function getSphereAction()
    {
        $get         = $this->params()->fromQuery();
        $optionsFile = sprintf('%s/%s/config/industrylabel.php', Pi::path('module'), $this->getModule());
        $options     = include $optionsFile;
        $industry    = $get['industry'];
        $callback    = isset($get['callback']) ? $get['callback'] : '';
        $result      = array();
        if (!empty($options[$industry])) {
            foreach ($options[$industry] as $value) {
                $result[] = $value;
            }
        }

        $data =  json_encode($result);
        if ($callback) {

            $data = "{$callback}({$data})";
        }
        echo $data;
        exit;
    }
}

