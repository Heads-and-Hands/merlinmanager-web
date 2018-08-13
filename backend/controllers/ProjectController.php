<?php

namespace backend\controllers;

use backend\models\ProjectForm;
use backend\models\ProjectSearch;
use Yii;
use backend\models\Project;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use Alchemy\Zippy\Zippy;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],

        ];
    }

    /**
     * Lists all Project models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->identity->isAdmin) {
            $dataProvider = new ActiveDataProvider([
                'query' => Project::find()->where(['user_id' => Yii::$app->user->identity->id]),
            ]);
        } else {
            $searchModel = new ProjectSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $projectList = Project::find()->all();
            $data = ArrayHelper::map($projectList, 'user.login', 'user.login');
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'data' => $data,
            ]);
        }
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Project model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function unpacking($projectModel, $model)
    {
        // Load Zippy
        $zippy = Zippy::load();
        // Open an archive
        $pathTree = $projectModel->getTree();
        $zipAdapter = $zippy->getAdapterFor('zip');
        if ($projectModel->file != 'index.html'){
            $archive = $zipAdapter->open(Yii::getAlias('@filePath') . '/' . $projectModel->file);
        }
        if ($projectModel->parent_id) {
            if ($model->fileIndex) {
                $projectFolder = Yii::getAlias('@filePath') . '/' . $pathTree;
                $projectRsc = Yii::getAlias('@filePath') . '/' . $pathTree . '/rsc';
                FileHelper::createDirectory($projectFolder);
                $model->fileIndex->saveAs($projectFolder . '/' . $model->fileIndex->name);
                FileHelper::createDirectory($projectRsc);
                FileHelper::copyDirectory(Yii::getAlias('@rscPath'),$projectFolder . '/rsc');
                return $projectFolder;
            } else {
                $projectFolder = Yii::getAlias('@filePath') . '/' . $pathTree;
                FileHelper::createDirectory($projectFolder);
                $archive->extract($projectFolder);
                return $projectFolder;
            }
        } else {
            if ($model->fileIndex) {
                $projectRsc = Yii::getAlias('@filePath') . '/' . $pathTree . '/rsc';
                $projectFolder = Yii::getAlias('@filePath') . '/' . $pathTree;
                FileHelper::createDirectory($projectFolder);
                $model->fileIndex->saveAs($projectFolder . '/' . $model->fileIndex->name);
                FileHelper::createDirectory($projectRsc);
                FileHelper::copyDirectory(Yii::getAlias('@rscPath'),$projectFolder . '/rsc');
                return $projectFolder;
            } else {
                $projectFolder = Yii::getAlias('@filePath') . '/' . $pathTree;
                FileHelper::createDirectory($projectFolder);
                $archive->extract($projectFolder);
                return $projectFolder;
            }

        }
    }

    public function delete($projectModel)
    {
        $projectModel->delete();
    }

    public function actionCreate()
    {
        $model = new ProjectForm();
        if (Yii::$app->user->identity->isAdmin) {
            $projectList = Project::find()->all();
        } else {
            $projectList = Project::find()->where(['user_id' => Yii::$app->user->identity->getId()])->all();
        }

        $data = ArrayHelper::map($projectList, 'id', 'name');

        if (!$model->load(Yii::$app->request->post())) {
            return $this->render('create', [
                'model' => $model,
                'data' => $data,
            ]);
        }

        $model->file = UploadedFile::getInstance($model, 'file');
        $model->fileIndex = UploadedFile::getInstance($model, 'fileIndex');

        if (!$model->validate()) {
            return $this->render('create', [
                'model' => $model,
                'data' => $data,
            ]);
        }

        $projectModel = new Project();
        $projectModel->user_id = Yii::$app->user->identity->getId();
        $projectModel->name = $model->name;
        $projectModel->parent_id = $model->parent_id;
        if ($model->file) {
            $zipName = Yii::$app->getSecurity()->generateRandomString();
            $projectModel->file = $zipName . '.' . $model->file->extension;
            $model->file->saveAs(Yii::getAlias('@filePath') . '/' . $projectModel->file);
        }else{
            $projectModel->file = $model->fileIndex->name;
        }

        if ($projectModel->save()) {

            $folderName = $this->unpacking($projectModel, $model);
            FileHelper::unlink(Yii::getAlias('@filePath') . '/' . $projectModel->file);
            $result = Project::searchFile($folderName);
            if (!$result) {
                $session = Yii::$app->session;
                // установка flash-сообщения с названием "projectDeleted"
                $session->setFlash('projectDeleted', Yii::t('content', 'index.html not found project not created'));
                $this->delete($projectModel);
                return $this->redirect(['create']);
            }
        }
        return $this->redirect(['view', 'id' => $projectModel->id]);
    }

    protected function findModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }


    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $projectForm = new ProjectForm();
        $projectForm->id = $model->id;
        if (!$projectForm->load(Yii::$app->request->post())) {
            $projectForm->name = $model->name;
            return $this->render('update', [
                'model' => $projectForm,
            ]);
        }

        $projectForm->file = UploadedFile::getInstance($projectForm, 'file');
        $projectForm->fileIndex = UploadedFile::getInstance($projectForm, 'fileIndex');

        if (!$projectForm->validate()) {
            $projectForm->name = $model->name;
            return $this->render('update', [
                'model' => $projectForm,
            ]);
        }

        if ($projectForm->file){
            $zipName = Yii::$app->getSecurity()->generateRandomString();
            $projectForm->file->saveAs(Yii::getAlias('@filePath') . '/' . $zipName . '.' . $projectForm->file->extension);
            $model->file = $zipName . '.' . $projectForm->file->extension;
        }else{
            $model->file = $projectForm->fileIndex->name;
        }

        $pathTree = $model->getTree();
        if ($projectForm->name != $model->name){
            $model->name = $projectForm->name;
            rename("./tmp/" . $pathTree, "./tmp/" . $model->getTree());
        }


        if (!$model->save()) {
            return $this->render('update', [
                'model' => $model,
            ]);
        }

        $tree = $model->getTree();
        $projectFolder = $this->updateArchive($model, $tree,$projectForm);
        $result = Project::searchFile($projectFolder);
        FileHelper::unlink(Yii::getAlias('@filePath') . '/' . $model->file);
        if (!$result) {
            $session = Yii::$app->session;
            // установка flash-сообщения с названием "projectDeleted"
            $session->setFlash('projectDeleted', Yii::t('content', 'Your project is not created!'));
            return $this->redirect(['update']);
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function updateArchive($model, $tree,$projectForm)
    {
        if ($model->file != 'index.html'){
            $zippy = Zippy::load();
            $zipAdapter = $zippy->getAdapterFor('zip');
            $archive = $zipAdapter->open(Yii::getAlias('@filePath') . '/' . $model->file);
            $projectFolder = Yii::getAlias('@filePath') . '/' . $tree;
            $archive->extract($projectFolder);
            return $projectFolder;
        }else{
            $projectForm->fileIndex->saveAs(Yii::getAlias('@filePath') . '/' . $tree  . $model->file);
            $projectFolder = Yii::getAlias('@filePath') . '/' . $tree;
            $projectRsc = Yii::getAlias('@filePath') . '/' . $tree . '/rsc';
            FileHelper::createDirectory($projectRsc);
            FileHelper::copyDirectory(Yii::getAlias('@rscPath'),$projectFolder . '/rsc');
            return $projectFolder;
        }
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }
}
