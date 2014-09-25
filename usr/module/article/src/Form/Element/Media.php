<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Element;

use Pi;
use Zend\Form\Element\Hidden;

/**
 * Media element class
 * 
 * <Options>
 * `medias`:     array, media items, such fields must provided - `id`, `url`, `title`
 * `preview`:    array, preview media size, include fields `width` and `height`
 * `size`:       array, allowed size of uploaded media, include fields `width` and `height`
 * `type`:       string, which media want to upload, 'image' or 'attachment' or '' (all types)
 * `multiple`:   bool, whether to allowed to upload more than one media, default as false
 * `to_session`: bool, whether to store media in session or media section, default as false
 * `urls`:       array, @see description of $urls variable
 * 
 * <Attributes>
 * `id`: unique id, use for distinquish these templates that included multi in same page
 * `value`: media IDs combine by ','
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Media extends Hidden
{
    /**
     * Custom element attributes
     * 
     * @var array 
     */
    protected $attributes = array(
        'type'  => 'Module\Article\Form\View\Helper\Media',
    );
    
    /**
     * Ajax URLs for upload, save, search and remove media, fields:
     * - `search`: search media from media section
     * - `upload`: upload media to media section
     * - `remove`: remove media from media section
     * - `save`  : save media into media section
     * @var type 
     */
    protected $urls = array();
    
    /**
     * Set AJAX url for operating media, set default url if current url is empty
     * 
     * @param array $data
     */
    public function setAjaxUrls($data = array())
    {
        $options = $this->getOptions();
        $data    = array_merge($this->urls, (array) $data);
        $type    = isset($options['type']) ? $options['type'] : '';
        $params  = $type ? array('type' => $type) : array();
        
        if (!isset($data['search']) || empty($data['search'])) {
            $data['search'] = Pi::service('url')->assemble(
                '', 
                array_merge(array(
                    'controller' => 'media',
                    'action'     => 'search',
                ), $params)
            );
        }
        
        if (!isset($data['upload']) || empty($data['upload'])) {
            $data['upload'] = Pi::service('url')->assemble(
                '',
                array_merge(array(
                    'controller' => 'media',
                    'action'     => 'upload',
                    'width'      => isset($options['size']['width']) ? $options['size']['width'] : 0,
                    'height'     => isset($options['size']['height']) ? $options['size']['height'] : 0,
                ), $params)
            );
        }
        
        if (!isset($data['remove']) || empty($data['remove'])) {
            $data['remove'] = Pi::service('url')->assemble(
                '',
                array(
                    'controller' => 'media',
                    'action'     => 'remove',
                )
            );
        }
        
        if (!isset($data['save']) || empty($data['save'])) {
            $data['save'] = Pi::service('url')->assemble(
                '',
                array(
                    'controller' => 'media',
                    'action'     => 'save',
                )
            );
        }
        
        $this->urls = $data;
    }
    
    /**
     * Get AJAX url for operating media
     * 
     * @return array
     */
    public function getAjaxUrls()
    {
        if (empty($this->urls)) {
            $urls = $this->getOption('urls');
            $this->setAjaxUrls((array) $urls);
        }
        
        return $this->urls;
    }
}
