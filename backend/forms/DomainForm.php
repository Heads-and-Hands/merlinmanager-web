<?php

namespace backend\forms;

use yii\base\Model;

class DomainForm  extends Model
{
    public $domain;
    public $file;
    public $id;

    public function rules()
    {
        return [
            [['domain'],'url'],
            [['file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'zip'],
        ];
    }
}