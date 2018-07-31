<?php

namespace backend\controllers;

use backend\models\ProjectForm;
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
            $dataProvider = new ActiveDataProvider([
                'query' => Project::find(),
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

    public function unpacking($projectModel, $projectList, $model)
    {
        // Load Zippy
        $zippy = Zippy::load();
        $projectFolder = Yii::getAlias('@filePath') . '/' . $projectModel->name;
        // Open an archive
        $zipAdapter = $zippy->getAdapterFor('zip');
        $archive = $zipAdapter->open(Yii::getAlias('@filePath') . '/' . $projectModel->file);
        $project = Project::findOne($projectList);
        if ($projectList != "") {
            $projectModel->project_tree = $project['project_tree'] . '/' . $model->name;
            $tree = $projectModel->project_tree;
            $projectModel->save();
            $projectFolder = Yii::getAlias('@filePath') . '/' . $tree;
            FileHelper::createDirectory($projectFolder);
            $archive->extract($projectFolder);
            return $projectFolder;
        } else {
            $projectModel->project_tree = $model->name;
            $projectModel->save();
            FileHelper::createDirectory($projectFolder);
            $archive->extract($projectFolder);
            return $projectFolder;
        }

    }

    public function delete($projectModel)
    {
        $session = Yii::$app->session;
        // установка flash-сообщения с названием "projectDeleted"
        $session->setFlash('projectDeleted', Yii::t('content', 'Your project is not created!'));
        $projectModel->delete();
    }


    public function actionCreate()
    {
        $model = new ProjectForm();
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {
                $projectModel = new Project();
                $projectList = $model->parent_id;
                $projectModel->user_id = Yii::$app->user->identity->getId();
                $projectModel->name = $model->name;
                $projectModel->parent_id = $model->parent_id;
                $zipFiles = Yii::$app->getSecurity()->generateRandomString();
                $projectModel->file = $zipFiles . '.' . $model->file->extension;
                $model->file->saveAs(Yii::getAlias('@filePath') . '/' . $projectModel->file);
                if ($projectModel->save()) {
                    $folderName = $this->unpacking($projectModel, $projectList, $model);
                    $result = Project::search_file($folderName);
                    if (!$result) {
                        $this->delete($projectModel);
                        return $this->redirect(['create']);
                    }
                }
                return $this->redirect(['view', 'id' => $projectModel->id]);
            } else {
                print_r($model->errors);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
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
        $project = Project::find()->where(['id' => $model->id])->one();
        $parent_name = Project::find()->where(['id' => $model->parent_id])->one();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->file = UploadedFile::getInstance($model, 'file')) {
                $zipFiles = Yii::$app->getSecurity()->generateRandomString();
                if ($model->validate()) {
                    $model->file->saveAs(Yii::getAlias('@filePath') . '/' . $zipFiles . '.' . $model->file->extension);
                    $model->file = $zipFiles . '.' . $model->file->extension;
                    rename("./tmp/" . $parent_name->project_tree . '/'. $project->name ,"./tmp/" . $parent_name->project_tree . '/' . $model->name);
                    $model->project_tree = $parent_name->project_tree . '/' . $model->name;
                    $model->save();
                    $projectFolder = $this->updateArchive($model, $parent_name);
                    $result = Project::search_file($projectFolder);
                    if (!$result) {
                        $session = Yii::$app->session;
                        // установка flash-сообщения с названием "projectDeleted"
                        $session->setFlash('projectDeleted', Yii::t('content', 'Your project is not created!'));
                        return $this->redirect(['update']);
                    }
                }
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function updateArchive($model, $parent_name)
    {
        $zippy = Zippy::load();
        $zipAdapter = $zippy->getAdapterFor('zip');
        $archive = $zipAdapter->open(Yii::getAlias('@filePath') . '/' . $model->file);
        $projectFolder = Yii::getAlias('@filePath') . '/' . $parent_name->project_tree . '/' . $model->name;
        $archive->extract($projectFolder);
        return $projectFolder;
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }
}
