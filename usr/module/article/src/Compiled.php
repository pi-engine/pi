<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article;

use Pi;

/**
 * Compiled service APIs
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Compiled
{
    protected static $module = 'article';

    /**
     * Compiled content
     * 
     * @param string  $srcType
     * @param string  $content
     * @param string  $destType
     * @return string
     */
    public static function compiled($srcType, $content, $destType)
    {
        $content = Pi::service('markup')->render($content, $destType, $srcType);
        return $content;
    }
    
    /**
     * Get compiled article content, if it is not exists, reading content
     * from article table and compiling it.
     * 
     * @param int     $article  Article ID
     * @param string  $type     Type that content will be complied to
     * @return boolean 
     */
    public static function getContent($article, $type)
    {
        $module = Pi::service('module')->current();
        $type   = empty($type) ? 'html' : $type;
        $name   = $article . '-' . $type;
        
        // Reading article content from compiled table
        $modelCompiled = Pi::model('compiled', $module);
        $rowCompiled   = $modelCompiled->find($name, 'name');
        if (!empty($rowCompiled->id)) {
            return $rowCompiled->content;
        }
        
        // Reading article content from article table
        $modelArticle  = Pi::model('article', $module);
        $rowArticle    = $modelArticle->find($article);
        if (!$rowArticle->id) {
            return false;
        }
        
        // Compiled article content and saving into compiled table
        $compiledContent = self::compiled(
            $rowArticle->markup, 
            $rowArticle->content, 
            $type
        );
        $data            = array(
            'name'       => $name,
            'article'    => $rowArticle->id,
            'type'       => $type,
            'content'    => $compiledContent,
        );
        $rowCompiled     = $modelCompiled->createRow($data);
        $rowCompiled->save();
        if (!$rowCompiled->id) {
            return false;
        }
        
        return $compiledContent;
    }
}
