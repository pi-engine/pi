<?php
/**
 * Pi Engine Filter PluginManager
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
 * @since           1.0
 * @package         Pi\Filter
 * @version         $Id$
 */

namespace Pi\Filter;

use Pi;
use Zend\Filter\FilterPluginManager as ZendFilterPluginManager;

class FilterPluginManager extends ZendFilterPluginManager
{
    /**
     * Default set of loaders
     *
     * @var array
     */
    protected $invokableClasses = array(
        'user'  => 'Pi\Filter\User',
        'tag'   => 'Pi\Filter\Tag',
    );

    /**
     * Default set of filters
     *
     * @var array
     */
    protected $invokableList = array(
        'alnum'                     => 'I18n\Filter\Alnum',
        'alpha'                     => 'I18n\Filter\Alpha',
        'basename'                  => 'Filter\BaseName',
        'boolean'                   => 'Filter\Boolean',
        'callback'                  => 'Filter\Callback',
        'compress'                  => 'Filter\Compress',
        'compressbz2'               => 'Filter\Compress\Bz2',
        'compressgz'                => 'Filter\Compress\Gz',
        'compresslzf'               => 'Filter\Compress\Lzf',
        'compressrar'               => 'Filter\Compress\Rar',
        'compresstar'               => 'Filter\Compress\Tar',
        'compresszip'               => 'Filter\Compress\Zip',
        'decompress'                => 'Filter\Decompress',
        'decrypt'                   => 'Filter\Decrypt',
        'digits'                    => 'Filter\Digits',
        'dir'                       => 'Filter\Dir',
        'encrypt'                   => 'Filter\Encrypt',
        'encryptblockcipher'        => 'Filter\Encrypt\BlockCipher',
        'encryptopenssl'            => 'Filter\Encrypt\Openssl',
        'filedecrypt'               => 'Filter\File\Decrypt',
        'fileencrypt'               => 'Filter\File\Encrypt',
        'filelowercase'             => 'Filter\File\LowerCase',
        'filerename'                => 'Filter\File\Rename',
        'fileuppercase'             => 'Filter\File\UpperCase',
        'htmlentities'              => 'Filter\HtmlEntities',
        'inflector'                 => 'Filter\Inflector',
        'int'                       => 'Filter\Int',
        'localizedtonormalized'     => 'Filter\LocalizedToNormalized',
        'normalizedtolocalized'     => 'Filter\NormalizedToLocalized',
        'null'                      => 'Filter\Null',
        'numberformat'              => 'I18n\Filter\NumberFormat',
        'pregreplace'               => 'Filter\PregReplace',
        'realpath'                  => 'Filter\RealPath',
        'stringtolower'             => 'Filter\StringToLower',
        'stringtoupper'             => 'Filter\StringToUpper',
        'stringtrim'                => 'Filter\StringTrim',
        'stripnewlines'             => 'Filter\StripNewlines',
        'striptags'                 => 'Filter\StripTags',
        'wordcamelcasetodash'       => 'Filter\Word\CamelCaseToDash',
        'wordcamelcasetoseparator'  => 'Filter\Word\CamelCaseToSeparator',
        'wordcamelcasetounderscore' => 'Filter\Word\CamelCaseToUnderscore',
        'worddashtocamelcase'       => 'Filter\Word\DashToCamelCase',
        'worddashtoseparator'       => 'Filter\Word\DashToSeparator',
        'worddashtounderscore'      => 'Filter\Word\DashToUnderscore',
        'wordseparatortocamelcase'  => 'Filter\Word\SeparatorToCamelCase',
        'wordseparatortodash'       => 'Filter\Word\SeparatorToDash',
        'wordseparatortoseparator'  => 'Filter\Word\SeparatorToSeparator',
        'wordunderscoretocamelcase' => 'Filter\Word\UnderscoreToCamelCase',
        'wordunderscoretodash'      => 'Filter\Word\UnderscoreToDash',
        'wordunderscoretoseparator' => 'Filter\Word\UnderscoreToSeparator',
    );

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  array $options
     * @param  bool $usePeeringServiceManagers
     * @return object
     */
    public function get($name, $options = array(), $usePeeringServiceManagers = true)
    {
        // Canonize invokable class from name
        if (!$this->has($name) && !class_exists($name)) {
            // Lookup in default invokable list
            $cname = strtolower(str_replace(array('-', '_', ' ', '\\', '/'), '', $name));
            if (isset($this->invokableList[$cname])) {
                $invokableClass = 'Pi\\' . $this->invokableList[$cname];
                if (!class_exists($invokableClass)) {
                    $invokableClass = 'Zend\\' . $this->invokableList[$cname];
                }
                $name = $invokableClass;
            // Lookup in helper locations
            } else {
                $class = str_replace(' ', '', ucwords(str_replace(array('-', '_', '\\', '/'), ' ', $name)));
                if (class_exists('Pi\\Filter\\' . $class)) {
                    $name = 'Pi\\Filter\\' . $class;
                } else {
                    $name = 'Zend\\Filter\\' . $class;
                }
            }
        }
        $filter = parent::get($name, $options, $usePeeringServiceManagers);
        return $filter;
    }
}
