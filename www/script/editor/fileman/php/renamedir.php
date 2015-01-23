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

verifyAction('RENAMEDIR');
checkAccess('RENAMEDIR');

$path = trim(empty($_POST['d'])? '': $_POST['d']);
$name = trim(empty($_POST['n'])? '': $_POST['n']);
verifyPath($path);

if(is_dir(fixPath($path))){
  if(fixPath($path.'/') == fixPath(getFilesPath().'/'))
    echo getErrorRes(t('E_CannotRenameRoot'));
  elseif(rename(fixPath($path), dirname(fixPath($path)).'/'.$name))
    echo getSuccessRes();
  else
    echo getErrorRes(t('E_RenameDir').' '.basename($path));
}
else
  echo getErrorRes(t('E_RenameDirInvalidPath'));
?>