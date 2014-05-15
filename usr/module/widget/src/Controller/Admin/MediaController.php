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

    /** @var string Temp dir for image uploads */
    protected $tmpDir = 'tmp';

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
        $result = 0;
        $row = $this->getModel('widget')->find($id);
        if (!$row) {
            return $result;
        }

        $items      = $row->meta ? json_decode($row->meta, true) : array();
        $itemsNew   = json_decode($block['content'], true);
        $result     = parent::updateWidget($id, $block);
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
     * Upload image files
     *
     * Store files to a tmp dir and will be moved to regular dir on submission
     */
    public function uploadAction()
    {
        $return = array(
            'status'    => 1,
            'message'   => '',
            'image'     => '',
        );
        $rename         = '%random%';
        $extensions     = 'jpg,png,gif';
        $destination    = $this->pathRoot() . '/' . $this->tmpDir;
        $uploadUrl      = $this->urlRoot() . '/' . $this->tmpDir;

        $uploader = new Upload(array('rename' => $rename));
        $uploader->setDestination($destination)->setExtension($extensions);
        if ($uploader->isValid()) {
            $uploader->receive();
            $file = $uploader->getUploaded('image');
            $return['image'] = $uploadUrl . '/' . $file;
        } else {
            $messages = $uploader->getMessages();
            $return = array(
                'status'    => 0,
                'image'     => '',
                'message'   => implode('; ', $messages),
            );
        }

        return $return;
    }

    /**
     * {@inheritDoc}
     *
     * Move uploaded images from tmp dir to regular dir
     */
    protected function canonizePost(array $values)
    {
        $content = json_decode($values['content'], true);
        $items = $this->moveImages($content);
        $values['content'] = json_encode($items);
        $values = parent::canonizePost($values);

        return $values;

    }

    /**
     * Move uploaded image files from tmp dir to regular dir
     *
     * @param array $list
     *
     * @return array
     */
    protected function moveImages(array $list)
    {
        $pathRoot   = $this->pathRoot();
        $urlRoot    = $this->urlRoot();
        $pathUpload = $this->pathRoot() . '/' . $this->tmpDir;
        $urlUpload  = $this->urlRoot() . '/' . $this->tmpDir . '/';
        $prefixLen  = strlen($urlUpload);

        $items = array();
        foreach ($list as $item) {
            if ($urlUpload == substr($item['image'], 0, $prefixLen)) {
                $imgName = substr($item['image'], $prefixLen);
                $renamed = rename(
                    $pathUpload . '/' . $imgName,
                    $pathRoot . '/' . $imgName
                );
                if ($renamed) {
                    $item['image'] = $urlRoot . '/' . $imgName;
                }
            }
            $items[] = $item;
        }

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
