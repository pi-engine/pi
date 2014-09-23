<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Module\Article\Form\View\Helper;

use Zend\Form\ElementInterface;
use Pi;

/**
 * Media element helper
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Media extends AbstractCustomHelper
{
    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element)
    {
        $this->view->css(array(
            Pi::service('asset')->getModuleAsset('css/form.media.css'),
        ));
        
        $required   = $element->getAttribute('required');
        $options    = $element->getOptions();
        
        $preview = isset($options['preview']) ? $options['preview'] : array();
        if (!isset($preview['width']) || empty($preview['width'])) {
            $preview['width'] = 100;
        }
        if (!isset($preview['height']) || empty($preview['height'])) {
            $preview['height'] = 100;
        }
        
        $module = isset($options['module']) ? $options['module']
            : Pi::service('module')->current();
        $config = Pi::config('', $module);
        $size = isset($options['size']) ? $options['size'] : array();
        if (!isset($size['width']) || empty($size['width'])) {
            $size['width'] = $config['image_width'];
        }
        if (!isset($size['height']) || empty($size['height'])) {
            $size['height'] = $config['image_height'];
        }
        
        // Resolving extension
        $type   = $element->getOption('type') ?: '';
        $mediaExts = explode(',', $config['image_extension']);
        $allExts   = explode(',', $config['media_extension']);
        array_walk($mediaExts, function(&$val) {
            $val = strtolower(trim($val));
        });
        array_walk($allExts, function(&$val) {
            $val = strtolower(trim($val));
        });
        if ('image' === $type) {
            $types = $mediaExts;
        } elseif ('attachment' === $type) {
            $types = array_diff($allExts, $mediaExts);
        } else {
            $types = $allExts;
        }
        $extension = implode(',', $types);
        $filesize  = $config['max_media_size'];
        
        $this->assign(array(
            'preview'    => $preview,
            'size'       => $size,
            'id'         => $element->getAttribute('id'),
            'urls'       => $element->getAjaxUrls(),
            'extension'  => $extension,
            'filesize'   => $filesize,
            'multiple'   => isset($options['multiple']) ? (bool) $options['multiple'] : false,
            'to_session' => isset($options['to_session']) ? (bool) $options['to_session'] : false,
            'type'       => $type,
            'medias'     => isset($options['medias']) ? $options['medias'] : array(),
        ));

        return $this->getTemplate($element, 'media');
    }
}
