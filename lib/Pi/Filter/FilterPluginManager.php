<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Filter;

use Pi;
use Zend\Filter\FilterPluginManager as ZendFilterPluginManager;

/**
 * Filter plugin manager
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FilterPluginManager extends ZendFilterPluginManager
{
    /**
     * Default set of loaders
     * @var array
     */
    protected $invokableClasses = array(
        'user'  => 'Pi\Filter\User',
        'tag'   => 'Pi\Filter\Tag',
    );

    /**
     * Default set of filters
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
     * {@inheritDoc}
     */
    public function get(
        $name,
        $options = array(),
        $usePeeringServiceManagers = true
    ) {
        // Canonize invokable class from name
        if (!$this->has($name) && !class_exists($name)) {
            // Lookup in default invokable list
            $cname = strtolower(
                str_replace(array('-', '_', ' ', '\\', '/'), '', $name)
            );
            if (isset($this->invokableList[$cname])) {
                $invokableClass = 'Pi\\' . $this->invokableList[$cname];
                if (!class_exists($invokableClass)) {
                    $invokableClass = 'Zend\\' . $this->invokableList[$cname];
                }
                $name = $invokableClass;
            // Lookup in helper locations
            } else {
                $class = str_replace(' ', '', ucwords(str_replace(
                    array('-', '_', '\\', '/'),
                    ' ',
                    $name
                )));
                if (class_exists('Pi\Filter\\' . $class)) {
                    $name = 'Pi\Filter\\' . $class;
                } else {
                    $name = 'Zend\Filter\\' . $class;
                }
            }
        }
        $filter = parent::get($name, $options, $usePeeringServiceManagers);

        return $filter;
    }
}
