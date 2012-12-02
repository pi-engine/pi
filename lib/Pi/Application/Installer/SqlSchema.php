<?php
/**
 * Installer SQL query class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Application
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Pi\Application\Installer;
use Pi;

class SqlSchema
{
    /**
     * Schema file
     * @var string
     */
    protected $file;
    /**
     * Table types, core or specified module
     */
    protected static $type;

    public function __construct($file = null)
    {
        if ($file) {
            $this->setFile($file);
        }
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public static function setType($type)
    {
        static::$type = $type;
    }

    public static function normalizeSchema($matches)
    {
        $name = $matches[1];
        // Core tables: {core.user}
        if (substr($name, 0, 6) == '{core.') {
            $tableName = substr($name, 6, -1);
            $tableName = Pi::db()->prefix($tableName, 'core');
        // Module tables: {article}
        } else {
            $tableName = substr($name, 1, -1);
            $tableName = Pi::db()->prefix($tableName, static::$type);
        }
        return $tableName;
    }

    public function parseContent($content)
    {
        // Remove comments to prevent from invalid syntax
        $content = preg_replace('|(#.*)|', '# <-- Comment skipped -->', $content);
        // Normalize table prefix
        return preg_replace_callback('|(\{[^\}]+\})|', 'static::normalizeSchema', $content);
    }

    public function queryContent($content = null)
    {
        $sql = $this->parseContent($content);
        Pi::db()->adapter()->query($sql, 'execute');
        return true;
    }

    public function queryFile($file = null)
    {
        $content = file_get_contents($file ?: $this->file);
        return $this->queryContent($content);
    }

    public static function query($file, $type = 'core')
    {
        $schema = new self;
        static::setType($type);
        return $schema->queryFile($file);
    }
}
