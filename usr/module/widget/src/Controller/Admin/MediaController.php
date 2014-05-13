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
        return Pi::url('upload') . '/' . $this->getModule();
    }

    /**
     * Get root path for upload
     *
     * @return string
     */
    protected function pathRoot()
    {
        return Pi::path('upload') . '/' . $this->getModule();
    }

    /**
     * {@inheritDoc}
     */
    protected function updateBlock($widgetRow, array $block)
    {
        // Old items
        //$items = json_decode($widgetRow->meta, true);
        $items = $widgetRow->meta;

        // Regular update
        $status = parent::updateBlock($widgetRow, $block);

        // Handling images
        if ($status) {
            //$itemsNew   = json_decode($widgetRow->meta, true);
            $itemsNew   = $widgetRow->meta;
            $imagesNew  = array();
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
            //$items = json_decode($row->meta, true);
            $items = $row->meta;
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
        $return = array(
            'status'    => 1,
            'message'   => '',
            'image'     => '',
        );
        $rename = $this->tmpPrefix . '%random%';

        $uploader = new Upload(array('rename' => $rename));
        $uploader->setDestination($this->pathRoot())->setExtension('jpg,png,gif');
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
        $values['content'] = json_decode($values['content'], true);
        $values['content'] = $this->canonizeImage($values['content']);

        return $values;
    }

    /**
     * {@inheritDoc}
     */
    protected function canonizeContent($content)
    {
        //$content = json_decode($content, true);
        $items = array();
        foreach ($content as $item) {
            if (!$this->isAbsoluteUrl($item['image'])) {
                $item['image'] = $this->urlRoot() . '/' . $item['image'];
            }
            $items[] = $item;
        }

        //return json_encode($items);
        return $items;
    }

    /**
     * Canonize images
     *
     * @param array $content
     *
     * @return array
     */
    protected function canonizeImage(array $content)
    {
        $pathRoot = Pi::path('upload') . '/' . $this->getModule();
        $prefixLength = strlen($this->tmpPrefix);
        //$content = json_decode($content, true);
        //$content = $content;
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

        //return json_encode($items);
        return $items;
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
        $path = $this->pathRoot();
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
