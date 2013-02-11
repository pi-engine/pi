<?php
/**
 * Action controller class
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
 * @package         Module\Widget
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\Widget\Controller\Admin;

use Pi;
use Module\Widget\Form\BlockCarouselForm as BlockForm;
use Pi\File\Transfer\Upload;

/**
 * For carousel block
 */
class CarouselController extends WidgetController
{
    protected $type = 'carousel';
    protected $form;
    protected $urlRoot;
    protected $tmpPrefix = 'tmp.';

    protected function getForm()
    {
        $this->form = $this->form ?: new BlockForm('block');
        return $this->form;
    }

    protected function urlRoot()
    {
        if (!$this->urlRoot) {
            $this->urlRoot = Pi::url('upload') . '/' . $this->getModule();
        }
        return $this->urlRoot;
    }

    protected function updateBlock($widgetRow, $block)
    {
        $widgetMeta = $block['content'];
        $block['content'] = $this->canonizeContent($block['content']);
        if (isset($block['type'])) {
            unset($block['type']);
        }

        $result = Pi::service('api')->system(array('block', 'update'), $widgetRow->block, $block);
        $status = $result['status'];
        if ($status) {
            $items = json_decode($widgetRow->meta, true);

            $widgetRow->name = $block['name'];
            $widgetRow->meta = $widgetMeta;
            $widgetRow->time = time();
            $widgetRow->save();

            $itemsNew = json_decode($widgetRow->meta, true);
            $imagesNew = array();
            foreach ($itemsNew as $item) {
                $imagesNew[] = $item['image'];
            }
            $images = array();
            foreach ($items as $item) {
                $images[] = $item['image'];
            }
            $imageList = array_diff($images, $imagesNew);
            $this->deleteImages($imageList);

        }
        return $status;
    }


    /**
     * List of carousel widgets
     */
    public function indexAction()
    {
        $this->view()->assign('widgets', $this->widgetList());
        $this->view()->assign('title', __('Carousel widgets'));
        $this->view()->setTemplate('list-carousel');
    }

    /**
     * Add a carousel block and default ACL rules
     */
    public function addAction()
    {
        parent::addAction();

        $this->view()->setTemplate('widget-carousel');
    }

    /**
     * Edit a carousel block
     */
    public function editAction()
    {
        parent::editAction();

        $this->view()->setTemplate('widget-carousel');
    }

    /**
     * Delete a block
     */
    public function deleteAction()
    {
        $images = array();
        $id = $this->params('id');
        if ($id) {
            $row = $this->getModel('widget')->find($id);
            $items = json_decode($row->meta, true);
            $images = array();
            foreach ($items as $item) {
                $images[] = $item['image'];
            }
        }
        $result = $this->deleteBlock();
        if ($result['status'] && $images) {
            $this->deleteImages($images);
        }
        $this->jump(array('action' => 'index'), $result['message']);
    }

    public function uploadAction()
    {
        Pi::service('log')->active(false);
        $return = array(
            'status'    => 1,
            'message'   => '',
            'image'     => '',
        );
        $rename = $this->tmpPrefix . '%random%';
        /**#@+
         * Just for demo for anonymous callback
         */
        /*
        $rename = function ($name)
        {
            $pos = strrpos($name, '.');
            if (false !== $pos) {
                $extension = substr($name, $pos);
                $name = substr($name, 0, $pos);
            } else {
                $extension = '';
            }
            $newName = $name . '.random-' .uniqid() . '.' . $extension;
            return $newName;
        };
        */
        /**#@-*/

        $uploader = new Upload(array('rename' => $rename));
        $uploader->setExtension('jpg,png,gif'); //->setRename('tmp.%random%'); //->setImageSize(array('maxWidth' => 600, 'maxHeight' => 500));
        if ($uploader->isValid()) {
            $uploader->receive();
            $file = $uploader->getUploaded('image');
            //$return['image'] = Pi::url('upload') . '/' . $this->getModule() . '/' . $file;
            $return['image'] = $file;
        } else {
            $messages = $uploader->getMessages();
            $return = array(
                'status'    => 0,
                'message'   => implode('; ', $messages),
            );
        }

        //return $return;
        /**#@+
         *  For iframe
         */
        $this->view()->setTemplate(false)->setLayout('layout-content');
        return json_encode($return);
        /**#@-*/
    }

    protected function canonizePost($values)
    {
        if (empty($values['id'])) {
            $values['config'] = array(
                'interval' => array(
                    'title'         => 'Time interval (ms)',
                    'edit'          => 'text',
                    'filter'        => 'number_int',
                    'value'         => 2000,
                ),
                'pause' => array(
                    'title'         => 'Mouse event to pause cycle',
                    'edit'          => array(
                        'type'  =>  'select',
                        'options'   => array(
                            'options'   => array(
                                'hover' => 'hover',
                            ),
                        ),
                    ),
                    'value'         => 'hover',
                ),
            );
        }
        $values['content'] = $this->canonizeImage($values['content']);

        return $values;
    }

    protected function canonizeContent($content)
    {
        $content = json_decode($content, true);
        $items = array();
        foreach ($content as $item) {
            $item['image'] = $this->urlRoot() . '/' . $item['image'];
            $items[] = $item;
        }
        return json_encode($items);
    }

    protected function canonizeImage($content)
    {
        $pathRoot = Pi::path('upload') . '/' . $this->getModule();
        $prefixLength = strlen($this->tmpPrefix);
        $content = json_decode($content, true);
        $items = array();
        foreach ($content as $item) {
            if ($this->tmpPrefix == substr($item['image'], 0, $prefixLength)) {
                $newName = substr($item['image'], $prefixLength);
                $renamed = rename($pathRoot . '/' . $item['image'], $pathRoot . '/' . $newName);
                if ($renamed) {
                    $item['image'] = $newName;
                }
            }

            $items[] = $item;
        }
        return json_encode($items);
    }

    protected function deleteImages($images)
    {
        $path = Pi::path('upload') . '/' . $this->getModule();
        foreach ($images as $image) {
            $file = $path . '/' . $image;
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
