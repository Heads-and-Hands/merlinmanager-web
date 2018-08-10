<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.08.2018
 * Time: 15:25
 */

namespace backend\models;

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