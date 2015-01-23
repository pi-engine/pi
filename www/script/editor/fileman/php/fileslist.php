<?php
/*
  RoxyFileman - web based file manager. Ready to use with CKEditor, TinyMCE. 
  Can be easily integrated with any other WYSIWYG editor or CMS.

  Copyright (C) 2013, RoxyFileman.com - Lyubomir Arsov. All rights reserved.
  For licensing, see LICENSE.txt or http://RoxyFileman.com/license

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

  Contact: Lyubomir Arsov, liubo (at) web-lobby.com
*/
include '../system.inc.php';
include 'functions.inc.php';

verifyAction('FILESLIST');
checkAccess('FILESLIST');

$path = (empty($_POST['d'])? getFilesPath(): $_POST['d']);
$type = (empty($_POST['type'])?'':strtolower($_POST['type']));
if($type != 'image' && $type != 'flash')
  $type = '';
verifyPath($path);

$files = listDirectory(fixPath($path), 0);
natcasesort($files);
$str = '';
echo '[';
foreach ($files as $f){
  $fullPath = $path.'/'.$f;
  if(!is_file(fixPath($fullPath)) || ($type == 'image' && !RoxyFile::IsImage($f)) || ($type == 'flash' && !RoxyFile::IsFlash($f)))
    continue;
  $size = filesize(fixPath($fullPath));
  $time = filemtime(fixPath($fullPath));
  $w = 0;
  $h = 0;
  if(RoxyFile::IsImage($f)){
    $tmp = @getimagesize(fixPath($fullPath));
    if($tmp){
      $w = $tmp[0];
      $h = $tmp[1];
    }
  }
  $str .= '{"p":"'.mb_ereg_replace('"', '\\"', $fullPath).'","s":"'.$size.'","t":"'.$time.'","w":"'.$w.'","h":"'.$h.'"},';
}
$str = mb_substr($str, 0, -1);
echo $str;
echo ']';
?>