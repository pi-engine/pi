<?PHP
/**
 * Preview script for MarkItUp markdown
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
 * @package         Editor\Markitup
 * @since           3.0
 * @version         $Id$
 */

$content = $_POST['preview'];

if (class_exists('MarkdownDocument')) {
    $markdown = MarkdownDocument::createFromString($content);
    $markdown->compile();
    $content = $markdown->getHtml();
} else {
    echo '<h2>MarkdownDocument is not available</h2><hr />';
    $content = '<pre>' . $content . '</pre>';
}

echo $content;