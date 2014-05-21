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
class MediaController extends ListController
{
    /**
     * {@inheritDoc}
     */
    protected $type = 'media';

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
    protected function rootUrl()
    {
        return Pi::url('upload') . '/' . $this->getModule();
    }

    /**
     * Get root path for upload
     *
     * @return string
     */
    protected function rootPath()
    {
        return Pi::path('upload') . '/' . $this->getModule();
    }

    /**
     * Get tmp upload URL
     *
     * @return string
     */
    protected function tmpUrl()
    {
        return Pi::url('upload/_tmp');
    }

    /**
     * Get tmp upload path
     *
     * @return string
     */
    protected function tmpPath()
    {
        return Pi::path('upload/_tmp');
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
        $destination    = $this->tmpPath();
        $uploadUrl      = $this->tmpUrl();

        $config = $this->config();
        if ($config['image_extension']) {
            $exts = explode(',', $config['image_extension']);
            $exts = array_filter(array_walk($exts, 'trim'));
            $extensions = implode(',', $exts);
        }
        $extensions     = $extensions ?: 'jpg,png,gif';
        $maxFile        = (int) $config['file_max_size']  * 1024;
        $maxSize        = array();
        if ($config['image_max_width']) {
            $maxSize['width'] = (int) $config['image_max_width'];
        }
        if ($config['image_max_height']) {
            $maxSize['height'] = (int) $config['image_max_height'];
        }

        $uploader = new Upload(array('rename' => $rename));
        $uploader->setDestination($destination)->setExtension($extensions);
        if ($maxFile) {
            $uploader->setSize($maxFile);
        }
        if ($maxSize) {
            $uploader->setImageSize($maxSize);
        }
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
        $rootPath   = $this->rootPath();
        $rootUrl    = $this->rootUrl();
        $uploadPath = $this->tmpPath();
        $uploadUrl  = $this->tmpUrl() . '/';
        $prefixLen  = strlen($uploadUrl);

        $items = array();
        foreach ($list as $item) {
            if ($uploadUrl == substr($item['image'], 0, $prefixLen)) {
                $imgName = substr($item['image'], $prefixLen);
                $renamed = rename(
                    $uploadPath . '/' . $imgName,
                    $rootPath . '/' . $imgName
                );
                if ($renamed) {
                    $item['image'] = $rootUrl . '/' . $imgName;
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
        $path   = $this->rootPath();
        $url    = $this->rootUrl();
        foreach ($images as $image) {
            $file = preg_replace('|^' . $url . '|', $path, $image);
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
