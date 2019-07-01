<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Sitemap\Lib;

use Pi;

class Generate
{
    protected $name = 'sitemap.xml';
    protected $limit = 5000;
    protected $start = '';
    protected $end = '';

    /**
     * Constructor
     * @param  string $name
     * @param  int    $start
     * @param  int    $end
     */
    public function __construct($name, $start = '', $end = '')
    {
    	$config = Pi::service('registry')->config->read('sitemap', 'sitemap');
        $this->name = $name;
        $this->start = ($this->name == 'sitemap.xml') ? 0 : $start;
        $this->end = ($this->name == 'sitemap.xml') ? 0 : $end;
        $this->limit = intval($config['sitemap_limit']);
    }

    /**
     * Get sitemap content
     * @return array
     */
    public function content()
    {
        $this->content = array();
        // Set index and top url
        if ($this->name == 'sitemap.xml') {     
            $this->content = $this->indexUrl($this->content);
            $this->content = $this->topUrl($this->content);
        }
        // Set list url
        $this->content = $this->listUrl($this->content);
        // Return content array
        return $this->content;
    }	

    /**
     * write on XML file
     * @param array
     */
    public function write($xml)
    {
        Pi::service('file')->mkdir(Pi::path('upload/sitemap'));
        // Set file path
        $sitemap = Pi::path(sprintf('upload/sitemap/%s', $this->name));
        // Remove old file
        if (Pi::service('file')->exists($sitemap)) {
            Pi::service('file')->remove($sitemap);
        }
        // write to file
        $file = fopen($sitemap, "x+");
        fwrite($file, $xml);
        fclose($file);
        // Save generate
        $this->canonizeGenerate();
    }

    /**
     * Add website index URl on content array
     * @param array
     * @return array
     */
    public function indexUrl($content)
    {
        $content[0] = array(
            'uri' => Pi::url('www'),
            'lastmod' => date("Y-m-d H:i:s"),
            'changefreq' => 'daily',
            'priority' => '',
        );
        return $content;
    }

    /**
     * Add top links from url_list table on content array
     * @param array
     * @return array
     */
    public function topUrl($content)
    {
        $where = array('top' => 1);
        $order = array('id DESC', 'time_create DESC');
        $select = Pi::model('url_list', 'sitemap')->select()->where($where)->order($order);
        $rowset = Pi::model('url_list', 'sitemap')->selectWith($select);
        foreach ($rowset as $row) {
            $link['uri']         = $row->loc;
            $link['lastmod']     = $row->lastmod;
            $link['changefreq']  = $row->changefreq;
            $link['priority']    = $row->priority;
            $content[$row->id]   = $link;
        }
        return $content;
    }

    /**
     * Add links from url_list table on content array
     * @param array
     * @return array
     */
    public function listUrl($content)
    {
        // Set info
        $order = array('id DESC', 'time_create DESC');
        $limit = intval($this->limit - count($content)); 
        // Set start and end
        if (!empty($this->start) && !empty($this->end) && ($this->end > $this->start)) {
            $where = array(
                'status'   => 1,
                'top'      => 0,
                'id < ?'   => $this->end,
                'id >= ?'  => $this->start,
            );
        } else {    
            $where = array('status' => 1, 'top' => 0);
        }
        $select = Pi::model('url_list', 'sitemap')->select()->where($where)->order($order)->limit($limit);
        $rowset = Pi::model('url_list', 'sitemap')->selectWith($select);
        foreach ($rowset as $row) {
            $link['uri']         = $row->loc;
            $link['lastmod']     = $row->lastmod;
            $link['changefreq']  = $row->changefreq;
            $link['priority']    = $row->priority;
            $content[$row->id]   = $link;
        }
        return $content;
    }

    /**
     * canonize generate table
     */
    public function canonizeGenerate()
    {
        $row = Pi::model('generate', 'sitemap')->find($this->name, 'file');
        if ($row) {
            $row->file = $this->name;
            $row->start = $this->start;
            $row->end = $this->end;
            $row->time_update = time();
            $row->save();
        } else {
            $row = Pi::model('generate', 'sitemap')->createRow();
            $row->file = $this->name;
            $row->start = $this->start;
            $row->end = $this->end;
            $row->time_create = time();
            $row->time_update = time();
            $row->assign($values);
            $row->save();
        }
    }
}	