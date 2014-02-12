# 1. System Architecture
[System architecture](https://github.com/pi-asset/image/blob/master/article/development/System%20Architecture.gif)

![system architecture](https://f.cloud.github.com/assets/2087430/921897/19dcaea4-ff0f-11e2-8b36-dfaf6388c550.gif)

# 2. Controller && Action
## Front
* IndexController
  * indexAction
      * Description: redirect to article homepage
      * Template: none
* ListController
  * allAction
      * Description: all article list page
      * Template: list-all.phtml
* TagController
  * listAction
      * Description: all article list related to tag
      * Template: tag-list.phtml
* SearchController
  * simpleAction
      * Description: searching articles
      * Template: search-simple.phtml
* DraftController
  * listAction
      * Description: listing articles with status such as draft, pending, rejected
      * POST: (draft | pending | rejected) && (my | all)
      * Template: draft-list.phtml
  * addAction
      * Description: adding a draft
      * Template: draft-add.phtml
  * editAction
      * Description: edit a draft
      * Template: draft-add.phtml
  * saveAction
      * Description: save a draft
      * Template: none
  * deleteAction
      * Description: delete a draft
      * Template: none
  * previewAction
      * Description: preview a draft
      * Template: draft-preview.phtml
  * publishAction
      * Description: publish a draft
      * Template: none
  * rejectAction
      * Description: reject a pending draft
      * Template: none
  * approveAction
      * Description: approve a pending draft
      * Template: none
  * updateAction
      * Description: update a draft of published article into article table
      * Template: none
  * batchApproveAction
      * Deacription: batch approve pending drafts
      * Template: none
* ArticleController
  * indexAction
      * Description: article homepage
      * Template: article-index.phtml
  * detailAction
      * Description: article detail page
      * Template: index-detail.phtml
  * publishedAction
      * Description: listing all or my published articles
      * POST: my | all
    * Template: article-published.phtml
  * editAction
      * Description: edit published article, the article will be copy to draft table, and it will redirect to editAction of DraftController to complete edit
      * Template: none
  * deleteAction
      * Description: delete a published article
      * Template: none
  * activeAction
      * Description: active/deactivate published article
      * Template: none
* MediaController
  * indexAction
      * Description: default media homepage, will redirect to list page
      * Template: none
  * listAction
     * Description: meida list page
     * Template: media-list.phtml
  * addAction
      * Description: add a media
      * Template: media-add.phtml
  * editAction
      * Description: edit a media
      * Template: media-list.phtml
  * searchAction
      * Description: search a media
      * Template: media-list.phtml
  * downloadAction
      * Description: download a media
      * Template: none
  * deleteAction
      * Description: delete a media
      * Template: none
* TopicController
  * indexAction
      * Description: homepage of a certain topic
      * Template: topic-index.phtml
  * listAction
      * Description: article list page of a certain topic
      * Template: index-topic-list.phtml
  * addAction 
      * Description: add a new topic 
      * Template:topic-edit.phtml
  * editAction
      * Description: edit a topic 
      * Template:topic-edit.phtml
  * deleteAction 
      * Description: delete a topic 
      * Template: none 
  * listTopicAction 
      * Description: list topics
      * Template:topic-list-topic.phtml 
  * listArticleAction 
      * Description: list articles belong to topic
      * Template:topic-list-article.phtml 
  * pullArticleAction 
      * Description: pull articles to topic
      * Template: none
  * pullAction
      * Description: list all articles for pull to topic
      * Template: topic-pull.phtml
  * removePullAction
      * Description: remove a article from topic
      * Template: none
  * activeAction 
      * Description: active/deactivate topic
      * Template: none
* AuthorController
  * ...
* CategoryController
  * ...
  * listAction
      * Description: list articles related to a category
      * Template: category-list.phtml
  * listCategoryAction
      * Description: list all categories
      * Template: category-list-category.phtml
## Admin 
* SetupController
  * formAction
      * Description: config form of draft add/edit page
      * Template: config-form.phtml
* PermissionController
  * indexAction
      * Description: permission homepage, redirect to user level list page
      * Template: none
  * addLevelAction
      * Description: add level
      * Template: permission-edit-level.phtml
  * editLevelAction
      * Description: edit level
      * Template: permission-edit-level.phtml
  * deleteLevelAction
      * Description: delete level
      * Template: none
  * listLevelAction
      * Description: list levels
      * Template: permission-list-level.phtml
  * activeAction
      * Description: active/deactivate level
      * Template: none
  * addAction
      * Description: add user level
      * Template: permission-add.phtml
  * editAction
      * Description: edit user level
      * Template: permission-add.phtml
  * deleteAction
      * Description: delete user level
      * Template: none
  * listAction
      * Description: list user levels
      * Template: permission-list.phtml
* StatisticsController
  * indexAction
      * Description: list statistics result
      * Template: statistics-index.phtml
# 3. Blocks
* Category list
  * Description: providing category list
  * Configuration:
      * sub-category
          * Type: text
          * Default value: 2
      * default-category
          * Type: text
          * Default value: None
* List newest article list
  * Description: can list article as follows
      * Newest all article of all topic
      * Newest all published articles
      * Newest published article of a category
      * Newest published article of a topic
  * Configuration
      * list-count
          * Type: text
          * Default value: 10
      * is-topic
          * Type: checkbox
          * Default value: 0
      * category
          * Type: select
          * Default value: none
      * topic
          * Type: select
          * Default value: none
      * block-style
          * Type: select
          * Value: normal/with summary/with featured image
          * Default value: normal
      * target
          * Type: select
          * Default value: _blank
      * max_subject_length
          * Type: text
          * Default value: 80
* Recommended article list
  * Description: Listing recommended articles of topic or non-topic
  * Configuration
      * list-count
          * Type: text
          * Default value: 10
      * is-topic
          * Type: checkbox
          * Default value: 0
      * block-style
          * Type: select
          * Value: normal/with summary/with featured image
          * Default value: normal
      * target
          * Type: select
          * Default value: _blank
      * max_subject_length
          * Type: text
          * Default value: 80
* Recommended article list with slideshow
  * Description: Left side is a slideshow and right side is recommended article list
  * Configuration
      * list-count
          * Type: text
          * Default value: 10
      * is-topic
          * Type: checkbox
          * Default value: 0
      * block-style
          * Type: select
          * Value: normal/with summary/with featured image
          * Default value: normal
      * images
          * Type: text
          * Default value: image/default-recommended.png
      * target
          * Type: select
          * Default value: _blank
      * max_subject_length
          * Type: text
          * Default value: 80
* Custom article list
  * Description: for users to add recommended article by article IDs
      * articles
          * Type: text
          * Default value: 0
      * target
          * Type: select
          * Default value: _blank
      * max_subject_length
          * Type: text
          * Default value: 80
* Article count statistics of submitter
  * Description: statistics include 7 days, 30 days and historical
  * Configuration
      * list-count
          * Type: text
          * Default value: 10
* Topic list
  * Description: provided newest topics
  * Configuration
      * list-count
          * Type: text
          * Default value: 10
* Hot article
  * Description: provide hot articles of topic or non-topic
  * Configuration
      * list-count
          * Type: text
          * Default value: 10
      * is-topic
          * Type: radio
          * Default value: 0
      * topic
          * Type: select
          * Default value: none
      * target
          * Type: select
          * Default value: _blank
      * max_subject_length
          * Type: text
          * Default value: 80
# 4. APIs
## Module APIs
* getComposeUrl()
  * Path: src/Api.php
  * Call: Pi::service('api')->article->getComposeUrl();
  * Description: return a url of draft add/edit page
  * Parameter:     none
  * Return value:   string
* getList($submitter = null, $count = 10, $options = array())
  * Path: src/Api.php
  * Call: Pi::service('api')->article->getList();
  * Description: return article list by condition
  * Parameter:     
      * $submitter: submitter
      * $count: count of article
      * $options: other condition
  * Return value:   array
## Permission Service APIs
* getPermission($isMine = false, $operation = null, $category = null, $uid = null)
  * Path: src/Service.php
  * Call: Rule::getPermission();
  * Description: return permission by passed condition
  * Parameter:
      * $isMine: whether a user is operating is own article
      * $operation: resource or operation
      * $category: category
      * $uid: user ID
  * Return value: array('{Category ID}' => array('{Operation name}' => true));
* getModuleResourcePermission($resource, $uid = null)
  * Path: src/Service.php
  * Call: Rule::getModuleResourcePermission();
  * Description: return permission of resource defines in acl configuration
  * Parameter:
      * $resource: resource name
      * $uid: user ID
  * Return value: boolean

## Media APIs
Reference here: [Media APIs](https://github.com/pi-engine/pi/wiki/Service.media)
# 5. Main logic flow chart
## Assemble and match of custom route
[Router](https://github.com/pi-asset/image/blob/master/article/development/Router%28EN%29.gif)

![router en](https://f.cloud.github.com/assets/2087430/921896/19d6cc1e-ff0f-11e2-9ab3-8a9a427b01e4.gif)

## Fetching and adding article content
[Publish article](https://github.com/pi-asset/image/blob/master/article/development/Publish%20Article%28EN%29.gif)

![publish article en](https://f.cloud.github.com/assets/2087430/921895/19655124-ff0f-11e2-9a0c-0ff277e000fc.gif)

# 6. Database
* my_article_article
  * Saving published articles
  
  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Article subject|subject|varchar|255|' '|Not null|
  |Subtitle|subtitle|varchar|255|' '|Not null|
  |Summary|summary|text| |' '|Not null|
  |Content|content|longtext| |' '|Not null|
  |Original content type|markup|varchar|64|html|Not null|
  |Featured image|image|varchar|255|' '|Not null|
  |Submitter ID|uid|unsigned int|10|0|Not null|
  |Author|author|unsigned int|10|0|Not null|
  |Article source|source|varchar|255|' '|Not null|
  |Total pages|pages|tinyint|3|0|Not null|
  |Category|category|unsigned int|10|0|Not null|
  |Article status|status|tinyint|3|0|Not null|
  |Whether to active|active|tinyint|1|0|Not null|
  |Submit time|time_submit|unsigned int|10|0|Not null|
  |Published time|time_publish|unsigned int|10|0|Not null|
  |Updating time|time_update|unsigned int|10|0|Not null|
  |Updating user|user_update|unsigned int|10|0|Not null|

  * **Key**
      * uid
      * author
      * time_publish, category
      * time_submit, category
      * subject

* my_article_extended
  * Saving extended details of published article

  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Article ID|article|unsigned int|10|0|Not null|
  |SEO title|seo_title|varchar|255|' '|Not null|
  |SEO keywords|seo_keywords|varchar|255|' '|Not null|
  |SEO Description|seo_description|varchar|255|' '|Not null|
  |Slug|slug|varchar|255|NULL| |

  * **Unique key**
      * slug
      * article
  
* my_article_field
  * Saving extended fields add by user

  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Field|name|varchar|64|0|Not null|
  |Field title|title|varchar|255|' '|Not null|

* my_article_article_compiled
  * Saving compiled article content

  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Unique name|name|varchar|64|' '|Not null, consists of article ID and type|
  |article ID|article|unsigned int|10|0|Not null|
  |Destination compiled type|type|varchar|64|' '|Not null|
  |Article content|content|longtext| |' '|Not null|

  * **Unique key**
      * name
  * **Key**
      * article, type

* my_article_draft
  * Saving un-published article
  
  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Article ID|article|unsigned int|10|0|Not null|
  |Article details|detail|longtext| |' '|Not null|
  |Original content type|markup|varchar|64|html|Not null|
  |Submitter ID|uid|unsigned int|10|0|Not null|
  |Author ID|author|unsigned int|10|0|Not null|
  |Category ID|category|unsigned int|10|0|Not null|
  |Draft status|status|tinyint|3|0|Not null|
  |Submit time|time_submit|unsigned int|10|0|Not null|
  |Update time|time_update|unsigned int|10|0|Not null|
  |Published time|time_publish|unsigned int|10|0|Not null|
  |Save time|time_save|unsigned int|10|0|Not null|
  |Reject reason|reject_reason|varchar|255|' '|Not null|

  * **Key**
      * article
      * uid
      * time_save

* my_article_related
  * Saving related articles of a article
  
  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Article ID|article|unsigned int|10|0|Not null|
  |Related article ID|related|unsigned int|10|0|Not null|
  |Order|order|tinyint|3|0|Not null|
  
  * **Key**
      * article

* my_article_visit
  * Saving visit log
  
  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Article ID|article|unsigned int|10|0|Not null|
  |Visit time|time|unsigned int|10|0|Not null|
  |IP|ip|varchar|255|' '|Not null|
  |User ID|uid|unsigned int|10|0|Not null|
  
  * **Unique key**
      * article-time
  * **Key**
      * time

* my_article_category
  * Saving category
  
  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Left side|left|unsigned int|10|0|Not null|
  |Right side|right|unsigned int|10|0|Not null|
  |Depth|depth|unsigned int|10|0|Not null|
  |Unique name|name|varchar|64|' '|Not null|
  |Slug|slug|varchar|64|NULL| |
  |Title for display|title|varchar|64|' '|Not null|
  |Description|description|varchar|255|' '|Not null|
  |Image|image|varchar|255|' '|Not null|
  
  * **Unique key**
      * name
      * slug

* my_article_author
  * Saving author data
  
  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Unique name|name|varchar|64|' '|Not null|
  |Description|description|text| |' '|Not null|
  |Photo|photo|varchar|255|' '|Not null|
  
  * **Unique key**
      * name

* my_article_statistics
  * Saving statistics data of articles
  
  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Article ID|article|unsigned int|10|0|Not null|
  |Total visit count|visits|unsigned int|10|0|Not null|
  
  * **Unique key**
      * article
  * Key
      * article, visits

* my_article_media
  * Saving media details

  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Unique name|name|varchar|255|' '|Not null|
  |Title|title|varchar|255|' '|Not null|
  |Format|type|varchar|64|' '|Not null|
  |Description|description|varchar|255|' '|Not null|
  |URL|url|varchar|255|' '|Not null|
  |Size|filesize|unsigned int|10|0|Not null|
  |Submitter|submitter|varchar|255|0|Not null|
  |Uploaded time|time_upload|unsigned int|10|0|Not null|
  |Updated time|time_update|unsigned int|10|0|Not null|

  * **Unique key**
      * name
  * **Key**
      * type
      * submitter
      * time_upload

* my_article_media_statistics
  * Saving statistics data of media

  |Description|Field|Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, Not null|
  |Refer count by article|num_article_refer|tinyint|3|0|Not null|
  |Last use time|time_lastused|unsigned int|10|0|Not null|
  |Download count|num_download|tinyint|3|0|Not null|

  * **Key**
      * id
      * time_lastused

* my_article_topic
  * Saving topics

  |Description|Field |Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id     |unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Unique name|name  |varchar|64|' '|Not null|
  |Topic content|content|text| |' '|Not null|
  |Title|title|varchar|255|' '|Not null|
  |Image|image|varchar|255|' '|Not null|
  |Slug|slug|varchar|64|NULL| |
  |Template|template|varchar|64|' '|Not null|
  |Description|description|varchar|255|' '|Not null|
  |Active|active|tinyint|1|1|Not null|

  * **Unique key**
      * name
      * slug

* my_article_article_topic
  * Saving relation between article and topic

  |Description|Field |Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id     |unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Article ID|article|unsigned int|10|0|Not null|
  |Topic ID|topic|unsigned int|10|0|Not null|

  * **Key**
      * topic
      * article

* my_article_level
  * Saving levels
  
  |Description|Field |Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id     |unsigned int|10|NULL|Unique, Not null, auto_increment|
  |Unique name|name  |varchar|64|' '|Not null|
  |Title|title|varchar|255|' '|Not null|
  |Created time|time_create|unsigned int|10|0|Not null|
  |Updated time|time_update|unsigned int|10|0|Not null|
  |Description|description|varchar|255|' '|Not null|
  |Active|active|tinyint|1|1|Not null|

  * **Unique key**
      * name
  * **Key**
      * active

* my_article_user_level
  * Saving relation among user, category and level
 
  |Description|Field |Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id     |unsigned int|10|NULL|Unique, Not null, auto_increment|
  |User ID|uid  |unsigned int|10|0|Not null|
  |Category IDs|category|varchar|255|' '|Not null|
  |Level ID|level|unsigned int|10|0|Not null|

* my_article_asset
  * Saving relationship between article and media
 
  |Description|Field |Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, not null, auto_increment|
  |Media ID|media|unsigned int|10|0|Not null|
  |Article ID|article|unsigned int|10|0|Not null|
  |Type|type|enum('attachment', 'image')| |attachment|Not null|

  * **Unique key**
      * media-article
  * **Key**
      * article, type
      * media

* my_article_asset_draft
  * Saving relationship between media and draft
 
  |Description|Field |Type|Length|Default value|More|
  |:--:|:--:|:----:|:-----------:|:---------:|:--:|
  |Unique ID|id|unsigned int|10|NULL|Unique, not null, auto_increment|
  |Media ID|media|unsigned int|10|0|Not null|
  |Draft ID|draft|varchar|255|0|Not null|
  |Type|type|enum('attachment', 'image')| |attachment|Not null|

  * **Key**
      * draft, type

# 7. System Component
* Comment
* Message
* Event
* User
* Timeline
* Activity
* Media

# 8. Configuration

## Autosave
* autosave_interval
  * Description: Time interval when enable auto saving draft, if its value set to 0, auto saving is disabled
  * Type: text
  * Default value: 5

## General
* page_limit_front
  * Description: article count limitation per page of front-end list page
  * Type: text
  * Default value: 40
* page_limit_topic
  * Description: article count limitation per page of front-end topic article list page
  * Type: text
  * Default value: 40
* page_limit_management
  * Description: article count limitation per page of management page
  * Type: text
  * Default value: 20
* enable_tag
  * Description: Whether to enable tag
  * Type: checkbox
  * Default value: 1
* default_source
  * Description: Default article source
  * Type: text
  * Default value: Pi
* default_category
  * Description: Default category
  * Type: select
  * Default value: 2
* max_related
  * Description: Max related article count
  * Type: text
  * Default value: 5

## Summary
* enable_summary
  * Description: Whether to enable summary
  * Type: checkbox
  * Default value: 1
* max_summary_length
  * Description: max summary length allowed
  * Type: text
  * Default value: 255
* max_subject_length
  * Description: max subject length allowed
  * Type: text
  * Default value: 255
* max_subtitle_length
  * Description: max subtitle length allowed
  * Type: text
  * Default value: 60

## Media
* path_media
  * Description: media default path
  * Type: text
  * Default value: upload/article/media
* sub_dir_pattern
  * Description: directory format
  * Type: select
  * Default value: Y/m/d
* image_extension
  * Description: image extension allowed
  * Type: text
  * Default value: jpg,png,gif
* doc_extension
  * Description: doc extension allowed
  * Type: text
  * Default value: txt,doc,pdf
* max_image_size
  * Description: max image size allowed
  * Type: text
  * Default value: 2M
* max_doc_size
  * Description: max doc size allowed
  * Type: text
  * Default value: 2M
* image_width
  * Description: max image width allowed
  * Type: text
  * Default value: 540
* image_height
  * Description: max image height allowed
  * Type: text
  * Default value: 460
* path_author
  * Description: default author photo path
  * Type: text
  * Default value: upload/article/author
* path_category
  * Description: default category image path
  * Type: text
  * Default value: upload/article/category
* path_feature
  * Description: default featured image path
  * Type: text
  * Default value: upload/article/feature
* author_width
  * Description: author photo width
  * Type: text
  * Default value: 110
* author_height
  * Description: author photo height
  * Type: text
  * Default value: 110
* default_author_photo
  * Description: default author photo
  * Type: text
  * Default value: image/default-author.png
* category_width
  * Description: category image width
  * Type: text
  * Default value: 40
* category_height
  * Description: category image height
  * Type: text
  * Default value: 40
* default_category_image
  * Description: default category image
  * Type: text
  * Default value: image/default-category.png
* feature_width
  * Description: featured image width
  * Type: text
  * Default value: 440
* feature_height
  * Description: featured image height
  * Type: text
  * Default value: 300
* default_feature_image
  * Description: default featured image
  * Type: text
  * Default value: image/default-feature.png