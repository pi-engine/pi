<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Form;

use Pi\Form\Form as BaseForm;

class FileForm extends BaseForm
{
    public function init()
    {
        $this->add([
            'name'    => 'upload_file',
            'type'    => 'file',
            'options' => [
                'label' => __('File'),
            ],
        ]);

        $this->add([
            'name'       => 'rename',
            'type'       => 'radio',
            'options'    => [
                'label'         => __('File naming'),
                'value_options' => [
                    'overwrite' => __('Keep name and overwrite when duplicated'),
                    'random'    => __('Rename to random'),
                ],
            ],
            'attributes' => [
                'value' => 'random',
            ],
        ]);

        /*
        $this->add(array(
            'name'          => 'filename',
            'options'       => array(
                'label' => __('File name'),
            ),
            'attributes'    => array(
                'description' => __('Specified filename w/o extension.'),
            )
        ));
        */

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Upload'),
            ],
        ]);
    }
}
