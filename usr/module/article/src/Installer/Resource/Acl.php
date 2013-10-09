<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Installer\Resource;

use Pi\Application\Installer\Resource\Acl as BasicAcl;
use Pi;

/**
 * Custom acl resource install class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Acl extends BasicAcl
{
    /**
     * Uninstall acl resource
     * 
     * @return bool 
     */
    public function uninstallAction()
    {
        $roles = array('article-manager', 'contributor');
        Pi::model('acl_inherit')->delete(array('child' => $roles));
        Pi::model('acl_inherit')->delete(array('parent' => $roles));
        
        return parent::uninstallAction();
    }
}
