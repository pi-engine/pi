Article Module 1.0.0 Release
============================

This is the first version of article module, it provides user complete function to create and publish articles, the feature list is show as follows:

### Author
* List/Add/Edit/Delete author
* Search author by name
* Add author photo
* Choose author photo from media

### Category
* List/Add/Edit/Delete category
* Merge/Move source category to target category
* Add category image / Choose category image from media

### Media
* List/Add/Edit/Delete media
* Batch delete/download media
* Search media by type and title
* Config media uploading limitation in module configuration section

### Topic
* Topic management
  * List/Add/Edit/Delete topic
  * Active/Deactivate topic
  * Add topic image / Choose topic image from media
  * Set custom template for topic
* Topic article management
  * List all articles belong to topic
  * Search articles of a topic
  * Remove/Batch remove articles from a topic
  * Pull/Batch pull articles to a topic
* topic browse
  * Browse homepage of a topic
  * Browse article list page of a topic
  * Browse all topics

### Article
* My article
  * List my published/draft/pending/rejected articles
  * Edit/Delete my published/draft/pending/rejected articles
  * Compose article
  * Add attachment or images from media in edit page
* All article
  * Edit/Approve pending articles
  * Edit/Active/Deactivate/Delete published articles
  * Search articles by title or category
* Article compose
  * Basic items: Subject/Subtitle/Summary/Category/Author/Source/Content
  * Extended items: Slug/SEO Title/SEO Keywords/SEO Description
  * Basic function: 
      * Edit publish time
      * Save/Submit/Approve draft
      * Preview draft
      * Add feature image
      * Batch add images and insert image into content
      * Batch add attachment and insert attachment into content
      * Search related articles by title
      * Add related articles
      * Segment content
* Browse article
  * Browse article homepage
  * Homepage can be dress up
  * Browse all articles list page
  * Browse articles of a category
  * Search articles by title

### Permission
* Add level to define resources permission in article
* List/Add/Edit/Delete level
* Active/Deactivate level
* Add user level to define user permission under categories
* List/Add/Edit/Delete user level

### Configuration
* Config the form to display in article edit page
  * Custom
  * Extended
  * Normal

### Statistics result browse
* Article count in a period group by category
* Ten top articles in period
* Max article count of submitter in period

### Blocks
* Top parent categories and their children list
* Recommended articles with slideshow
* Simple article search form
* Newest published articles or topic articles
* Hot published articles or topic articles
* Custom recommended articles
* Submitter statistics
* Some blocks allowed to choose template, the provided templates are:
  * Common article list
  * Article list with summary
  * Article list with feature
  * Subfield list
  * Article list with number rank

### API for Other Module
* Get compose URL - getComposeUrl()
* Get list of a submitter - getListBySubmitter(...)