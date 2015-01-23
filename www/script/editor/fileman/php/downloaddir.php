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
@ini_set('memory_limit', -1);
verifyAction('DOWNLOADDIR');
checkAccess('DOWNLOADDIR');

$path = trim($_GET['d']);
verifyPath($path);
$path = fixPath($path);

if(!class_exists('ZipArchive')){
  echo '<script>alert("Cannot create zip archive - ZipArchive class is missing. Check your PHP version and configuration");</script>';
}
else{
  try{
    $filename = basename($path);
    $zipFile = $filename.'.zip';
    $zipPath = BASE_PATH.'/tmp/'.$zipFile;
    RoxyFile::ZipDir($path, $zipPath);

    header('Content-Disposition: attachment; filename="'.$zipFile.'"');
    header('Content-Type: application/force-download');
    readfile($zipPath);
    function deleteTmp($zipPath){
      @unlink($zipPath);
    }
    register_shutdown_function('deleteTmp', $zipPath);
  }
  catch(Exception $ex){
    echo '<script>alert("'.  addslashes(t('E_CreateArchive')).'");</script>';
  }
}
?>