<?php

namespace backend\controllers;

use backend\models\ProjectForm;
use backend\models\ProjectSearch;
use backend\models\Save;
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

    public function actionCreate()
    {
        $model = new ProjectForm();
        $data = $this->getProjectList();
        if (!$this->loadAndValidateProject($model)){
            return $this->render('create', [
                'model' => $model,
                'data' => $data,
            ]);
        }

        $projectModel = new Project();
        $projectModel->user_id = Yii::$app->user->identity->getId();
        $projectModel->name = $model->name;
        $projectModel->parent_id = $model->parent_id;
        $projectModel = $this->setFile($model, $projectModel);
        if ($projectModel->save()) {
            $folderName = $this->unpacking($projectModel, $model);
            FileHelper::unlink(Yii::getAlias('@filePath') . '/' . $projectModel->file);
            $result = Project::searchFile($folderName);
            if (!$result) {
                $this->delete($projectModel);
                $session = Yii::$app->session;
                // установка flash-сообщения с названием "projectDeleted"
                $session->setFlash('projectDeleted', Yii::t('content', 'Your project is not created!'));
                return $this->redirect(['create']);
            }
            return $this->redirect(['view', 'id' => $projectModel->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'data' => $data,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $projectForm = new ProjectForm();
        $projectForm->id = $model->id;
        if (!$this->loadAndValidateProject($projectForm, $model)){
            return $this->render('update', [
                'model' => $projectForm,
            ]);
        }

        $model = $this->setFile($projectForm, $model);

        $pathTree = $model->getTree();
        if ($projectForm->name != $model->name) {
            $model->name = $projectForm->name;
            rename("./tmp/" . $pathTree, "./tmp/" . $model->getTree());
        }

        if (!$model->save()) {
            return $this->render('update', [
                'model' => $projectForm,
            ]);
        }

        $tree = $model->getTree();
        $projectFolder = $this->updateArchive($model, $tree, $projectForm);
        $result = Project::searchFile($projectFolder);
        if ($projectForm->file) {
            FileHelper::unlink(Yii::getAlias('@filePath') . '/' . $model->file);
        }

        if (!$result) {
            $this->handleError($this->action->id);
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }


    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * @param $projectModel
     * @param $model
     * @return string
     */
    public function unpacking($projectModel, $model)
    {
        // Load Zippy
        $zippy = Zippy::load();
        // Open an archive
        $pathTree = $projectModel->getTree();
        $zipAdapter = $zippy->getAdapterFor('zip');
        if ($model->fileIndex) {
            return Save::saveIndexFile($model, $pathTree);
        } else {
            $archive = $zipAdapter->open(Yii::getAlias('@filePath') . DIRECTORY_SEPARATOR . $projectModel->file);
            return Save::saveFile($archive, $pathTree);
        }
    }

    /**
     * @param $projectModel
     */
    public function delete($projectModel)
    {
        $projectModel->delete();
    }

    /**
     * @return array
     */
    public function getProjectList(){
        if (Yii::$app->user->identity->isAdmin) {
            $projectList = Project::find()->all();
        } else {
            $projectList = Project::find()->where(['user_id' => Yii::$app->user->identity->getId()])->all();
        }

        return ArrayHelper::map($projectList, 'id', 'name');
    }

    /**
     * @param $projectForm
     * @param null $model
     * @return bool
     */
    public function loadAndValidateProject($projectForm, $model = null)
    {
        if (!$projectForm->load(Yii::$app->request->post())) {
            $projectForm->name = $model ? $model->name : '';
            return false;
        }

        $projectForm->file = UploadedFile::getInstance($projectForm, 'file');
        $projectForm->fileIndex = UploadedFile::getInstance($projectForm, 'fileIndex');

        if (!$projectForm->validate()) {
            $projectForm->name = $model ? $model->name : '';
            return false;
        }

        return true;
    }

    /**
     * @param $projectForm
     * @param $model
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function setFile($projectForm, $model)
    {
        if ($projectForm->file) {
            $zipName = Yii::$app->getSecurity()->generateRandomString();
            $model->file = $zipName . '.' . $projectForm->file->extension;
            $projectForm->file->saveAs(Yii::getAlias('@filePath') . '/' . $model->file);
        } else {
            $model->file = $projectForm->fileIndex->name;
        }

        return $model;
    }

    /**
     * @param $action
     * @return \yii\web\Response
     */
    public function handleError($action)
    {
        $session = Yii::$app->session;
        // установка flash-сообщения с названием "projectDeleted"
        $session->setFlash('projectDeleted', Yii::t('content', 'Your project is not created!'));
        return $this->redirect(['create']);
    }

    /**
     * @param $id
     * @return Project|null
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function updateArchive($model, $tree, $projectForm)
    {
        if ($projectForm->file) {
            $zippy = Zippy::load();
            $zipAdapter = $zippy->getAdapterFor('zip');
            $archive = $zipAdapter->open(Yii::getAlias('@filePath') . '/' . $model->file);
            $projectFolder = Yii::getAlias('@filePath') . '/' . $tree;
            $archive->extract($projectFolder);
            return $projectFolder;
        } else {
            $projectForm->fileIndex->saveAs(Yii::getAlias('@filePath') . '/' . $tree . $model->file);
            $projectFolder = Yii::getAlias('@filePath') . '/' . $tree;
            $projectRsc = Yii::getAlias('@filePath') . '/' . $tree . '/rsc';
            FileHelper::createDirectory($projectRsc);
            FileHelper::copyDirectory(Yii::getAlias('@rscPath'), $projectFolder . '/rsc');
            return $projectFolder;
        }
    }
}
