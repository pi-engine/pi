<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Module\Article\Controller\Admin\SetupController as Setup;
use Zend\EventManager\Event;
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
        $events->attach(
            'install.post',
            array($this, 'initClonedBlocksPermission'),
            -120
        );
        $events->attach(
            'install.post',
            array($this, 'initCategoryPermission'),
            -200
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
            return true;
        }
        
        // Add a root category and its child category
        $module = $this->event->getParam('module');
        $model  = Pi::model('category', $module);
        $data   = array(
            'id'          => null,
            'name'        => 'root',
            'slug'        => 'root',
            'title'       => _a('Root'),
            'description' => _a('Module root category'),
        );
        $result = $model->add($data);
        $defaultCategory = array(
            'id'          => null,
            'name'        => 'default',
            'slug'        => 'slug',
            'title'       => _a('Default'),
            'description' => _a('The default category can not be delete, but can be modified!'),
        );
        $parent = $model->select(array('name' => 'root'))->current();
        $model->add($defaultCategory, $parent);
        
        return $result;
    }
    
    /**
     * Add a config file to initilize draft edit page form type as extended.
     * 
     * @param Event $e 
     */
    public function initDraftEditPageForm(Event $e)
    {
        $module = $this->event->getParam('module');
        $elements = array(
            'mode'  => 'extension',
        );
        
        $filename = Setup::getFilename(false, $module);
        $result   = Pi::config()->write($filename, $elements, true);
        
        return $result;
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
            Pi::path('upload'),
            $module
        );
        
        $result = true;
        if (!file_exists($destFilename)) {
            $result = Pi::service('file')->mkdir($destFilename);
        }
        
        // Copy screenshot into target folder
        if ($result) {
            chmod($destFilename, 0777);
            $config = Pi::config('', $module);
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
        
        return $result;
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
                    if (Pi::service('file')->mkdir($targetPath)) {
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
        
        return $result;
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
        
        return $result;
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
        
        return $result;
    }
    
    /**
     * Initilize cloned blocks permission
     * 
     * @param Event $e
     * @return boolean 
     */
    public function initClonedBlocksPermission(Event $e)
    {
        $module = $this->event->getParam('module');
        
        $rowset = Pi::model('block')->select(array(
            'module'    => $module,
            'cloned'    => 1,
        ));
        
        $tableName = Pi::model('permission_rule')->getTable();
        $sql = <<<EOD
INSERT INTO `{$tableName}` (`resource`, `module`, `section`, `role`) VALUES 

EOD;
        foreach ($rowset as $row) {
            $sql .=<<<VALUE
('block-{$row->id}', '{$module}', 'front', 'guest'),
('block-{$row->id}', '{$module}', 'front', 'member'),

VALUE;
        }
        $sql = rtrim(trim($sql), ',') . ';';
        
        // Insert data
        try {
            Pi::db()->getAdapter()->query($sql, 'execute');
        } catch (\Exception $exception) {
            return false;
        }
        
        return true;
    }
    
    public function initCategoryPermission(Event $e)
    {
        $module = $this->event->getParam('module');
        
        // Get permission table name
        $tableName = Pi::model('permission_rule')->getTable();
        $sql = <<<EOD
INSERT INTO `{$tableName}` (`resource`, `module`, `section`, `role`) VALUES 

EOD;

        // Get admin role name
        $roles = Pi::user()->getRole(1, 'admin');
        $role  = array_shift($roles);

        // Get all add categories
        $model = Pi::model('category', $module);
        $rowset = $model->select(array());
        foreach ($rowset as $row) {
            $sql .=<<<VALUE
('category-{$row->name}', '{$module}', 'admin', '{$role}'),

VALUE;
        }
        $sql = rtrim(trim($sql), ',') . ';';
        
        // Insert data
        try {
            Pi::db()->getAdapter()->query($sql, 'execute');
        } catch (\Exception $exception) {
            return false;
        }
        
        return true;
    }
}
