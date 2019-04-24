<?php

namespace backend\controllers;

use backend\forms\ProjectForm;
use common\models\ProjectDomain;
use Yii;
use common\models\Project;
use backend\models\ProjectSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use common\components\FileManager;

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
            'verbs' => [
                'class'   => VerbFilter::class,
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
            return $this->render('index', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Displays a single Project model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Project model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProjectForm();
        $projectModel = new Project();
        if ($model->load(Yii::$app->request->post())) {
            $project = $projectModel::find()->where(['name' => $model->name])->
            orWhere(['name' => $model->name, 'parent_id' => $model->parent_id])->one();
            if (!$project) {
                $projectModel->name = mb_strtolower($model->name);
                $projectModel->user_id = Yii::$app->user->identity->getId();
                $projectModel->parent_id = $model->parent_id;
                $projectModel->secret = $model->secret;
                $projectModel->file = 'qw';
                return $this->redirect(['view', 'id' => $projectModel->id]);
            }
            return $this->render('create', [
                'model' => $model,
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $projectForm = new ProjectForm();
        if ($model->load(Yii::$app->request->post())) {
            $project = $model::find()->where(['name' => $projectForm->name])->
            orWhere(['name' => $projectForm->name, 'parent_id' => $projectForm->parent_id])->one();
            if (!$project) {
                $model->name = mb_strtolower($model->name);
                $model->file = 'qw';
                $model->save();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', [
            'projectForm' => $projectForm,
            'model'       => $model,
        ]);
    }

    /**
     * Deletes an existing Project model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionTiming($id)
    {
        $project = Project::find()->where(['secret' => $id])->one() ?? false;
        $domain = ProjectDomain::find()->one()->domain ?? false;
        if ($domain && $project) {
            $this->redirect($domain . $project->getTree());
        }
    }

    public function actionProjectSelected()
    {
        $select = Yii::$app->request->post('selection');
        if ($select) {
            foreach($select as $id){
                $model= Project::findOne((int)$id);
                $model->status = 1;
                $model->save();
                $this->redirect(['project/index']);
            }
        }
        $this->redirect(['project/index']);
    }

    /**
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
