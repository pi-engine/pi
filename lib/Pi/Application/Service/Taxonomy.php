<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Application\Model\Nest as Model;

/**
 * Taxonomy service
 *
 * <code>
 *  // Taxon model
 *  Pi::service('taxonomy')->get($domainName = null);
 *
 *  Pi::service('taxonomy')->truncate($domainName = null);
 *
 *  Pi::service('taxonomy')->update($taxonData, $domainName = null);
 *
 *  Pi::service('taxonomy')->delete($domainName);
 *  Pi::service('taxonomy')->add($taxonData, $domainName = null);
 *
 *  Pi::service('taxonomy')->getTree($domainName = null, $cols = array());
 *  Pi::service('taxonomy')->getList($domainName = null, $cols = array());
 *
 *  // Domain model
 *  Pi::service('taxonomy')->addDomain($domainData, $taxonData = null);
 *
 *  Pi::service('taxonomy')->updateDomain($domainData, $taxonData = null);
 *
 *  Pi::service('taxonomy')->getDomain($domainName);
 *  Pi::service('taxonomy')->getDomain($domainId);
 *
 *  Pi::service('taxonomy')->deleteDomain($domainName, $deleteTaxa = true);
 *  Pi::service('taxonomy')->deleteDomain($domainId, $deleteTaxa = true);
 * </code>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Taxonomy extends AbstractService
{
    /** @var string Default domain */
    protected $defaultDomain = 'taxon';

    /** @var string[] Taxon columns */
    protected $columnsTaxon = array('name', 'title', 'description');

    /**#@+
     * Taxon APIs
     */
    /**
     * Canonize taxon data
     *
     * @param array $data
     * @return void
     * @see Pi\Db\Table\AbstractNest::convertFromNested()
     */
    protected function canonizeTaxon(&$data)
    {
        $columns = $this->columnsTaxon;

        foreach ($data as &$taxon) {
            foreach ($taxon as $key => $val) {
                if ('child' == $key) {
                    $child = $taxon['child'];
                    $this->canonizeTaxon($child);
                    $taxon['child'] = $child;
                } elseif (!in_array($key, $columns)) {
                    unset($taxon[$key]);
                }
            }
        }
    }

    /**
     * Create taxon data table for a damon and return corresponding model
     *
     * @param string $name Domain name
     * @return Model|false
     */
    protected function createModel($name)
    {
        $modelName = sprintf('taxonomy_%s', $name);
        $tableNew = Pi::db()->prefix($modelName);
        $tableOriginal = Pi::db()->prefix('taxonomy_taxon');

        $sql = sprintf('CREATE TABLE %s LIKE %s', $tableNew, $tableOriginal);
        try {
            Pi::db()->getAdapter()->query($sql, 'execute');
        } catch (\Exception $exception) {
            return false;
        }

        $model = Pi::db()->model($modelName, array('type' => 'nest'));

        return $model;
    }

    /**
     * Delete taxon data table for a damon
     *
     * @param string $name Domain name
     * @return bool
     */
    protected function deleteModel($name)
    {
        if ($name == $this->defaultDomain) {
            throw new \Exception('System taxonomy is not allowed to delete.');
        }

        $modelName = sprintf('taxonomy_%s', $name);
        $model = Pi::db()->model($modelName, array('type' => 'nest'));
        if (!$model) {
            return false;
        }
        try {
            $sql = sprintf('DROP TABLE IF EXISTS %s', $model->getTable());
            Pi::db()->getAdapter()->query($sql, 'execute');
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * Get taxon data model of a domain
     *
     * @param string $name Domain name
     * @return Model|false
     */
    protected function getModel($name)
    {
        $modelName = sprintf('taxonomy_%s', $name);
        $model = Pi::db()->model($modelName, array('type' => 'nest'));

        return $model;
    }

    /**
     * Add taxon data to a taxonomy domain
     *
     * @param array $taxonData Nested taxon data =>
     *                  string: name            Domain name, required;
     *                  string: title           Domain title, optional;
     *                  string: description     Domain description, optional.
     * @param string $domainName
     * @return bool
     * @see Pi\Db\Table\AbstractNest::convertFromNested()
     */
    public function add($taxonData, $domainName = null)
    {
        $status = false;
        $domain = $domainName ?: $this->defaultDomain;
        $model = $this->createModel($domain);
        if ($model) {
            $this->canonizeTaxon($taxonData);
            $status = $model->graft($taxonData);
        }

        return $status;
    }

    /**
     * Get taxon data model of a taxonomy domain
     *
     * @param string $domainName
     * @return Model|false
     */
    public function get($domainName = 'taxon')
    {
        $domain = $domainName ?: $this->defaultDomain;
        $model = $this->getModel($domain);

        return $model;
    }

    /**
     * Empty taxon data model of a taxonomy domain
     *
     * @param string $domainName
     * @return bool
     */
    public function truncate($domainName = null)
    {
        $domain = $domainName ?: $this->defaultDomain;
        $model = $this->getModel($domain);
        if ($model) {
            $model->delete(array());
            return true;
        }

        return false;
    }

    /**
     * Delete taxon data table of a taxonomy domain
     *
     * @param string $domainName
     * @return bool
     */
    public function delete($domainName = null)
    {
        $domain = $domainName ?: $this->defaultDomain;
        $status = $this->deleteModel($domain);

        return $status;
    }

    /**
     * Update taxa data for a domain
     *
     * @param array $taxonData Nested taxon data =>
     *                  string: name            Domain name, required;
     *                  string: title           Domain title, optional;
     *                  string: description     Domain description, optional.
     * @param string $domainName
     * @return bool
     * @see Pi\Db\Table\AbstractNest::convertFromNested()
     */
    public function update($taxonData, $domainName = null)
    {
        $status = false;
        $domain = $domainName ?: $this->defaultDomain;
        $model = $this->getModel($domain);
        if ($model) {
            $this->truncate($domain);
            $this->canonizeTaxon($taxonData);
            $status = $model->graft($taxonData);
        }

        return $status;
    }

    /**
     * Get nested taxon data of a taxonomy domain
     *
     * @param string $domainName
     * @param array  $cols Fields to fetch
     * @return array|bool
     */
    public function getTree($domainName = null, $cols = array())
    {
        $data = false;
        $domain = $domainName ?: $this->defaultDomain;
        $model = $this->getModel($domain);
        if ($model) {
            if (!$cols) {
                $cols = $this->columnsTaxon;
                array_unshift($cols, 'id');
            }
            $data = $model->enumerate(null, $cols) ?: array();
        }

        return $data;
    }

    /**
     * Get adjacency list of taxon data of a taxonomy domain
     *
     * @param string $domainName
     * @param array  $cols Fields to fetch
     * @return array|bool
     */
    public function getList($domainName = null, $cols = array())
    {
        $list = false;
        $data = $this->getTree($domainName, $cols);
        if (false !== $data) {
            $transform = function (&$node, &$plainList, $pid, &$transform) {
                $id = $node['id'];
                $node['pid']    = $pid;
                $plainList[$id] = $node;
                if (isset($node['child'])) {
                    unset($plainList[$id]['child']);
                    foreach ($node['child'] as $cid => &$page) {
                        $transform($page, $plainList, $id);
                    }
                }
            };
            $list = array();
            $transform($data, $list, 0);
        }

        return $list;
    }
    /**#@-*/

    /**#@+
     * Domain APIs
     */
    /**
     * Canonize domain data
     *
     * @param array $data
     * @return array
     */
    protected function canonizeDomain($data)
    {
        $columns = array('name', 'title', 'description', 'module');

        $result = array();
        foreach ($data as $key => $val) {
            if (in_array($key, $columns)) {
                $result[$key] = $val;
            }
        }

        return $result;
    }

    /**
     * Add a taxonomy domain, and add its taxa data if provided
     *
     * @param array $domainData =>
     *                  string: name            Domain name, required;
     *                  string: title           Domain title, optional;
     *                  string: description     Domain description, optional;
     *                  string: module          Module name, optional.
     * @param array|false $taxonData
     * @return int Created ID
     */
    public function addDomain($domainData, $taxonData = array())
    {
        $data = $this->canonizeDomain($domainData);
        $row = Pi::model('taxonomy_domain')->createRow($data);
        $row->save();

        if (false !== $taxonData) {
            $this->add((array) $taxonData, $row->name);
        }

        return $row->id;
    }

    /**
     * Update a taxonomy domain, and update its taxa data if provided
     *
     * @param array $domainData =>
     *                  int:    id              Domain id, optional;
     *                  string: name            Domain name, required;
     *                  string: title           Domain title, optional;
     *                  string: description     Domain description, optional;
     *                  string: module          Module name, optional.
     * @param array|bool $taxonData
     * @return int Created ID
     */
    public function updateDomain($domainData, $taxonData = false)
    {
        if (isset($domainData['id'])) {
            $id = $domainData['id'];
            $row = Pi::model('taxonomy_domain')->find($id);
        } else {
            $row = Pi::model('taxonomy_domain')->find(
                $domainData['name'],
                'name'
            );
        }
        $data = $this->canonizeDomain($domainData);
        $row->assign($data);
        $row->save();

        if (false !== $taxonData) {
            $this->update((array) $taxonData, $row->name);
        }

        return $row->id;
    }

    /**
     * Get a taxonomy domain
     *
     * @param int|string $entity
     * @return array|bool
     */
    public function getDomain($entity)
    {
        if (is_int($entity)) {
            $row = Pi::model('taxonomy_domain')->find($entity);
        } else {
            $row = Pi::model('taxonomy_domain')->find($entity, 'name');
        }

        return $row ? $row->toArray() : false;
    }

    /**
     * Delete a taxonomy domain, and delete its taxa if required
     *
     * @param int|string $entity
     * @param bool       $deleteTaxa
     *
     * @throws \Exception
     * @return bool
     */
    public function deleteDomain($entity, $deleteTaxa = true)
    {
        if ($entity === $this->defaultDomain || $entity === 1) {
            throw new \Exception('System taxonomy is not allowed to delete.');
        }

        $status = true;
        if (is_int($entity)) {
            $row = Pi::model('taxonomy_domain')->find($entity);
        } else {
            $row = Pi::model('taxonomy_domain')->find($entity, 'name');
        }
        if (!$row) {
            return false;
        }
        $row->delete();

        if ($deleteTaxa) {
            $status = $this->delete($row->name);
        }

        return $status;
    }
    /**#@-*/
}
