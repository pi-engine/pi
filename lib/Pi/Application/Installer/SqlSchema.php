<?php
/**
 * Installer SQL query class
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
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
     * @var string
     */
    protected static $type;

    /**
     * Constructor
     *
     * @param string|null $file
     */
    public function __construct($file = null)
    {
        if ($file) {
            $this->setFile($file);
        }
    }

    /**
     * Set schema file
     *
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Set schema type
     *
     * @param string $type
     * @return void
     */
    public static function setType($type)
    {
        static::$type = $type;
    }

    /**
     * Normalize schema name
     *
     * @param array $matches
     * @return string
     */
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

    /**
     * Parse schema definition content
     *
     * @param string $content
     * @return string
     */
    public function parseContent($content)
    {
        // Remove comments to prevent from invalid syntax
        $content = preg_replace('|(#.*)|', '# <-- Comment skipped -->', $content);
        // Normalize table prefix
        return preg_replace_callback('|(\{[^\}]+\})|', 'static::normalizeSchema', $content);
    }

    /**
     * Performe query on content
     *
     * @param string $content
     * @return bool
     */
    public function queryContent($content = null)
    {
        $sql = $this->parseContent($content);
        Pi::db()->adapter()->query($sql, 'execute');
        return true;
    }

    /**
     * Query content from a file
     *
     * @param string $file
     * @return bool
     */
    public function queryFile($file = null)
    {
        $content = file_get_contents($file ?: $this->file);
        return $this->queryContent($content);
    }

    /**
     * Query a file with specified type
     *
     * @param string $file
     * @param string $type
     * @return bool
     */
    public static function query($file, $type = 'core')
    {
        $schema = new self;
        static::setType($type);
        return $schema->queryFile($file);
    }
}
