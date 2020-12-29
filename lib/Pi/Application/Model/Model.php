<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Model;

use Pi\Db\Table\AbstractTableGateway;

/**
 * Pi table gateway model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Model extends AbstractTableGateway
{
    /**
     * {@inheritDoc}
     */
    protected $primaryKeyColumn = 'id';

    /**
     * {@inheritDoc}
     */
    protected $rowClass = 'Pi\Db\RowGateway\RowGateway';

    protected $mediaLinks = [];

    /**
     * Return media links
     *
     * @return array
     */
    public function getMediaLinks()
    {
        return $this->mediaLinks;
    }
}
