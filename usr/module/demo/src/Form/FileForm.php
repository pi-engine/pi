<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class FileForm extends BaseForm
{
    public function init()
    {
        $this->add(array(
            'name'          => 'upload_file',
            'type'          => 'file',
            'options'   => array(
                'label' => __('File'),
            ),
        ));

        $this->add(array(
            'name'      => 'rename',
            'type'      => 'radio',
            'options'   => array(
                'label' => __('File naming'),
                'value_options'   => array(
                    'overwrite' => __('Keep name and overwrite when duplicated'),
                    'random'    => __('Rename to random'),
                ),
            ),
            'attributes'    => array(
                'value' => 'random',
            )
        ));

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

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Upload'),
            )
        ));
    }
}
