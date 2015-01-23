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

header("Pragma: cache");
header("Cache-Control: max-age=3600");

verifyAction('GENERATETHUMB');
checkAccess('GENERATETHUMB');

$path = urldecode(empty($_GET['f'])?'':$_GET['f']);
verifyPath($path);

@chmod(fixPath(dirname($path)), octdec(DIRPERMISSIONS));
@chmod(fixPath($path), octdec(FILEPERMISSIONS));

$w = intval(empty($_GET['width'])?'100':$_GET['width']);
$h = intval(empty($_GET['height'])?'0':$_GET['height']);

header('Content-type: '.RoxyFile::GetMIMEType(basename($path)));
if($w && $h)
  RoxyImage::CropCenter(fixPath($path), null, $w, $h);
else 
  RoxyImage::Resize(fixPath($path), null, $w, $h);
?>