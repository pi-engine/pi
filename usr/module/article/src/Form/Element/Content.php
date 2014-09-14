<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Element;

use Pi;
use Pi\Form\Element\Editor;

/**
 * Content element class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Content extends Editor
{
    /**
     * Add default required options, avoid options been override
     * 
     * @param array $options
     */
    public function setOptions($options)
    {
        $module = $this->getOption('module') 
                ?: Pi::service('module')->current();
        if (!isset($options['editor']) || !isset($options['set'])) {
            switch (Pi::config('markup', $module)) {
                case 'html':
                    $editor = 'html';
                    $set    = '';
                    break;
                case 'compound':
                    $editor = 'markitup';
                    $set    = 'html';
                    break;
                case 'markdown':
                    $editor = 'markitup';
                    $set    = 'markdown';
                    break;
                default:
                    $editor = 'textarea';
                    $set    = '';
            }
            $options['editor'] = $editor;
            $options['set']    = $set;
        }
        
        if (!isset($options['attributes']) || !isset($options['options'])) {
            $configFile = "module.{$module}.ckeditor.php";
            if (!file_exists(Pi::path("var/config/{$configFile}"))) {
                $configFile = 'module.article.ckeditor.php';
            }
            $editorConfig = Pi::config()->load($configFile);
            $options = array_merge($options, $editorConfig);
        }
        parent::setOptions($options);
    }
}
