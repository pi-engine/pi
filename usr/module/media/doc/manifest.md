# 1 Feature List
![feature list](https://f.cloud.github.com/assets/2087430/1780453/28a8c5b0-6875-11e3-81e3-bfbe2773b950.gif)

# 2 Development Design of Media Service

## 2.1 Architecture of media service and its application
![system architecture](https://f.cloud.github.com/assets/2087430/1779898/dd135e0e-685a-11e3-98f2-bc16896f7455.gif)

## 2.2 Way to use media service
![upload schema](https://f.cloud.github.com/assets/2087430/1779899/dd14efc6-685a-11e3-99ed-b940612a6153.gif)

## 2.3 Service APIs
* `upload($meta, $options = array())`
  * `$meta`
    * `uid`: user id
    * `title`:
    * `path`: logical or absolute path to file
    * `description`:
  * `$options`
    * `dir`: folder of the file
    * `types`: allowed file format

* `update($id, $data)`
  * `$id`: file id
  * `$data`
    * `title`: file title
    * `description`: file description

* `activeFile($id)`
  * `$id`: file id

* `deactivateFile($id)`
  * `$id`: file id

* `getAttributes($id,$attribute)`
  * `$id`: file id
  * `$attribute`: name of attribute
  
* `getStatistics($id,$statistics)`
  * `$id`: file id
  * `$attribute`: name of statistics

* `getFileIds($condition, $limit, $offset, $order)`
  * `$condition`: condition
    * `category`: file category
    * `mini_type`: file mini_type, extension
    * `title`: file title
    * `description`: file description
    * `size`: file size
    * `uid`: uploader
    * `time_upload`: uploaded time
  * `$limit`: list limitation
  * `$offset`: list offset
  * `$order`: order

* `getList($condition, $limit, $offset, $order)`
  * `$condition`: condition
  * `$limit`: list limitation
  * `$offset`: list offset
  * `$order`: order

* `getCount($condition)`
  * `$condition`: condition

* `getUrl($id)`
  * `$id`: file id

* `download($id)`
  * `$id`: file id
    
* `delete($id)`
  * `$id`: file id

* `getValidator($adapter = null)`
  * `$adapter`: adapter

* `getAdapter()` and `setAdapter()`

# 3 Development Design of Media Module

## 3.1 File attributes

### Basic attributes
* Raw title
* Title defined by uploader
* Name generate by module
* Description
* mimeType
* File size
* Image width
* Image height
* URL
* Application belonged
* Module
* Module category
* Uploader
* Uploader IP
* Uploaded time
* Update time

### Extended attributes

### Statistics attributes
* Fetch count

## 3.2 File saving mode
![storage](https://f.cloud.github.com/assets/2087430/1779897/dccdb28c-685a-11e3-98c9-d6a693c76189.gif)

## 3.3 Controller & Action
* ~~Front~~
  * ~~UploadController~~
    * uploadAction
      * Description: verify uploaded file
      * Template: none
    * removeAction
      * Description: remove uploaded file
      * Template: none
    * syncAction (Client module only)
      * Description: synchronize client media to server
      * Template: none
  * ~~MediaController~~
    * saveAction
      * Description: save a file
      * Template: none
    * addAction
      * Description: add a file
      * Template: media-edit.phtml
    * editAction
      * Description: edit a file
      * Template: media-edit.phtml
    * deleteAction
      * Description: delete media
      * Template: none
  * ~~ListController~~
    * indexAction
      * Description: list all active/deactivate media
      * Template: list-index.phtml
    * categoryAction
      * Description: list media of a category
      * Template: list-category.phtml
    * moduleAction
      * Description: list media from a module
      * Template: list-module.phtml
    * userAction
      * Description: list media of a user
      * Template: list-user.phtml
  * DownloadController
    * indexAction
      * Description: download files
      * Template: none
  * ~~DetailController~~
    * imageAction
      * Description: list image details
      * Template: detail-image.phtml
    * documentAction
      * Description: list document details
      * Template: detail-document.phtml
    * indexAction
      * Description: list details of other type file
      * Template: detail-index.phtml
    * videoAction
      * Description: list video details
      * Template: detail-video.phtml
* Admin
  * ApplicationController
    * editAction
      * Description: edit application alias
      * Template: application-edit.phtml
    * deleteAction
      * Description: delete an application
      * Template: none
    * listAction
      * Description: list all application
      * Template: application-list.phtml
  * MediaController
    * editAction
      * Description: edit a file
      * Template: media-edit.phtml
    * deleteAction
      * Description: delete media
      * Template: none
    * activeAction
      * Description: active/deactivate media
      * Template: none
  * ListController
    * indexAction
      * Description: list all active/deactivate media
      * Template: list-index.phtml
    * typeAction
      * Description: list media of a media type
      * Template: list-category.phtml
    * userAction
      * Description: list media of a user
      * Template: list-user.phtml
    * applicationAction
      * Description: list media of an application
      * Template: list-application.phtml
  * ~~AnalysisController~~
    * indexAction
      * Description: get statistics data of media
      * Template: analysis-index.phtml

## 3.4 APIs for other module and remote calling
* The same as APIs of media service

## 3.5 Database
* my_media_detail
  * Storing media details

  |Name|Type|Length|Default value|Description|
  |:--:|:--:|:----:|:-----------:|:---------:|
  |id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |name|varchar|255|' '|Not null|
  |title|varchar|255|' '|Not null|
  |raw_name|varchar|255|' '|Not null|
  |mimetype|varchar|64|' '|Not null|
  |description|varchar|255|' '|Not null|
  |url|varchar|255|' '|Not null|
  |filesize|unsigned int|10|0|Not null|
  |size_width|unsigned int|10|0|Not null|
  |size_height|unsigned int|10|0|Not null|
  |uid|unsigned int|10|0|Not null|
  |ip|varchar|64|0|Not null|
  |time_upload|unsigned int|10|0|Not null|
  |time_update|unsigned int|10|0|Not null|
  |meta|text| |null| |
  |module|varchar|64|' '|Not null|
  |application|unsigned int|10|0|Not null|
  |category|unsigned int|10|0|Not null|
  |delete|tinyint|1|0|Not null|
  |active|tinyint|1|1|Not null|

* my_media_extended
  * Storing media extended attributes

  |Name|Type|Length|Default value|Description|
  |:--:|:--:|:----:|:-----------:|:---------:|
  |id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |media|unsigned int|10|0|Not null|

* my_media_statistics
  * Storing media statistics data

  |Name|Type|Length|Default value|Description|
  |:--:|:--:|:----:|:-----------:|:---------:|
  |id|unsigned int|10|NULL|Unique, Not null|
  |media|unsigned int|10|0|Not null|
  |fetch_count|unsigned int|10|0|Not null|

* my_media_application
  * Storing media application data
  
  |Name|Type|Length|Default value|Description|
  |:--:|:--:|:----:|:-----------:|:---------:|
  |id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |appkey|varchar|255|' '|Not null|
  |name|varchar|255|' '|Not null|
  |title|varchar|255|' '|Not null|

* my_media_category
  * Storing media category in a module

  |Name|Type|Length|Default value|Description|
  |:--:|:--:|:----:|:-----------:|:---------:|
  |id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |module|varchar|64|' '|Not null|
  |name|varchar|64|' '|Not null|
  |title|varchar|255|' '|Not null|
  |active|tinyint|1|1|Not null|
