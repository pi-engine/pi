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
namespace Module\Sitemap\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Pi\Application\Installer\SqlSchema;
use Zend\EventManager\Event;

class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        parent::attachDefaultListeners();
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function updateSchema(Event $e)
    {
        $moduleVersion  = $e->getParam('version');
        
        // Set url_list model
        $listModel    = Pi::model('url_list', $this->module);
        $listTable    = $listModel->getTable();
        $listAdapter  = $listModel->getAdapter();

        // Update to version 1.2.0
        if (version_compare($moduleVersion, '1.2.0', '<')) {

        	// Set url_top model
        	$topModel    = Pi::model('url_top', $this->module);
        	$topTable    = $topModel->getTable();
        	$topAdapter  = $topModel->getAdapter();

        	// Alter table drop index `loc_unique`
        	$sql = sprintf("ALTER TABLE %s DROP INDEX loc_unique;", 
        		$listTable);
            try {
                $listAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                                   . $exception->getMessage(),
                ));
                return false;
            }

            // Alter table add field `top`
        	$sql = sprintf("ALTER TABLE %s ADD `top` tinyint(1) unsigned NOT NULL default '0' , ADD INDEX (`top`) ;", 
        		$listTable);
            try {
                $listAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                                   . $exception->getMessage(),
                ));
                return false;
            }

            // insert information from url_top to url_list
            $select = $topModel->select();
            $rowset = $topModel->selectWith($select);
            foreach ($rowset as $row) {
            	// Add link
        		$listData = array(
            		'loc'          => $row->loc,
            		'lastmod'      => $row->lastmod,
            		'changefreq'   => $row->changefreq,
            		'priority'     => $row->priority,
            		'time_create'  => $row->time_create,
            		'module'       => '',
            		'table'        => '',
            		'item'         => '',
            		'status'       => 1,
            		'top'          => 1,
        		);
        		$listModel->insert($listData);
            }

            // Drop not used `url_top` table
            try {
                $sql = sprintf('DROP TABLE IF EXISTS %s',
                               $topTable);
                $topAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table drop failed: '
                                   . $exception->getMessage(),
                ));
                return false;
            }
        }
    }
}    