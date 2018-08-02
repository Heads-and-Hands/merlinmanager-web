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

    public function unpacking($projectModel)
    {
        // Load Zippy
        $zippy = Zippy::load();
        // Open an archive
        $pathTree = $projectModel->getTree();
        $zipAdapter = $zippy->getAdapterFor('zip');
        $archive = $zipAdapter->open(Yii::getAlias('@filePath') . '/' . $projectModel->file);
        if ($projectModel->parent_id) {
            $projectFolder = Yii::getAlias('@filePath') . '/' . $pathTree;
            FileHelper::createDirectory($projectFolder);
            $archive->extract($projectFolder);
            return $projectFolder;
        } else {
            $projectFolder = Yii::getAlias('@filePath') . '/' . $pathTree;
            FileHelper::createDirectory($projectFolder);
            $archive->extract($projectFolder);
            return $projectFolder;
        }

    }

    public function delete($projectModel)
    {
        $session = Yii::$app->session;
        // установка flash-сообщения с названием "projectDeleted"
        $session->setFlash('projectDeleted', Yii::t('content', 'index.html not found project not created'));
        $projectModel->delete();
    }


    public function actionCreate()
    {
        $model = new ProjectForm();
        $projectList = Project::find()->all();
        $data = ArrayHelper::map($projectList, 'id', 'name');

        if (!$model->load(Yii::$app->request->post())) {
            return $this->render('create', [
                'model' => $model,
                'data' => $data,
            ]);
        }

        $model->file = UploadedFile::getInstance($model, 'file');
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
        $zipName = Yii::$app->getSecurity()->generateRandomString();
        $projectModel->file = $zipName . '.' . $model->file->extension;
        $model->file->saveAs(Yii::getAlias('@filePath') . '/' . $projectModel->file);

        if ($projectModel->save()) {
            $pathTree = $projectModel->getTree();
            $folderName = $this->unpacking($projectModel);
            $result = Project::searchFile($folderName);
            if (!$result) {
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
        $pathTree = $model->getTree();

        if (!$model->load(Yii::$app->request->post())) {
            return $this->render('update', [
                'model' => $model,
            ]);
        }

        if (!$model->file = UploadedFile::getInstance($model, 'file')) {
            return $this->render('update', [
                'model' => $model,
            ]);
        }

        $zipName = Yii::$app->getSecurity()->generateRandomString();

        if (!$model->validate()) {
            return $this->render('update', [
                'model' => $model,
            ]);
        }

        $model->file->saveAs(Yii::getAlias('@filePath') . '/' . $zipName . '.' . $model->file->extension);
        $model->file = $zipName . '.' . $model->file->extension;
        rename("./tmp/" . $pathTree, "./tmp/" . $model->getTree());

        if (!$model->save()) {
            return $this->render('update', [
                'model' => $model,
            ]);
        }

        $tree = $model->getTree();
        $projectFolder = $this->updateArchive($model, $tree);
        $result = Project::searchFile($projectFolder);

        if (!$result) {
            $session = Yii::$app->session;
            // установка flash-сообщения с названием "projectDeleted"
            $session->setFlash('projectDeleted', Yii::t('content', 'Your project is not created!'));
            return $this->redirect(['update']);
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function updateArchive($model, $tree)
    {
        $zippy = Zippy::load();
        $zipAdapter = $zippy->getAdapterFor('zip');
        $archive = $zipAdapter->open(Yii::getAlias('@filePath') . '/' . $model->file);
        $projectFolder = Yii::getAlias('@filePath') . '/' . $tree;
        $archive->extract($projectFolder);
        return $projectFolder;
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }
}
