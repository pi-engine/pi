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
use Pi\File\Transfer\Upload;

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
    protected $tmpPrefix = 'widget.';

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
    protected function updateWidget($id, array $block)
    {
        $row = $this->getModel('widget')->find($id);
        if (!$row) {
            $result = 0;
        } else {
            $items = $row->meta ? json_decode($row->meta, true) : array();
            $itemsNew = $block['content'] ? json_decode($block['content'], true) : array();
            $result = parent::updateWidget($id, $block);
            if ($result) {
                $images     = array();
                $imagesNew  = array();
                foreach ($items as $item) {
                    $images[] = $item['image'];
                }
                foreach ($itemsNew as $item) {
                    $imagesNew[] = $item['image'];
                }
                $imageList = array_diff($images, $imagesNew);
                $this->deleteImages($imageList);
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function deleteWidget($id)
    {
        $row = $this->getModel('widget')->find($id);
        if (!$row) {
            $result = 0;
        } else {
            $items = $row->meta ? json_decode($row->meta, true) : array();
            foreach ($items as $item) {
                $images[] = $item['image'];
            }
            $result = parent::deleteWidget($id);
            if ($result) {
                $this->deleteImages($images);
            }
        }

        return $result;
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
            $return['image'] = $this->urlRoot() . '/' . $file;
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
     * Delete image files
     *
     * @param array $images
     *
     * @return void
     */
    protected function deleteImages(array $images)
    {
        $path   = $this->pathRoot();
        $url    = $this->urlRoot();
        foreach ($images as $image) {
            $file = preg_replace('|^' . $url . '|', $path, $image);
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
