<?php

namespace common\components;

use Alchemy\Zippy\Zippy;
use yii\helpers\FileHelper;
use Yii;

class FileManager
{

    public static function setFile($projectForm, $model)
    {
        if ($projectForm->file) {
            $zipName = Yii::$app->getSecurity()->generateRandomString();
            $model->file = $zipName . '.' . $projectForm->file->extension;
            $projectForm->file->saveAs(Yii::getAlias('@filePath') . '/' . $model->file);
        } else if ($projectForm->fileIndex) {
            $model->file = $projectForm->fileIndex->name;
        }
        return $model;
    }

    public static function searchFile($folderName)
    {
        $files = FileHelper::findFiles($folderName, ['only' => ['index.html'], 'recursive' => false]);
        return (bool)$files;
    }


    public function unpacking($projectModel, $model)
    {
        // Load Zippy
        $zippy = Zippy::load();
        // Open an archive
        $pathTree = $projectModel->getTree();
        $zipAdapter = $zippy->getAdapterFor('zip');
        if ($model->fileIndex) {
            return self::saveIndexFile($model, $pathTree);
        } else {
            $archive = $zipAdapter->open(Yii::getAlias('@filePath') . DIRECTORY_SEPARATOR . $projectModel->file);
            return self::saveFile($archive, $pathTree);
        }
    }

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
        $projectFolder = Yii::getAlias('@filePath') . DIRECTORY_SEPARATOR . $pathTree;
        FileHelper::createDirectory($projectFolder);
        $archive->extract($projectFolder);
        return $projectFolder;
    }

    public function updateArchive($model, $tree, $projectForm)
    {
        $ds = DIRECTORY_SEPARATOR;
        if ($projectForm->file) {
            $zippy = Zippy::load();
            $zipAdapter = $zippy->getAdapterFor('zip');
            $archive = $zipAdapter->open(Yii::getAlias('@filePath') . $ds . $model->file);
            $projectFolder = Yii::getAlias('@filePath') . $ds . $tree;
            $archive->extract($projectFolder);
            return $projectFolder;
        } else if ($projectForm->fileIndex) {
            $projectForm->fileIndex->saveAs(Yii::getAlias('@filePath') . $ds . $tree . $model->file);
            $projectFolder = Yii::getAlias('@filePath') . $ds . $tree;
            $projectRsc = Yii::getAlias('@filePath') . $ds . $tree  . 'rsc';
            if (!file_exists($projectRsc)) {
                FileHelper::createDirectory($projectRsc);
            }
            FileHelper::copyDirectory(Yii::getAlias('@rscPath'), $projectFolder  . 'rsc');
            return $projectFolder;
        }
        return false;
    }
}