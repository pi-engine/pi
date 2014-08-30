<?PHP
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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