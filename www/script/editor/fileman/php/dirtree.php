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

verifyAction('DIRLIST');
checkAccess('DIRLIST');

function getFilesNumber($path, $type){
  $files = 0;
  $dirs = 0;
  $tmp = listDirectory($path);
  foreach ($tmp as $ff){
    if($ff == '.' || $ff == '..')
      continue;
    elseif(is_file($path.'/'.$ff) && ($type == '' || ($type == 'image' && RoxyFile::IsImage($ff)) || ($type == 'flash' && RoxyFile::IsFlash($ff))))
      $files++;
    elseif(is_dir($path.'/'.$ff))
      $dirs++;
  }

  return array('files'=>$files, 'dirs'=>$dirs);
}
function GetDirs($path, $type){
  $ret = $sort = array();
  $files = listDirectory(fixPath($path), 0);
  foreach ($files as $f){
    $fullPath = $path.'/'.$f;
    if(!is_dir(fixPath($fullPath)) || $f == '.' || $f == '..')
      continue;
    $tmp = getFilesNumber(fixPath($fullPath), $type);
    $ret[$fullPath] = array('path'=>$fullPath,'files'=>$tmp['files'],'dirs'=>$tmp['dirs']);
    $sort[$fullPath] = $f;
  }
  natcasesort($sort);
  foreach ($sort as $k => $v) {
    $tmp = $ret[$k];
    echo ',{"p":"'.mb_ereg_replace('"', '\\"', $tmp['path']).'","f":"'.$tmp['files'].'","d":"'.$tmp['dirs'].'"}';
    GetDirs($tmp['path'], $type);
  }
}

$type = (empty($_GET['type'])?'':strtolower($_GET['type']));
if($type != 'image' && $type != 'flash')
  $type = '';

echo "[\n";
$tmp = getFilesNumber(fixPath(getFilesPath()), $type);
echo '{"p":"'.  mb_ereg_replace('"', '\\"', getFilesPath()).'","f":"'.$tmp['files'].'","d":"'.$tmp['dirs'].'"}';
GetDirs(getFilesPath(), $type);
echo "\n]";
?>