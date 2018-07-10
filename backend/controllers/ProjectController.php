<?php

namespace backend\controllers;

use app\models\ProjectForm;
use Yii;
use app\models\Project;
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
        if (Yii::$app->user->identity->isAdmin) {
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

    public function unpacking($project)
    {
        // Load Zippy
        $zippy = Zippy::load();
        $projectFolder = Yii::getAlias('@filePath') . '/' . $project->name;
        // Open an archive
        $zipAdapter = $zippy->getAdapterFor('zip');
        $archive = $zipAdapter->open(Yii::getAlias('@filePath') . '/' . $project->file);
        FileHelper::createDirectory($projectFolder);
        $archive->extract($projectFolder);
        return $projectFolder;
    }

    public function delete($project)
    {
        $session = Yii::$app->session;
        // установка flash-сообщения с названием "projectDeleted"

        $session->setFlash('projectDeleted',Yii::t('content', 'Your project is not created!'));
        $project->delete();
    }

    public function actionCreate()
    {
        $model = new ProjectForm();
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {
                $project = new Project();
                $project->user_id = Yii::$app->user->identity->getId();
                $project->name = $model->name;
                $zipFiles = Yii::$app->getSecurity()->generateRandomString();
                $project->file = $zipFiles . '.' . $model->file->extension;
                $model->file->saveAs(Yii::getAlias('@filePath') . '/' . $project->file);
                if ($project->save()) {
                    $folderName = $this->unpacking($project);
                    $result = $project->search_file($folderName);
                    if (!$result) {
                        $this->delete($project);
                        return $this->redirect(['create']);
                    }
                }
                return $this->redirect(['view', 'id' => $project->id]);
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

    /**
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public
    function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public
    function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

}
