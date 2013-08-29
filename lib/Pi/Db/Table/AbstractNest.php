<?PHP
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Db\Table;

use Pi\Db\RowGateway\Node;
use Pi\Db\Sql\Where;
use Zend\Db\ResultSet\Row;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

/**
 * Pi Nested Table Gateway
 *
 * Managing Hierarchical Data with Nested Set Model
 *
 * @see http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
 * @see http://web.archive.org/web/20110606032941/http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractNest extends AbstractTableGateway
{
    /**
     * Predefined columns
     *  - left: left value
     *  - right: right value
     *  - depth: node depth
     * @var array
     */
    protected $column = array(
        //'id'    => 'id',
        'left'  => 'left',
        'right' => 'right',
        'depth' => 'depth',
    );

    /**
     * Valid positions
     * @var string[]
     */
    protected $position = array('firstOf', 'lastOf', 'nextTo', 'previousTo');

    /**
     * Class for row gateway
     * @var string
     */
    protected $rowClass = 'Pi\Db\RowGateway\Node';

    /**
     * {@inheritDoc}
     */
    public function setup($options = array())
    {
        foreach (array_keys($this->column) as $key) {
            if (isset($options[$key])) {
                $this->column[$key] = (string) $options[$key];
                unset($options[$key]);
            }
        }
        parent::setup($options);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        if ($this->isInitialized == true) {
            return;
        }
        parent::initialize();

        $rowObject = $this->resultSetPrototype->getArrayObjectPrototype();
        if (is_callable(array($rowObject, 'setTableGateway'))) {
            $rowObject->setTableGateway($this);
        }
    }

    /**
     * Get column name
     *
     * @param string $column
     * @return string|null
     */
    public function column($column)
    {
        return isset($this->column[$column]) ? $this->column[$column] : null;
    }

    /**
     * Get quoted column name
     *
     * @param string $column
     * @return string|null
     */
    public function quoteColumn($column)
    {
        return $this->quoteIdentifier($this->column[$column]);
    }

    /**#@+
     * Node operations
     */
    /**
     * Normalizes node value
     *
     * @param Node|Row|int $node
     * @return Node|Row
     */
    protected function normalizeNode($node)
    {
        if ($node instanceof Node || $node instanceof Row) {
            $result = $node;
        } else {
            $result = $this->find($node);
        }

        return $result;
    }

    /**
     * Get extreme value of left or right
     *
     * @param string    $side       Node side, default as 'right'
     * @param mixed     $objective  Target node ID or Node
     * @return int
     */
    public function getSideExtreme($side = 'right', $objective = null)
    {
        $result = null;
        $side = ($side == 'left') ? 'left' : 'right';
        if ($objective) {
            $row = $this->normalizeNode($objective);
            $result = $row->$side;
            return $result;
        }

        if ($side == 'left') {
            $column = 'left';
            $operation = 'MIN';
        } else {
            $column = 'right';
            $operation = 'MAX';
        }
        $select = $this->select()->columns(array(
            'extreme' => new Expression(
                sprintf('%s(%s)', $operation, $this->quoteColumn($column))
            )
        ));
        $row = $this->selectWith($select)->current();
        $result = $row ? $row->extreme : 0;

        return $result;
    }

    /**
     * Get node param of a single pseudo node to be inserted
     *
     * @param mixed   $objective  Target node ID or Node
     * @param string  $position   Position to the target node,
     *      potential value: firstOf, lastOf, nextTo, previousTo
     * @return  array|false   Postion paramters: left, right
     */
    protected function getPosition($objective = null, $position = 'lastOf')
    {
        // Escape is position is invalid
        if (!in_array($position, $this->position)) {
            return false;
        }

        // Escape if objectiveId is invalid
        $row = null;
        if ($objective) {
            $row = $this->normalizeNode($objective);
            if (!$row) {
                return false;
            }
        }

        $node = array('left' => 0, 'right' => 0, 'depth' => 0);
        // Root node
        if (empty($row)) {
            if ($position == 'nextTo' || $position == 'fistOf') {
                $node['left'] = $this->getSideExtreme('left', 0);
            } else {
                $node['left'] = $this->getSideExtreme('right', 0) + 1;
            }
            return $node;
        }

        // Next to the object
        if ($position == 'nextTo') {
            $node['left'] = $row->right + 1;
            $node['depth'] = $row->depth;
        // Previous to the object
        } elseif ($position == 'previousTo') {
            $node['left'] = $row->left;
            $node['depth'] = $row->depth;
        // Fist child of the object
        } elseif ($position == 'firstOf') {
            $node['left'] = $row->left + 1;
            $node['depth'] = $row->depth + 1;
        // Last child of the object
        } elseif ($position == 'lastOf') {
            $node['left'] = $row->right;
            $node['depth'] = $row->depth + 1;
        }

        return $node;
    }

    /**
     * Shift a list of nodes
     *
     * @param int $left_start   Starting value of node_left
     * @param int $increment    Count of position increment
     * @param int $right_end    End value of right_end, if gt 0
     * @return bool
     */
    protected function shift($left_start, $increment, $right_end = 0)
    {
        if (!empty($right_end) && $right_end < $left_start) {
            return true;
        }

        // Get operator and absolute increment value
        $operator = ($increment > 0) ? '+' : '-';
        $direction = ($increment > 0) ? 'DESC' : 'ASC';
        $value = abs($increment);

        foreach (array('left', 'right') as $col) {
            $column = $this->quoteColumn($col);
            $where = sprintf('%s >= %s', $column, $left_start);
            if ($right_end) {
                $where = sprintf('%s AND %s <= %s',
                    $where, $column, $right_end);
            }
            $order = sprintf('%s %s', $column, $direction);
            $sql = sprintf(
                'UPDATE %s SET %s = %s %s %d WHERE %s ORDER BY %s',
                $this->quoteIdentifier($this->table),
                $column, $column, $operator, $value, $where, $order
            );
            $this->adapter->query($sql, 'execute');
        }

        return true;
    }

    /**
     * Strip empty positions starting from a specific position
     *
     * @param int   $start          Starting value of node left or right
     * @param bool  $leftVerified   If left side is already verified
     * @return void
     */
    public function trim($start = 1, $leftVerified = false)
    {
        // Fetch the first node in valid range
        $select = $this->select()
            ->where(array($this->quoteColumn('left') . ' >= ?' => $start))
            ->where(array($this->quoteColumn('right') . ' >= ?' => $start),
                    'OR')
            ->order($this->column['left'] . ' ASC');
        $rowRight = $this->selectWith($select)->current();
        if (!$rowRight) {
            return true;
        }

        // Detect empty positions on left side if not verified yet
        if (!$leftVerified) {
            $start = $rowRight->left;
            if ($start > 1) {
                // Find the first previous node
                $select = $this->select()
                    ->where(
                        array($this->quoteColumn('left') . ' < ?' => $start)
                    )
                    ->where(
                        array($this->quoteColumn('right') . ' < ?' => $start),
                        'OR'
                    )
                    ->order($this->column['right'] . ' DESC');
                $rowLeft = $this->selectWith($select)->current();
                if (!$rowLeft) {
                    $start = 1;
                } else {
                    $start = (($rowLeft->right < $start)
                            ? $rowLeft->right : $rowLeft->left)
                        + 1;
                }
            }
            // Shift if empty positions detected
            $shift = $start - $rowRight->left;
            if ($shift) {
                $this->shift($start, $shift);
                $rowRight->left = $start;
                $rowRight->right += $shift;
            }
        }
        if ($rowRight->depth == 0) {
            // Calulate children number v.s. right/left value
            // to determine if empty positions exist
            $select = $this->select()
                ->columns(array('count' => new Expression('COUNT(*)')))
                ->where(array(
                    $this->quoteColumn('left') . ' >= ?' => $rowRight->left
                ));
            $row = $this->selectWith($select)->current();
            // children number equal to right/left value gap,
            // no empty positions detected, exit
            $rightExtreme = $this->getSideExtreme();
            if ($row->count * 2 == $rightExtreme - $rowRight->left + 1) {
                return true;
            }
        }
        // Move on to next node if current node is a leaf
        if ($rowRight->right == $rowRight->left + 1) {
            $this->trim($rowRight->right + 1, true);
            return;
        }

        $moveOn = true;
        // Calulate children number v.s. right/left value
        // to determine if empty positions exist
        $select = $this->select()
            ->columns(array('count' => new Expression('COUNT(*)')))
            ->where(array(
                $this->quoteColumn('right') . ' < ?' => $rowRight->right
            ))
            ->where(array(
                $this->quoteColumn('left') . ' > ?' => $rowRight->left
            ));
        $row = $this->selectWith($select)->current();
        // children number smaller than right/left value gap,
        // empty positions detected, move on to first child
        if ($row->count * 2 < $rowRight->right - $rowRight->left - 1) {
            $moveOn = false;
            // Find last child in order to remove empty positions on right side
            $select = $this->select()
                ->where(array(
                    $this->quoteColumn('right') . ' < ?' => $rowRight->right
                ))
                ->order($this->column['right'] . ' DESC');
            $rowChild = $this->selectWith($select)->current();
            if (!$rowChild) {
                $end = $rowRight->left + 1;
            } else {
                $end = $rowChild->right + 1;
            }
            // Shift if empty positions detected
            $shift = $end - $rowRight->right;
            if ($shift) {
                $this->shift($end, $shift);
                $rowRight->right += $shift;
                if (
                    $row->count * 2 == $rowRight->right - $rowRight->left - 1
                ) {
                    $moveOn = true;
                }
            }
        }

        if ($moveOn) {
            $this->trim($rowRight->right + 1, true);
        } else {
            $this->trim($rowRight->left + 1, true);
        }

        return;
    }

    /**
     * Reconciles a nest set
     *
     * Widht bugfix by Lin Zongshu
     *
     * @param int       $start
     * @param int|null  $end
     * @return void
     */
    public function reconcile($start = 1, $end = null)
    {
        $primary = $this->quoteIdentifier($this->primaryKeyColumn);
        $leftCol = $this->quoteColumn('left');
        $rightCol = $this->quoteColumn('right');

        $node = $this->table;
        $parent = 'parent';
        $parentTable = $this->quoteIdentifier($parent);
        $select = new Select;
        $select->columns(array(
                    $this->primaryKeyColumn,
                    $this->column('depth'),
                    'depth_cal' => new Expression(
                        sprintf('(COUNT(%s.%s) - 1)', $parent, $primary)
                    ),
                ))
            ->from($node)
            ->join(
                array($parent => $this->table),
                sprintf(
                    '%s.%s BETWEEN %s.%s AND %s.%s',
                    $parent,
                    $this->column('left'),
                    $node,
                    $this->column('left'),
                    $node,
                    $this->column('right')
                )
              )
            ->group($parent . '.' . $this->primaryKeyColumn);
        $where = array();
        if ($start > 1) {
            $where[$parentTable . '.' . $leftCol . ' >= ?'] = $start;
        }
        if (!empty($end)) {
            $where[$parentTable . '.' . $rightCol . ' <= ?'] = $end;
        }
        $select->where($where);
        $rowset = $this->selectWith($select);
        foreach ($rowset as $row) {
            $this->update(
                array($this->column('depth') => $row->depth_cal),
                array($this->primaryKeyColumn => $row->id)
            );
        }
    }

    /**
     * Add a leaf node
     *
     * @param   array   $data       node data
     * @param   mixed   $objective  target node ID or Node
     * @param   string  $position   position to the target node,
     *      potential value: firstOf, lastOf, nextTo, previousTo
     * @return  int   The primary key of the row inserted.
     */
    public function add($data, $objective = null, $position = 'lastOf')
    {
        $objective = empty($objective) ? null : $objective;
        $position = empty($position) ? 'lastOf' : $position;
        if (!$node = $this->getPosition($objective, $position)) {
            return false;
        }
        $node_left = MAX(1, $node['left'], $node['right']);
        if (!$this->shift($node_left, 2)) {
            return false;
        }
        $data = array_merge($data, array(
            $this->column('left')   => $node_left,
            $this->column('right')  => $node_left + 1
        ));
        $row = $this->createRow($data);
        if (!isset($data['depth'])) {
            //$row = $this->createRow($data);
            $row->depth = $this->getDepth($row);
        }
        $row->save();

        return $row->id;
        //return $this->insert($data);
    }

    /**
     * Remove a node
     *
     * @param   mixed   $objective  target node ID or Node
     * @param   bool    $recursive  Whether to delete all children nodes
     * @return int|false Affected rows
     */
    public function remove($objective, $recursive = false)
    {
        $row = $this->normalizeNode($objective);
        if (!$row) {
            return false;
        }
        list($left, $right) = array($row->left, $row->right);

        //$result = parent::delete(array($this->primaryKeyColumn => $row->id));
        $result = $row->delete();
        /*
        if (!$result) {
            return false;
        }
        */

        // Remove all children
        if ($recursive/* && !$row->isLeaf()*/) {
            // Prepare for clause for children nodes with quoted identifier
            $where = array(
                $this->quoteColumn('left') . ' > ?'    => $left,
                $this->quoteColumn('right') . ' < ?'   => $right,
            );

            // Delete children and add up deleted row number
            $result += parent::delete($where);
            // shift right hand nodes with width
            if (!$this->shift($right + 1, -1 * ($right - $left + 1))) {
                return false;
            }
        // Keep children
        } else {
            $data = array(
                $this->column('depth') => new Expression(
                    $this->quoteColumn('depth') . ' - 1'
                )
            );
            $where = array(
                $this->quoteColumn('left') . ' > ?' => $left,
                $this->quoteColumn('right') . ' < ?' => $right
            );
            $this->update($data, $where);

            if (!$this->shift($left + 1, -1, $right - 1)) {
                return false;
            }
            if (!$this->shift($right + 1, -2)) {
                return false;
            }
        }

        return $result;
    }

    /**
     * Move a node
     *
     * @param   mixed   $objective  target node ID or Node
     * @param   integer $reference  reference node ID or Node
     * @param   string  $position   position to the destination node,
     *      potential value: firstOf, lastOf, nextTo, previousTo
     * @return bool
     */
    public function move($objective, $reference = null, $position = 'lastOf')
    {
        $row = $this->normalizeNode($objective);
        if (!$row) {
            return false;
        }
        $reference = $this->normalizeNode($reference);
        if (!$reference) {
            return false;
        }
        if (!$node = $this->getPosition($reference, $position)) {
            return false;
        }

        $source = array(
            'left'  => $row->left,
            'right' => $row->right
        );

        $rightExtreme = $this->getSideExtreme();
        $incrementPlaceholder = $rightExtreme - $source['left'] + 1;
        if (!$this->shift($source['left'],
            $incrementPlaceholder, $source['right'])) {
            return false;
        }

        $increment = $row->right - $row->left + 1;
        if (!empty($node['left'])) {
            $dest = array(
                'left'  => $node['left'],
                'right' => $node['left'] + $increment - 1
            );
        } elseif (!empty($node['right'])) {
            $dest = array(
                'left'  => $node['right'] - $increment + 1,
                'right' => $node['right']
            );
        } else {
            $dest = array(
                'left'  => 1,
                'right' => $increment
            );
        }
        if ($dest['left'] > $source['left']) {
            if (!$this->shift($source['right'] + 1,
                -1 * $increment, $dest['left'] - 1)) {
                return false;
            }
            $dest['left'] += -1 * $increment;
        } else {
            if (!$this->shift($dest['left'],
                $increment, $source['left'] - 1)) {
                return false;
            }
        }

        $incrementPlaceholder = $dest['left'] - $rightExtreme - 1;
        if (!$this->shift($rightExtreme + 1, $incrementPlaceholder)) {
            return false;
        }
        $this->reconcile(
            $dest['left'],
            $dest['left'] + $row->right - $row->left
        );

        return true;
    }

    /**
     * Calculate depth for a node
     *
     * @param int|Node $objective Target node ID or Node
     * @return int|false
     */
    public function getDepth($objective)
    {
        $row = $this->normalizeNode($objective);
        if (!$row) {
            return false;
        }
        $select = $this->select()
            ->columns(array('depth' => new Expression('COUNT(*)')))
            ->where(array(
                $this->quoteColumn('left') . ' < ?' => $row->left
            ))
            ->where(array(
                $this->quoteColumn('right') . ' > ?' => $row->right
            ));
        $result = $this->selectWith($select)->current();
        if (!$result) {
            return false;
        }

        return $result->depth;
    }

    /**
     * Get root nodes sorted by left value in ASC
     *
     * @param   Where|array  $where
     * @return  Rowset
     */
    public function getRoots($where = null, $order = array())
    {
        if (is_array($where) || $where instanceof Where) {
            $clause = new Where($where);
        } else {
            $clause = new Where;
        }
        $clause->equalTo($this->column['depth'], 0);
        $order = $order ?: array($this->column['left'] . ' ASC');

        $select = $this->select()->where($clause)->order($order);

        return $this->selectWith($select);
    }

    /**
     * Get ancestor nodes, top to down
     *
     * @param int|Node  $objective  Target node ID or Node
     * @param array     $cols
     * @return Rowset
     */
    public function getAncestors($objective, $cols = null)
    {
        $row = $this->normalizeNode($objective);
        if (!$row) {
            return false;
        }
        $select = $this->select()
            ->where(array($this->quoteColumn('left') . ' <= ?' => $row->left))
            ->where(array(
                $this->quoteColumn('right') . ' >= ?' => $row->right
            ));
        if (!empty($cols)) {
            $select->columns($cols);
        }
        $select->order($this->column['left'] . ' ASC');
        if (!$result = $this->selectWith($select)) {
            return false;
        }

        return $result;
    }

    /**
     * Get children nodes
     *
     * @param int|Node  $objective  Target node ID or Node
     * @param array     $cols
     * @return Rowset
     */
    public function getChildren($objective, $cols = null)
    {
        $row = $this->normalizeNode($objective);
        if (!$row) {
            return false;
        }
        $select = $this->select()
            ->where(array($this->quoteColumn('left') . ' >= ?' => $row->left))
            ->where(array(
                $this->quoteColumn('right') . ' <= ?' => $row->right
            ));
        if (!empty($cols)) {
            $select->columns($cols);
        }
        $select->order($this->column['left'] . ' ASC');
        if (!$result = $this->selectWith($select)) {
            return false;
        }

        return $result;
    }
    /**#@-*/

    /**#@+
     * Section operations
     */
    /**
     * Add a section from formulated array
     *
     * @param array     $nodes      Formulated array of nodes:
     *      left, right, ...
     * @param int|Node  $objective  Target node ID or Node
     * @param string    $position   Position to the target node,
     *      potential value: firstOf, lastOf, nextTo, previousTo
     * @return bool
     */
    public function graft($nodes, $objective = 0, $position = 'lastOf')
    {
        if (empty($nodes)
            || !$node = $this->getPosition($objective, $position)
        ) {
            return false;
        }
        $node_left = 1;
        if (isset($node['left']) && $node['left']) {
            $node_left = $node['left'];
        }
        if (isset($node['right']) && $node['right']) {
            $node_left = $node['right'];
        }
        if (!$this->shift($node_left, 2 * count($nodes))) {
            return false;
        }
        $increment = $node_left - 1;
        $depth = isset($node['depth']) ? $node['depth'] : 0;
        foreach ($nodes as $node) {
            $data = array_merge($node, array(
                $this->column('left')   => $node['left'] + $increment,
                $this->column('right')  => $node['right'] + $increment,
                $this->column('depth')  => $node['depth'] + $depth,
            ));
            $this->insert($data);
        }

        return true;
    }

    /**
     * Convert a section from a nested array
     *
     * Format of nested array
     *
     *  ```
     *                          array(
     *                              array(
     *                                  'name'  => <node name>
     *                                  'param' => <node param>
     *                              ),
     *                              array(
     *                                  'name'  => <node name>
     *                                  'param' => <node param>
     *                                  'child' => array(
     *                                      array(
     *                                          'name'  => <node name>
     *                                          'param' => <node param>
     *                                          'child' => array(
     *                                              array(
     *                                                  'name'  => <node name>
     *                                                  'param' => <node param>
     *                                              ),
     *                                          ),
     *                                      ),
     *                                      array(
     *                                          'name'  => <node name>
     *                                          'param' => <node param>
     *                                      ),
     *                                  ),
     *                              ),
     *                           );
     *  ```
     *
     * Converted associative array
     *
     *  ```
     *                          array(
     *                              array(
     *                                  'left'  => <left value>
     *                                  'right' => <right value>
     *                                  'depth' => <depth value>
     *                                  'name'  => <node name>
     *                                  'param' => <node param>
     *                              ),
     *                              array(
     *                                  'left'  => <left value>
     *                                  'right' => <right value>
     *                                  'depth' => <depth value>
     *                                  'name'  => <node name>
     *                                  'param' => <node param>
     *                              ),
     *                          );
     *  ```
     *
     * @param array $nodes  Associative nested array of nodes
     * @param int   $left   Left value
     * @param int   $depth  Depth value
     * @param int   $right  Right value
     * @return array List of formulated associative array
     */
    public function convertFromNested(
        $nodes,
        $left = 1,
        $depth = 0,
        &$right = null
    ) {
        $list = array();
        $right = 0;
        foreach ($nodes as $node) {
            // Calculate right value
            $right = $left + 1;
            // Set node value
            $node['left']   = $left;
            $node['right']  = $right;
            $node['depth']  = $depth;
            // Set children if available
            if (isset($node['child'])) {
                $children = $this->convertFromNested(
                    $node['child'],
                    $left + 1,
                    $depth + 1,
                    $right
                );
                $right++;
                // Reset right value based on children's right
                $node['right'] = $right;
                unset($node['child']);
                // Add current node
                $list[] = $node;
                // Append children
                $list = array_merge($list, $children);
            // Simply add current node
            } else {
                $list[] = $node;
            }
            // Updated left value
            $left = $node['right'] + 1;
        }

        return $list;
    }

    /**
     * Convert a section from an adjacency array
     *
     * Associative array of nodes
     *
     *  ```
     *                          array(
     *                              <node key> => array(
     *                                  'name'      => <node name>
     *                                  'title'     => <node title>
     *                                  'param'     => <node param>
     *                              ),
     *                              <node key> => array(
     *                                  'name'      => <node name>
     *                                  'parent'    => <node parent>
     *                                  'title'     => <node title>
     *                                  'param'     => <node param>
     *                              ),
     *                          );
     *  ```
     *
     * Converted associative array
     *
     *  ```
     *                          array(
     *                              array(
     *                                  'left'  => <left value>
     *                                  'right' => <right value>
     *                                  'depth' => <depth value>
     *                                  'name'  => <node name>
     *                                  'param' => <node param>
     *                              ),
     *                              array(
     *                                  'left'  => <left value>
     *                                  'right' => <right value>
     *                                  'depth' => <depth value>
     *                                  'name'  => <node name>
     *                                  'param' => <node param>
     *                              ),
     *                          );
     *  ```
     *
     * @param array $nodes  List of associative array of nodes
     * @param int   $left   Left value
     * @param int   $depth  Depth value
     * @return array List of formulated associative array
     */
    public function convertFromAdjacency($nodes, $left = 1, $depth = 0)
    {
        // Set up node container
        $temp = $nodes;
        // Set up key container
        $keys = array_fill_keys(array_keys($nodes), 1);

        // Look up node list to append child node to its parent,
        // until no child node is left in container
        $registered = array();
        do {
            foreach (array_keys($keys) as $key) {
                $item =& $temp[$key];
                // Has parent
                if (isset($item['parent'])) {
                    $parentKey = $item['parent'];
                    // Register to parent
                    if (isset($temp[$parentKey])) {
                        if (!isset($temp[$parentKey]['child'])) {
                            $temp[$parentKey]['child'] = array();
                            $temp[$parentKey]['child'][] =& $item;
                            $registered[$key] = 1;
                        } elseif (!isset($registered[$key])) {
                            $temp[$parentKey]['child'][] =& $item;
                            $registered[$key] = 1;
                        }
                        // To reactivate parent
                        $keys[$parentKey] = 1;
                    }
                }
                // Remove node from container
                unset($keys[$key]);
            }
        } while ($keys);

        // Fetch formuated nodes
        $list = array();
        foreach ($temp as $key =>& $node) {
            if (!empty($node['parent'])) {
                //unset($node['parent']);
                continue;
            }
            $list[$key] = $node;
        }

        // Nested array is generated and deliver to its right method
        return $this->convertFromNested($list, $left, $depth);
    }

    /**
     * Enumerate child nodes of a node
     *
     * Formatted associative array of children
     *
     * Tree mode
     *
     *  ```
     *                              <id> => array(
     *                                  //<depth> => {0-?},
     *                                  <node>  => array(),  node data
     *                                  <child> => array(   ld nodes
     *                                      <id>    => array(
     *                                          //<depth>   => {0-?},
     *                                          <node>    => array(),
     *                                          <child>   => array(
     *  ```
     *
     * Plain format
     *
     *  ```
     *                              <id> => array(
     *                                  //<depth> => {0-?},
     *                                  //<node>  => array(),  odes
     *                              <id> => array(
     *                                  //<depth> => {0-?},
     *                                  //<node>  => array(),
     *  ```
     *
     * @param int|Node  $objective  Root node ID or Node or Where
     * @param array     $cols       Columns to be fetched
     * @param bool      $plain      Result format,
     *      plain array or hirechical tree
     * @return array Associative array of children
     */
    public function enumerate($objective = null, $cols = null, $plain = false)
    {
        $result = array();

        $root = null;
        $singleRoot = false;
        $select = $this->select();
        if (!empty($objective)) {
            if ($objective instanceof Where) {
                $select->where($objective);
            } else {
                $row = $this->normalizeNode($objective);
                if (!$row) {
                    return false;
                }
                $root = $row;
                $root_id = $root->id;
                $item = $row->toArray();
                if (empty($plain)) {
                    $ret[$root_id] = $item;
                } else {
                    $ret[$root_id] = $item;
                    $result[$root_id] = $item;
                }
                $stack = array();
                $select->where(array(
                        $this->quoteColumn('left') . ' > ?' => $row->left
                    ))
                    ->where(array(
                        $this->quoteColumn('right') . ' < ?' => $row->right
                    ));
            }
        }
        if (!empty($cols)) {
            $cols = (array) $cols;
            if (!in_array('*', $cols)) {
                if (!in_array($this->column('left'), $cols)) {
                    $cols[] = $this->column('left');
                }
                if (!in_array($this->column('right'), $cols)) {
                    $cols[] = $this->column('right');
                }
            }
            $select->columns($cols);
        }
        $select->order($this->column['left'] . ' ASC');
        if (!$rowset = $this->selectWith($select)) {
            return false;
        }

        // start with the root and an empty stack
        foreach ($rowset as $row) {
            // Initialize a tree or start a new tree when last tree finishes
            if (is_null($root) || $row->left > $root->right) {
                if (!empty($ret) && empty($plain)) {
                    $result += $ret;
                }
                unset($ret);
                $root = $row;
                $root_id = $root->id;
                $item = $row->toArray();
                if (empty($plain)) {
                    $ret[$root_id] = $item;
                } else {
                    $ret[$root_id] = $item;
                    $result[$root_id] = $item;
                }
                $stack = array();
                continue;
            }

            $parent =& $ret[$root_id];
            if (!empty($stack)) {
                // remove nodes with right smaller than current node,
                // which means not ancestors anymore
                $count = count($stack);
                while ($count && $stack[$count - 1]['right'] < $row->right) {
                    array_pop($stack);
                    $count = count($stack);
                }
                if (($count_stack = count($stack)) > 0) {
                    for($i = 0; $i < $count_stack; $i ++) {
                        $parent =& $parent['child'][$stack[$i]['id']];
                    }
                }
            }

            // add this node to the stack for next node
            $stack[] = array('id' => $row->id, 'right' => $row->right);

            //continue;
            // store the node
            $item = $row->toArray();
            if (empty($plain)) {
                $parent['child'][$row->id] = $item;
            } else {
                $parent['child'][$row->id] = $item;
                $result[$row->id] = $item;
            }
        }

        if (empty($plain) && !$singleRoot && !empty($ret)) {
            $result += $ret;
        }

        return $result;
    }
}

/**
 * Test code for convertFromNested, convertFromAdjacency, graft
 */
/*
        $nested = array(
            array(
                'name'  => '1',
                'child'    => array(
                    array(
                        'name'  => '11',
                    ),
                    array(
                        'name'  => '12',
                        'child' => array(
                            array(
                                'name'  => '121',
                            ),
                            array(
                                'name'  => '122',
                            ),
                            array(
                                'name'  => '123',
                                'child' => array(
                                    array(
                                        'name'  => '1231',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'name'  => '13',
                    ),
                    array(
                        'name'  => '14',
                        'child' => array(
                            array(
                                'name'  => '141',
                                'child' => array(
                                    array(
                                        'name'  => '1411',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'name'  => '2',
            ),
        );

        $list = array(
            1   => array(
                'name'  => 'a1',
            ),
            2   => array(
                'name'  => 'a2',
            ),
            11   => array(
                'name'      => 'a11',
                'parent'    => 1,
            ),
            12   => array(
                'name'      => 'a12',
                'parent'    => 1,
            ),
            13   => array(
                'name'      => 'a13',
                'parent'    => 1,
            ),
            14   => array(
                'name'      => 'a14',
                'parent'    => 1,
            ),
            121   => array(
                'name'      => 'a121',
                'parent'    => 12,
            ),
            122   => array(
                'name'      => 'a122',
                'parent'    => 12,
            ),
            123   => array(
                'name'      => 'a123',
                'parent'    => 12,
            ),
            1231   => array(
                'name'      => 'a1231',
                'parent'    => 123,
            ),
            141   => array(
                'name'      => 'a141',
                'parent'    => 14,
            ),
            1411   => array(
                'name'      => 'a1411',
                'parent'    => 141,
            ),
        );

        $model = Pi::db()->model('test', array('type' => 'nest'));
        $nested = $model->convertFromNested($nested);
        $model->graft($nested);

        $list = $model->convertFromAdjacency($list);
        $model->graft($list, 11);
*/
