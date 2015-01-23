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
include 'security.inc.php';
function t($key){
  global $LANG;
  if(empty($LANG)){
    $file = 'en.json';
    $langPath = '../lang/';
    if(defined('LANG')){
      if(LANG == 'auto'){
        $lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
        if(is_file($langPath.$lang.'.json'))
          $file = $lang.'.json';
      }
      elseif(is_file($langPath.LANG.'.json'))
        $file = LANG.'.json';
    }
    $file = $langPath.$file;
    $LANG = json_decode(file_get_contents($file), true);
  }
  if(!$LANG[$key])
    $LANG[$key] = $key;

  return $LANG[$key];
}
function checkPath($path){
  $ret = false;
  if(mb_strpos($path.'/', getFilesPath()) === 0)
    $ret = true;

  return $ret;
}
function verifyAction($action){
  if(!defined($action) || !constant($action))
    exit;
  else{
    $confUrl = constant($action);
    $qStr = mb_strpos($confUrl, '?');
    if($qStr !== false)
      $confUrl = mb_substr ($confUrl, 0, $qStr);
    $confUrl = BASE_PATH.'/'.$confUrl;
    $confUrl = RoxyFile::FixPath($confUrl);
    $thisUrl = dirname(__FILE__).'/'.basename($_SERVER['PHP_SELF']);
    $thisUrl = RoxyFile::FixPath($thisUrl);
    if($thisUrl != $confUrl){
      echo "$confUrl $thisUrl";
      exit;
    }
  }
}
function verifyPath($path){
  if(!checkPath($path)){
    echo getErrorRes("Access to $path is denied").' '.$path;
    exit;
  }
}
function fixPath($path){
  $path = $_SERVER['DOCUMENT_ROOT'].'/'.$path;
  $path = str_replace('\\', '/', $path);
  $path = RoxyFile::FixPath($path);
  return $path;
}
function gerResultStr($type, $str = ''){
  return '{"res":"'.  addslashes($type).'","msg":"'.  addslashes($str).'"}';
}
function getSuccessRes($str = ''){
  return gerResultStr('ok', $str);
}
function getErrorRes($str = ''){
  return gerResultStr('error', $str);
}
function getFilesPath(){
  $ret = (isset($_SESSION[SESSION_PATH_KEY]) && $_SESSION[SESSION_PATH_KEY] != ''?$_SESSION[SESSION_PATH_KEY]:FILES_ROOT);
  if(!$ret){
    $ret = RoxyFile::FixPath(BASE_PATH.'/Uploads');
    $tmp = $_SERVER['DOCUMENT_ROOT'];
    if(mb_substr($tmp, -1) == '/' || mb_substr($tmp, -1) == '\\')
      $tmp = mb_substr($tmp, 0, -1);
    $ret = str_replace(RoxyFile::FixPath($tmp), '', $ret);
  }
  return $ret;
}
function listDirectory($path){
  $ret = @scandir($path);
  if($ret === false){
    $ret = array();
    $d = opendir($path);
    if($d){
      while(($f = readdir($d)) !== false){
        $ret[] = $f;
      }
      closedir($d);
    }
  }
  
  return $ret;
}
class RoxyFile{
  static public function CheckWritable($dir){
    $ret = false;
    if(self::CreatePath($dir)){
      $dir = self::FixPath($dir.'/');
      $testFile = 'writetest.txt';
      $f = @fopen($dir.$testFile, 'w', false);
      if($f){
        fclose($f);
        $ret = true;
        @unlink($dir.$testFile);
      }
    }

    return $ret;
  }
  static function CanUploadFile($filename){
    $ret = false;
    $forbidden = array_filter(preg_split('/[^\d\w]+/', strtolower(FORBIDDEN_UPLOADS)));
    $allowed = array_filter(preg_split('/[^\d\w]+/', strtolower(ALLOWED_UPLOADS)));
    $ext = RoxyFile::GetExtension($filename);

    if((empty($forbidden) || !in_array($ext, $forbidden)) && (empty($allowed) || in_array($ext, $allowed)))
      $ret = true;

    return $ret;
  }
  static function ZipAddDir($path, $zip, $zipPath){
    $d = opendir($path);
    $zipPath = str_replace('//', '/', $zipPath);
    if($zipPath && $zipPath != '/'){
      $zip->addEmptyDir($zipPath);
    }
    while(($f = readdir($d)) !== false){
      if($f == '.' || $f == '..')
        continue;
      $filePath = $path.'/'.$f;
      if(is_file($filePath)){
        $zip->addFile($filePath, ($zipPath?$zipPath.'/':'').$f);
      }
      elseif(is_dir($filePath)){
        self::ZipAddDir($filePath, $zip, ($zipPath?$zipPath.'/':'').$f);
      }
    }
    closedir($d);
  }
  static function ZipDir($path, $zipFile, $zipPath = ''){
    $zip = new ZipArchive();
    $zip->open($zipFile, ZIPARCHIVE::CREATE);
    self::ZipAddDir($path, $zip, $zipPath);
    $zip->close();
  }
  static function IsImage($fileName){
    $ret = false;
    $ext = strtolower(self::GetExtension($fileName));
    if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'jpe' || $ext == 'png' || $ext == 'gif' || $ext == 'ico')
      $ret = true;
    return $ret;
  }
  static function IsFlash($fileName){
    $ret = false;
    $ext = strtolower(self::GetExtension($fileName));
    if($ext == 'swf' || $ext == 'flv' || $ext == 'swc' || $ext == 'swt')
      $ret = true;
    return $ret;
  }
  /**
   * Returns human formated file size
   *
   * @param int $filesize
   * @return string
   */
  static function FormatFileSize($filesize){
    $ret = '';
    $unit = 'B';
    if($filesize > 1024){
      $unit = 'KB';
      $filesize = $filesize / 1024;
    }
    if($filesize > 1024){
      $unit = 'MB';
      $filesize = $filesize / 1024;
    }
    if($filesize > 1024){
      $unit = 'GB';
      $filesize = $filesize / 1024;
    }

    $ret = round($filesize, 2).' '.$unit;
    return $ret;
  }
  /**
   * Returns MIME type of $filename
   *
   * @param string $filename
   * @return string
   */
  static function GetMIMEType($filename){
    $type = 'application/octet-stream';
    $ext = self::GetExtension($filename);

    switch(strtolower($ext)){
      case 'jpg':  $type = 'image/jpeg';break;
      case 'jpeg': $type = 'image/jpeg';break;
      case 'gif':  $type = 'image/gif';break;
      case 'png':  $type = 'image/png';break;
      case 'bmp':  $type = 'image/bmp';break;
      case 'tiff': $type = 'image/tiff';break;
      case 'tif':  $type = 'image/tiff';break;
      case 'pdf':  $type = 'application/pdf';break;
      case 'rtf':  $type = 'application/msword';break;
      case 'doc':  $type = 'application/msword';break;
      case 'xls':  $type = 'application/vnd.ms-excel'; break;
      case 'zip':  $type = 'application/zip'; break;
      case 'swf':  $type = 'application/x-shockwave-flash'; break;
      default: $type = 'application/octet-stream';
    }

    return $type;
  }

  /**
   * Replaces any character that is not letter, digit or underscore from $filename with $sep
   *
   * @param string $filename
   * @param string $sep
   * @return string
   */
  static function CleanupFilename($filename, $sep = '_'){
    $str = '';
    if(strpos($filename,'.')){
      $ext = self::GetExtension($filename) ;
      $name = self::GetName($filename);
    }
    else{
      $ext = '';
      $name = $filename;
    }
    if(mb_strlen($name) > 32)
      $name = mb_substr($name, 0, 32);
    $str = str_replace('.php', '', $str);
    $str = mb_ereg_replace("[^ a-zA-Z\\_\\d\\.]|\\s", $sep, $name);
    
    $str = mb_ereg_replace("$sep+", $sep, $str).($ext?'.'.$ext:'');

    return $str;
  }

  /**
   * Returns file extension without dot
   *
   * @param string $filename
   * @return string
   */
  static function GetExtension($filename) {
    $ext = '';

    if(mb_strrpos($filename, '.') !== false)
      $ext = mb_substr($filename, mb_strrpos($filename, '.') + 1);

    return strtolower($ext);
  }

  /**
   * Returns file name without extension
   *
   * @param string $filename
   * @return string
   */
  static function GetName($filename) {
    $name = '';
    $tmp = mb_strpos($filename, '?');
    if($tmp !== false)
        $filename = mb_substr ($filename, 0, $tmp);
    $dotPos = mb_strrpos($filename, '.');
    if($dotPos !== false)
      $name = mb_substr($filename, 0, $dotPos);
    else
      $name = $filename;

    return $name;
  }
  static function GetFullName($filename) {
    $tmp = mb_strpos($filename, '?');
    if($tmp !== false)
      $filename = mb_substr ($filename, 0, $tmp);
    $filename = basename($filename);

    return $filename;
  }
  static public function FixPath($path){
    $path = mb_ereg_replace('[\\\/]+', '/', $path);
    return $path;
  }
  /**
   * creates unique file name using $filename( " - Copy " and number is added if file already exists) in directory $dir
   *
   * @param string $dir
   * @param string $filename
   * @return string
   */
  static function MakeUniqueFilename($dir, $filename){
    $temp = '';
    $dir .= '/';
    $dir = self::FixPath($dir.'/');
    $ext = self::GetExtension($filename);
    $name = self::GetName($filename);
    $name = self::CleanupFilename($name);
    $name = mb_ereg_replace(' \\- Copy \\d+$', '', $name);
    if($ext)
      $ext = '.'.$ext;
    if(!$name)
      $name = 'file';

    $i = 0;
    do{
      $temp = ($i > 0? $name." - Copy $i": $name).$ext;
      $i++;
    }while(file_exists($dir.$temp));

    return $temp;
  }
  /**
   * creates unique directory name using $name( " - Copy " and number is added if directory already exists) in directory $dir
   *
   * @param string $dir
   * @param string $name
   * @return string
   */
  static function MakeUniqueDirname($dir, $name){
    $temp = '';
    $dir = self::FixPath($dir.'/');
    $name = mb_ereg_replace(' - Copy \\d+$', '', $name);
    if(!$name)
      $name = 'directory';

    $i = 0;
    do{
      $temp = ($i? $name." - Copy $i": $name);
      $i++;
    }while(is_dir($dir.$temp));

    return $temp;
  }
}
class RoxyImage{
  public static function GetImage($path){
    $img = null;
    switch(RoxyFile::GetExtension(basename($path))){
      case 'png':
        $img = imagecreatefrompng($path);
        break;
      case 'gif':
        $img = imagecreatefromgif($path);
        break;
      default:
        $img = imagecreatefromjpeg($path);
    }
    return $img;
  }
  public static function OutputImage($img, $type, $destination = '', $quality = 90){
    if(is_string($img))
      $img = self::GetImage ($img);
    switch(strtolower($type)){
      case 'png':
        imagepng($img, $destination);
        break;
      case 'gif':
        imagegif($img, $destination);
        break;
      default:
        imagejpeg($img, $destination, $quality);
    }
  }
  public static function Resize($source, $destination, $width = '150',$height = 0, $quality = 90) {
    $tmp = getimagesize($source);
    $w = $tmp[0];
    $h = $tmp[1];
    $r = $w / $h;

    if($w <= ($width + 1) && (($h <= ($height + 1)) || (!$height && !$width))){
      if($source != $destination)
        self::OutputImage($source, RoxyFile::GetExtension(basename($source)), $destination, $quality);
      return;
    }
    
    $newWidth = $width;
    $newHeight = floor($newWidth / $r);
    if(($height > 0 && $newHeight > $height) || !$width){
      $newHeight = $height;
      $newWidth = intval($newHeight * $r);
    }

    $thumbImg = imagecreatetruecolor($newWidth, $newHeight);
    $img = self::GetImage($source);
    imagecopyresampled($thumbImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $w, $h);

    self::OutputImage($thumbImg, RoxyFile::GetExtension(basename($source)), $destination, $quality);
  }
  public static function CropCenter($source, $destination, $width, $height, $quality = 90) {
    $tmp = getimagesize($source);
    $w = $tmp[0];
    $h = $tmp[1];
    if(($w <= $width) && (!$height || ($h <= $height))){
      self::OutputImage(self::GetImage($source), RoxyFile::GetExtension(basename($source)), $destination, $quality);
    }
    $ratio = $width / $height;
    $top = $left = 0;

    $cropWidth = floor($h * $ratio);
    $cropHeight = floor($cropWidth / $ratio);
    if($cropWidth > $w){
      $cropWidth = $w;
      $cropHeight = $w / $ratio;
    }
    if($cropHeight > $h){
      $cropHeight = $h;
      $cropWidth = $h * $ratio;
    }

    if($cropWidth < $w){
       $left = floor(($w - $cropWidth) / 2);
    }
    if($cropHeight < $h){
       $top = floor(($h- $cropHeight) / 2);
    }

    self::Crop($source, $destination, $left, $top, $cropWidth, $cropHeight, $width, $height, $quality);
  }
  public static function Crop($source, $destination, $x, $y, $cropWidth, $cropHeight, $width, $height, $quality = 90) {
    $thumbImg = imagecreatetruecolor($width, $height);
    $img = self::GetImage($source);
    imagecopyresampled($thumbImg, $img, 0, 0, $x, $y, $width, $height, $cropWidth, $cropHeight);

    self::OutputImage($thumbImg, RoxyFile::GetExtension(basename($source)), $destination, $quality);
  }
}
$tmp = json_decode(file_get_contents(BASE_PATH.'/conf.json'), true);
if($tmp){
  foreach ($tmp as $k=>$v)
    define($k, $v);
}
else
  die('Error parsing configuration');
$FilesRoot = fixPath(getFilesPath());
if(!is_dir($FilesRoot))
  @mkdir($FilesRoot, octdec(DIRPERMISSIONS));
?>