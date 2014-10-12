<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Article custom attributes
 * 
 * Common fields: fields that exist in article table
 * <code>
 * 'field' => array(
 *     // Case 1
 *     'field1' => array(
 *         'type' => 'common',
 *         ...
 *     ),
 *     // Case 2
 *     'field2' => array(
 *         // If there is no `field` key, it means the element is not compound item,
 *         // therefore, `type` key can be ignored
 *         ...
 *     ),
 * ),
 * </code>
 * 
 * Custom fields: fields that can be operated in edit page but not in article table
 * E.G. `tag` field can be operated in edit page, but its relation table is in tag module.
 * 
 * <code>
 * 'field' => array(
 *     'field1' => array(
 *         // The type field must be indicated, or else the field will be treated as common field
 *         'type' => 'custom',
 *         ...
 *     ),
 * ),
 * <code>
 * 
 * Compound fields
 * <code>
 * 'field' => array(
 *     // Case 1
 *     'field1' => array(
 *         'type' => 'compound',
 *         ...
 *     ),
 *     // Case 2
 *     'field2' => array(
 *         'field' => array(
 *             ...
 *         ),
 *     ),
 * ),
 * </code>
 * 
 * Form element definition
 * <code>
 * // Define edit type directly by string
 * 'edit' => 'text',
 * // Use array to define
 * 'edit' => array(
 *     'element' => 'text',
 *     ...
 * ),
 * 'edit' => array(
 *      // Without `element` field
 *     'required' => true,
 *     ...
 * ),
 * </code>
 * All edit above will be resolved as 
 * 'edit' => array(
 *     'element' => array(
 *         'type' => 'text',
 *     ),
 *     ...
 * ),
 * 
 * Is required
 * <code>
 * // Case 1, the value of `is_required` field in database will be true
 * 'edit' => array(
 *     'required' => true,
 * ),
 * 'is_required' => false,
 * // Case 2, the value of `is_required` will be true
 * 'edit' => array(
 *     'required' => true,
 * ),
 * // Case 3, the value of `is_required` will be false
 * 'edit' => array(
 *     
 * ),
 * 'is_required' => false,
 * </code>
 * 
 * Table column type for non compound but custom fields
 * <code>
 * // Default as text if the key not set or set to empty string
 * 'field_type' => '',
 * // int
 * 'field_type' => 'int(10) unsigned not null default \'0\',
 * </code>
 * 
 * Media form
 * @see Module\Article\Form\Element\Media
 * <code>
 * 'edit' => array(
 *     'element' => array(
 *         'type' => 'Module\Article\Form\Element\Media',
 *     ),
 * ),
 * </code>
 * 
 * Filter for format form data by using $form->getData()
 * <code>
 * 'filter' => 'Module\Article\Form\Filter\Test',
 * </code>
 * Note:
 * If the form instance of `getInputSpecification()` method, this config is not support.
 *
 * @see Module\Article\Installer\Resource\Article
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    // Fields
    'field'     => array(

        // Attributes in `article` table
        'subject'        => array(
            'type'       => 'common',
            'name'       => 'subject',
            'title'      => _a('Subject'),
            'edit'       => array(
                'required' => true,
                // Custom form element, this allows custom form template
                'element'  => 'Module\Article\Form\Element\Subject',
                'validators' => array(
                    array(
                        'name'  => 'Module\Article\Validator\RepeatSubject',
                    ),
                    'callback' => 'Module\Article\Form\CommonValidators::subjectValidator',
                ),
            ),
            // Is editable by admin, default as true
            'is_edit'    => true,
            // Do not insert this column to article table
            'is_insert'  => false,
        ),
        'subtitle'       => array(
            'type'       => 'common',
            'name'       => 'subtitle',
            'title'      => _a('Subtitle'),
            'edit'       => array(
                'required' => false,
                'element'  => 'Module\Article\Form\Element\Subtitle',
                'validators' => array(
                    'callback' => 'Module\Article\Form\CommonValidators::subtitleValidator',
                ),
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'summary'        => array(
            'type'       => 'common',
            'name'       => 'summary',
            'title'      => _a('Summary'),
            'edit'       => array(
                'required' => false,
                'element'  => 'Module\Article\Form\Element\Summary',
                'filters'  => array(),
                'validators' => array(
                    'callback' => 'Module\Article\Form\CommonValidators::summaryValidator',
                ),
            ),
            // Custom filter for formatting value post by form
            'filter'     => 'Module\Article\Form\Filter\Summary',
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'content'        => array(
            'type'       => 'common',
            'name'       => 'content',
            'title'      => _a('Content'),
            'edit'       => array(
                'required' => false,
                'element'  => 'Module\Article\Form\Element\Content',
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'markup'         => array(
            'type'       => 'common',
            'name'       => 'markup',
            'title'      => _a('Markup'),
            'edit'       => array(
                'required' => false,
            ),
            'is_edit'    => false,
            'is_display' => false,
            'is_insert'  => false,
        ),
        'image'          => array(
            'type'       => 'common',
            'name'       => 'image',
            'title'      => _a('Image'),
            'edit'       => array(
                'required' => false,
                'element'  => 'Module\Article\Form\Element\FeatureImage',
                'options'  => array(
                    'preview'   => array(
                        'width'     => 80,
                        'height'    => 60,
                    ),
                    'type'       => 'image',
                    'to_session' => true,
                ),
                'attributes' => array(
                    'id'        => 'featured',
                ),
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'gallery'        => array(
            'type'       => 'custom',
            'name'       => 'gallery',
            'title'      => _a('Gallery'),
            'edit'       => array(
                'required' => false,
                'element'  => 'Module\Article\Form\Element\Media',
                'options'  => array(
                    'preview'   => array(
                        'width'     => 80,
                        'height'    => 60,
                    ),
                    'type'       => 'image',
                    'multiple'   => true,
                ),
                'attributes' => array(
                    'id'        => 'gallery',
                ),
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'attachment'     => array(
            'type'       => 'custom',
            'name'       => 'attachment',
            'title'      => _a('Attachment'),
            'edit'       => array(
                'required' => false,
                'element'  => 'Module\Article\Form\Element\Media',
                'options'  => array(
                    'preview'   => array(
                        'width'     => 120,
                        //'height'    => 60,
                    ),
                    'type'       => 'attachment',
                    'multiple'   => true,
                ),
                'attributes' => array(
                    'id'        => 'attachment',
                ),
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'uid'            => array(
            'type'       => 'common',
            'name'       => 'uid',
            'title'      => _a('Submitter'),
            'is_edit'    => false,
            'is_insert'  => false,
        ),
        'author'         => array(
            'type'       => 'common',
            'name'       => 'author',
            'title'      => _a('Author'),
            'edit'       => array(
                'element'  => 'Module\Article\Form\Element\Author',
                'filters'  => array(
                    array(
                        'name' => 'Int',
                    ),
                ),
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'source'         => array(
            'type'       => 'common',
            'name'       => 'source',
            'title'      => _a('Source'),
            'edit'       => 'text',
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'pages'          => array(
            'type'       => 'common',
            'name'       => 'pages',
            'title'      => _a('Pages'),
            'is_edit'    => false,
            'is_display' => false,
            'is_insert'  => false,
        ),
        'category'       => array(
            'type'       => 'common',
            'name'       => 'category',
            'title'      => _a('Category'),
            'edit'       => array(
                'element'  => 'Module\Article\Form\Element\Category',
            ),
            'is_edit'    => false,
            'is_insert'  => false,
        ),
        // Config that article only belong to one cluster
        /*'cluster'        => array(
            'type'       => 'custom',
            'name'       => 'cluster',
            'title'      => _a('Cluster'),
            'edit'       => array(
                'element'  => 'Module\Article\Form\Element\Cluster',
            ),
            'is_edit'    => false,
            'is_insert'  => false,
        ),*/
        // Article can belong to multi clusters
        'cluster'        => array(
            'type'       => 'custom',
            'name'       => 'cluster',
            'title'      => _a('Cluster'),
            'edit'       => array(
                'element'  => 'Module\Article\Form\Element\MultiCluster',
                'options'  => array(
                    'is_multiple' => true,
                ),
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'status'         => array(
            'type'       => 'common',
            'name'       => 'status',
            'title'      => _a('Status'),
            'is_edit'    => false,
            'is_display' => false,
            'is_insert'  => false,
        ),
        'active'         => array(
            'type'       => 'common',
            'name'       => 'active',
            'title'      => _a('Activate'),
            'is_edit'    => false,
            'is_display' => false,
            'is_insert'  => false,
        ),
        'time_submit'    => array(
            'type'       => 'common',
            'name'       => 'time_submit',
            'title'      => _a('Submit Time'),
            'is_edit'    => false,
            'is_display' => false,
            'is_insert'  => false,
        ),
        'time_publish'   => array(
            'type'       => 'common',
            'name'       => 'time_publish',
            'title'      => _a('Publish Time'),
            'edit'       => array(
                'element'  => 'Module\Article\Form\Element\TimePublish',
            ),
            'filter'     => 'Module\Article\Form\Filter\TimePublish',
            'is_edit'    => false,
            'is_display' => false,
            'is_insert'  => false,
        ),
        'time_update'    => array(
            'type'       => 'common',
            'name'       => 'time_update',
            'title'      => _a('Update Time'),
            'is_edit'    => false,
            'is_display' => false,
            'is_insert'  => false,
        ),
        'user_update'    => array(
            'type'       => 'common',
            'name'       => 'user_update',
            'title'      => _a('Update user ID'),
            'is_edit'    => false,
            'is_display' => false,
            'is_insert'  => false,
        ),
        'tag'            => array(
            'type'       => 'custom',
            'name'       => 'tag',
            'title'      => _a('Tag'),
            'edit'       => array(
                'required' => false,
                'element'  => 'tag',
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'seo_keywords'   => array(
            'type'       => 'common',
            'name'       => 'seo_keywords',
            'title'      => _a('Meta Keywords'),
            'edit'       => array(
                'required' => false,
                'element'  => 'text',
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        'seo_description' => array(
            'type'       => 'common',
            'name'       => 'seo_description',
            'title'      => _a('Meta Description'),
            'edit'       => array(
                'required' => false,
                'element'  => 'textarea',
                'attributes' => array(
                    'rows'      => 3,
                ),
            ),
            'is_edit'    => true,
            'is_insert'  => false,
        ),
        
        // Compound
        'related'        => array(
            'name'       => 'related',
            'title'      => _a('Related'),
            // Custom handler
            'handler'    => 'Module\Article\Field\Related',

            // Fields
            'field' => array(
                'related'    => array(
                    'title'      => _a('Related Article'),
                    'edit'       => array(
                        'element'  => 'Module\Article\Form\Element\Related\Related',
                    ),
                ),
            ),
        ),
        /*'seo_title'      => array(
            'type'       => 'common',
            'name'       => 'seo_title',
            'title'      => _a('SEO Title'),
            'is_edit'    => false,
            'is_insert'  => true,
            'field_type' => 'varchar(255) not null default \'\'',
        ),
        'seo_keywords'   => array(
            'type'       => 'common',
            'name'       => 'seo_keywords',
            'title'      => _a('SEO Keywords'),
            'is_edit'    => false,
            'is_insert'  => true,
        ),
        'seo_description' => array(
            'type'       => 'common',
            'name'       => 'seo_description',
            'title'      => _a('SEO Description'),
            'edit'       => array(
                'attributes' => array(
                    'id'        => 'seo_description',
                    'type'      => 'textarea',
                ),
            ),
            'is_edit'    => false,
            'is_insert'  => true,
        ),
        'slug'           => array(
            'type'       => 'common',
            'name'       => 'slug',
            'title'      => _a('Slug'),
            'is_edit'    => false,
            'is_insert'  => true,
        ),*/
    ),
);
