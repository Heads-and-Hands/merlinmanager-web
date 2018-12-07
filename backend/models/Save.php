<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.08.2018
 * Time: 11:25
 */

namespace backend\models;

use Yii;
use yii\helpers\FileHelper;

class Save
{
    public static function saveIndexFile($model, $pathTree)
    {
        $projectFolder = Yii::getAlias('@filePath') . '/' . $pathTree;
        $projectRsc = Yii::getAlias('@filePath') . '/' . $pathTree . '/rsc';
        FileHelper::createDirectory($projectFolder);
        $model->fileIndex->saveAs($projectFolder . '/' . $model->fileIndex->name);
        FileHelper::createDirectory($projectRsc);
        FileHelper::copyDirectory(Yii::getAlias('@rscPath'), $projectFolder . '/rsc');
        return $projectFolder;
    }

    public static function saveFile($archive, $pathTree)
    {
        $projectFolder = Yii::getAlias('@filePath') . DIRECTORY_SEPARATOR. $pathTree;
        FileHelper::createDirectory($projectFolder);
        $archive->extract($projectFolder);
        return $projectFolder;
    }
}


