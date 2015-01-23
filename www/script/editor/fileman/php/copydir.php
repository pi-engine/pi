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

verifyAction('COPYDIR');
checkAccess('COPYDIR');

$path = trim(empty($_POST['d'])?'':$_POST['d']);
$newPath = trim(empty($_POST['n'])?'':$_POST['n']);
verifyPath($path);
verifyPath($newPath);

function copyDir($path, $newPath){
  $items = listDirectory($path);
  if(!is_dir($newPath))
    mkdir ($newPath, octdec(DIRPERMISSIONS));
  foreach ($items as $item){
    if($item == '.' || $item == '..')
      continue;
    $oldPath = RoxyFile::FixPath($path.'/'.$item);
    $tmpNewPath = RoxyFile::FixPath($newPath.'/'.$item);
    if(is_file($oldPath))
      copy($oldPath, $tmpNewPath);
    elseif(is_dir($oldPath)){
      copyDir($oldPath, $tmpNewPath);
    }
  }
}

if(is_dir(fixPath($path))){
  copyDir(fixPath($path.'/'), fixPath($newPath.'/'.basename($path)));
  echo getSuccessRes();
}
else
  echo getErrorRes(t('E_CopyDirInvalidPath'));
?>