<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Field controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class FieldController extends ActionController
{
    /**
     * Default page
     * 
     * @return 
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', array('action' => 'list'));
    }
    
    /**
     * List fields info
     */
    public function listAction()
    {
        $module    = $this->getModule();
        $fields    = Pi::registry('field', $module)->read();
        
        $compounds = array();
        foreach ($fields as $field) {
            if ('compound' !== $field['type']) {
                continue;
            }
            $name = $field['name'];
            $compounds[$name] = Pi::registry('compound_field', $module)->read($name);
        }
        
        $this->view()->assign(array(
            'fields'    => $fields,
            'compounds' => $compounds,
            'module'    => $this->getModule(),
        ));
    }
    
    /**
     * AJAX action, update fields or compound fields data.
     * 
     * @param `id`     Field or compound field id
     * @param `name`   Column name want to update
     * @param `type`   Field type, common or compound
     * @param `status` Value to set
     */
    public function updateAction()
    {
        Pi::service('log')->mute();
        
        $return = array('status' => false);
        
        // Get available columns to update
        $name   = $this->params('name', '');
        $status = (int) $this->params('status', null);
        if (empty($name) || null === $status) {
            $return['message'] = __('Name or its value is needed.');
            echo json_encode($return);
            exit;
        }
        $columns = array('is_required');
        if (!in_array($name, $columns)) {
            $return['message'] = sprintf(
                __('Name `%s` is not allowed to update.'),
                $name
            );
            echo json_encode($return);
            exit;
        }
        
        $id = $this->params('id', 0);
        if (empty($id)) {
            $return['message'] = __('Invalid ID.');
            echo json_encode($return);
            exit;
        }
        
        // Get model by type parameter
        $type = $this->params('type', 'common');
        if ('compound' === $type) {
            $model   = $this->getModel('compound_field');
        } else {
            $model   = $this->getModel('field');
        }
        
        $row = $model->find($id);
        if (!$row || ('common' === $type && !$row->is_edit)) {
            $return['message'] = __('Permission denied.');
            echo json_encode($return);
            exit;
        }
        
        $row->$name = $status;
        $row->save();
        
        $module = $this->getModule();
        Pi::registry('field', $module)->flush();
        Pi::registry('compound_field', $module)->flush();
        
        $return['status'] = (bool) $row->id;
        $return['data']   = $row->toArray();
        echo json_encode($return);
        exit;
    }
}
