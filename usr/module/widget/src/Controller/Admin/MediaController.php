<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Admin;

use Pi;
//use Module\Widget\Form\BlockMediaForm as BlockForm;
use Pi\File\Transfer\Upload;
use Zend\Uri\Uri;

/**
 * For media list block
 */
class MediaController extends WidgetController
{
    /**
     * {@inheritDoc}
     */
    protected $type = 'media';

    /** @var  BlockForm */
    //protected $form;

    /** @var  string Root URL */
    protected $urlRoot;

    /** @var string Prefix for image files */
    protected $tmpPrefix = 'tmp.';

    /**
     * {@inheritDoc}
     */
    protected $editTemplate = 'widget-media';

    /**
     * {@inheritDoc}
     */
    protected $formClass = 'BlockMediaForm';

    /**
     * Get root URL
     *
     * @return string
     */
    protected function urlRoot()
    {
        if (!$this->urlRoot) {
            $this->urlRoot = Pi::url('upload') . '/' . $this->getModule();
        }

        return $this->urlRoot;
    }

    /**
     * {@inheritDoc}
     */
    protected function updateBlock($widgetRow, array $block)
    {
        $widgetMeta = $block['content'];
        $block['content'] = $this->canonizeContent($block['content']);
        if (isset($block['type'])) {
            unset($block['type']);
        }

        $result = Pi::api('block', 'system')->update(
            $widgetRow->block,
            $block
        );
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
     * {@inheritDoc}
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

    /**
     * {@inheritDoc}
     */
    public function uploadAction()
    {
        //Pi::service('log')->mute();
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
        $uploader->setExtension('jpg,png,gif');
        //->setRename('tmp.%random%');
        //->setImageSize(array('maxWidth' => 600, 'maxHeight' => 500));
        if ($uploader->isValid()) {
            $uploader->receive();
            $file = $uploader->getUploaded('image');
            $return['image'] = $file;
        } else {
            $messages = $uploader->getMessages();
            $return = array(
                'status'    => 0,
                'message'   => implode('; ', $messages),
            );
        }

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    protected function canonizePost(array $values)
    {
        /*
        // Set block configs
        if (empty($values['id'])) {
            $values['config'] = array(
                'height'    => array(
                    'title'         => _a('Block frame height'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                ),
                'interval' => array(
                    'title'         => _a('Time interval (ms)'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => 2000,
                ),
                'pause' => array(
                    'title'         => _a('Mouse event'),
                    'description'   => _a('Event to pause cycle'),
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
        */
        $values['content'] = $this->canonizeImage($values['content']);

        return $values;
    }

    /**
     * {@inheritDoc}
     */
    protected function canonizeContent($content)
    {
        $content = json_decode($content, true);
        $items = array();
        foreach ($content as $item) {
            if (!$this->isAbsoluteUrl($item['image'])) {
                $item['image'] = $this->urlRoot() . '/' . $item['image'];
            }
            $items[] = $item;
        }

        return json_encode($items);
    }

    /**
     * Canonize images
     *
     * @param string $content
     *
     * @return string
     */
    protected function canonizeImage($content)
    {
        $pathRoot = Pi::path('upload') . '/' . $this->getModule();
        $prefixLength = strlen($this->tmpPrefix);
        $content = json_decode($content, true);
        $items = array();
        foreach ($content as $item) {
            if ($this->tmpPrefix == substr($item['image'], 0, $prefixLength)) {
                $newName = substr($item['image'], $prefixLength);
                $renamed = rename($pathRoot . '/' . $item['image'],
                                  $pathRoot . '/' . $newName);
                if ($renamed) {
                    $item['image'] = $newName;
                }
            }

            $items[] = $item;
        }

        return json_encode($items);
    }

    /**
     * Delete image files
     *
     * @param array $images
     *
     * @return void
     */
    protected function deleteImages(array $images)
    {
        $path = Pi::path('upload') . '/' . $this->getModule();
        foreach ($images as $image) {
            if ($this->isAbsoluteUrl($image)) {
                continue;
            }
            $file = $path . '/' . $image;
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareFormValues($blockRow)
    {
        $data = $blockRow->toArray();
        //$values = array();
        foreach ($data['content'] as &$item) {
            if (!$this->isAbsoluteUrl($item['image'])) {
                $item['image'] = $this->urlRoot() . '/' . $item['image'];
            }
            //$values[] = $item;
        }

        return $data;
    }

    /**
     * Check if a link is absolute URL
     *
     * @param $link
     *
     * @return bool
     */
    protected function isAbsoluteUrl($link)
    {
        $uri = new Uri($link);

        return $uri->isAbsolute();
    }
}
