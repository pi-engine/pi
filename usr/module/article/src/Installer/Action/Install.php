<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Zend\EventManager\Event;
use Module\Article\Service;
use Module\Article\File;
use ZipArchive;

/**
 * Custom install class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Install extends BasicInstall
{
    /**
     * Sql file and static data for initilizing data 
     */
    const INIT_FILE_NAME   = 'article/data/data.sql';
    const INIT_STATIC_NAME = 'article/data/article.zip';
    const INIT_BLOCK_NAME  = 'article/data/blocks.sql';
    const PAGE_BLOCK_NAME  = 'article/data/page_block.sql';
    
    /**
     * Attach method to listener
     * 
     * @return \Module\Article\Installer\Action\Install 
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', array($this, 'initCategory'), 1);
        $events->attach(
            'install.post',
            array($this, 'initDraftEditPageForm'),
            -90
        );
        $events->attach(
            'install.post',
            array($this, 'initDefaultTopicTemplateScreenshot'),
            -90
        );
        $events->attach(
            'install.post',
            array($this, 'initModuleData'),
            -100
        );
        $events->attach(
            'install.post',
            array($this, 'initCloneBlocks'),
            -100
        );
        $events->attach(
            'install.post',
            array($this, 'dressupBlocks'),
            -110
        );
        parent::attachDefaultListeners();
        return $this;
    }
    
    /**
     * Add a root category, and its child as default category
     * 
     * @param Event $e 
     */
    public function initCategory(Event $e)
    {
        // Skip if the initial data is exists
        $sqlPath = sprintf('%s/%s', Pi::path('module'), self::INIT_FILE_NAME);
        if (file_exists($sqlPath)) {
            return $e->setParam('result', true);
        }
        
        // Add a root category and its child category
        $module = $this->event->getParam('module');
        $model  = Pi::model('category', $module);
        $data   = array(
            'id'          => null,
            'name'        => 'root',
            'slug'        => 'root',
            'title'       => __('Root'),
            'description' => __('Module root category'),
        );
        $result = $model->add($data);
        $defaultCategory = array(
            'id'          => null,
            'name'        => 'default',
            'slug'        => 'slug',
            'title'       => __('Default'),
            'description' => __('The default category can not be delete, but can be modified!'),
        );
        $parent = $model->select(array('name' => 'root'))->current();
        $itemId = $model->add($defaultCategory, $parent);
        
        $e->setParam('result', $result);
    }
    
    /**
     * Add a config file to initilize draft edit page form type as extended.
     * 
     * @param Event $e 
     */
    public function initDraftEditPageForm(Event $e)
    {
        $module = $this->event->getParam('module');
        $content  =<<<EOD
<?php
return array(
    'mode'     => 'extension',
);
EOD;
        $filename = Service::getModuleConfigPath('draft-edit-form', $module);
        $result   = File::addContent($filename, $content);
        
        $e->setParam('result', $result);
    }
    
    /**
     * Add a folder in static folder and copy the default topic template
     * screenshot into this folder.
     * 
     * @param Event $e 
     */
    public function initDefaultTopicTemplateScreenshot(Event $e)
    {
        $module = $this->event->getParam('module');
        
        // Create folder in static folder
        $destFilename = sprintf(
            '%s/%s/topic-template',
            Pi::path('static'),
            $module
        );
        
        $result = true;
        if (!file_exists($destFilename)) {
            $result = File::mkdir($destFilename);
        }
        
        // Copy screenshot into target folder
        if ($result) {
            chmod($destFilename, 0777);
            $config = Pi::service('module')->config('', $module);
            $basename = $config['default_topic_template_image'];
            $srcFilename = sprintf(
                '%s/article/asset/%s',
                Pi::path('module'),
                $basename
            );
            
            if (file_exists($srcFilename)) {
                $result = copy(
                    $srcFilename,
                    $destFilename . '/' . basename($basename)
                );
            }
        }
        
        $e->setParam('result', $result);
    }
    
    /**
     * Initize module data
     * 
     * @param Event $e
     * @return boolean 
     */
    public function initModuleData(Event $e)
    {
        $result = true;
        
        // Skip if the initial data is not exists
        $sqlPath = sprintf('%s/%s', Pi::path('module'), self::INIT_FILE_NAME);
        if (!file_exists($sqlPath)) {
            return $e->setParam('result', $result);
        }
        
        // Get module name and prefix of table
        $module = $this->event->getParam('module');
        $prefix = Pi::db()->getTablePrefix();
        
        // Fetch data and insert into database
        $file = fopen($sqlPath, 'r');
        if ($file) {
            $sql = fread($file, filesize($sqlPath));
            $sql = preg_replace('|{prefix}|', $prefix, $sql);
            $sql = preg_replace('|{module}|', $module, $sql);
            $sql = preg_replace('|{upload-url}|', Pi::url('upload/' . $module), $sql);
            $sql = preg_replace('|upload\/article|', 'upload\/' . $module, $sql);

            try {
                $isInsert = Pi::db()->getAdapter()->query($sql, 'execute');
            } catch (\Exception $exception) {
                return false;
            }
            
            // Copy uploaded data
            $staticFilename = sprintf(
                '%s/%s',
                Pi::path('module'),
                self::INIT_STATIC_NAME
            );

            if (file_exists($staticFilename)) {
                $targetFilename = sprintf(
                    '%s/%s/%s',
                    Pi::path('upload'),
                    $module,
                    basename($staticFilename)
                );
                // Create folder
                $targetPath = dirname($targetFilename);
                if (!is_dir($targetPath)) {
                    if (File::mkdir($targetPath)) {
                        chmod($targetPath, 0777);
                    }
                }
                
                // Copy data and decompression
                $isCorrect = copy(
                    $staticFilename,
                    $targetFilename
                );
                if ($isCorrect) {
                    $zip = new ZipArchive;
                    if ($zip->open($targetFilename) === TRUE) {
                        $zip->extractTo(dirname($targetFilename));
                        $zip->close();
                        @unlink($targetFilename);
                    }
                }
            }
        } else {
            $result = false;
        }
        
        $e->setParam('result', $result);
    }
    
    /**
     * Init clone blocks
     * 
     * @param Event $e 
     * @return boolean
     */
    public function initCloneBlocks(Event $e)
    {
        $result = true;
        $module = $this->event->getParam('module');
        
        $filename = sprintf('%s/%s', Pi::path('module'), self::INIT_BLOCK_NAME);
        if (file_exists($filename)) {
            $file = fopen($filename, 'r');
            if ($file) {
                $prefix = Pi::db()->getTablePrefix();
                
                $sql = fread($file, filesize($filename));
                $sql = preg_replace('|{prefix}|', $prefix, $sql);
                $sql = preg_replace('|{module}|', $module, $sql);
                
                // Get root block name
                preg_match_all('|{{{{([a-zA-Z_-]+)}}}}|', $sql, $matches);
                
                // Get root block ID
                $rowBlock = Pi::model('block_root')->select(array(
                    'module'    => 'article',
                    'name'      => $matches[1],
                ));
                foreach ($rowBlock as $row) {
                    $sql = preg_replace(
                        '|{{{{' . $row->name . '}}}}|', 
                        $row->id,
                        $sql
                    );
                }
                
                // Insert data
                try {
                    Pi::db()->getAdapter()->query($sql, 'execute');
                } catch (\Exception $exception) {
                    return false;
                }
            }
        }
        
        $e->setParam('result', $result);
    }
    
    /**
     * Dress up blocks
     * 
     * @param Events $e
     * @return boolean 
     */
    public function dressupBlocks(Event $e)
    {
        $result = true;
        $module = $this->event->getParam('module');
        
        $filename = sprintf('%s/%s', Pi::path('module'), self::PAGE_BLOCK_NAME);
        if (file_exists($filename)) {
            $file = fopen($filename, 'r');
            if ($file) {
                $prefix = Pi::db()->getTablePrefix();
                
                $sql = fread($file, filesize($filename));
                $sql = preg_replace('|{prefix}|', $prefix, $sql);
                $sql = preg_replace('|{module}|', $module, $sql);
                
                // Get page controller & action
                preg_match_all('|####([a-zA-Z_-]+)####|', $sql, $pages);
                $pageName = array_unique($pages[1]);
                
                // Get page ID
                $rowPage = Pi::model('page')->select(array(
                    'section'   => 'front',
                    'module'    => $module,
                ));
                foreach ($rowPage as $row) {
                    $name = $row->controller . '-' . $row->action;
                    if (in_array($name, $pageName)) {
                        $sql = preg_replace(
                            '|####' . $name . '####|',
                            $row->id,
                            $sql
                        );
                    }
                }
                
                // Get root block name
                preg_match_all('|{{{{([a-zA-Z_-]+)}}}}|', $sql, $matches);
                $blockName = $matches[1];
                
                // Get block ID
                $rowBlock = Pi::model('block')->select(array(
                    'module'    => $module,
                    'name'      => $blockName,
                ));
                foreach ($rowBlock as $row) {
                    $sql = preg_replace(
                        '|{{{{' . $row->name . '}}}}|', 
                        $row->id,
                        $sql
                    );
                }
                
                // Insert data
                try {
                    Pi::db()->getAdapter()->query($sql, 'execute');
                } catch (\Exception $exception) {
                    return false;
                }
            }
        }
        
        $e->setParam('result', $result);
    }
}
