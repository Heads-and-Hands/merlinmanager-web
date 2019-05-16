<?php

namespace backend\controllers;

use backend\forms\ProjectForm;
use Yii;
use common\models\Project;
use backend\models\ProjectSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use backend\components\Controller;
use yii\web\NotFoundHttpException;
use common\components\FileManager;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends Controller
{
    /**
     * Lists all Project models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProjectSearch();
        if (!Yii::$app->user->identity->isAdmin) {
            $dataProvider = new ActiveDataProvider([
                'query' => Project::find()->where(['user_id' => Yii::$app->user->getId()]),
            ]);
        } else {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
        $formModel = new ProjectForm(['isNew' => true]);
        $projectModel = new Project();
        if (!$formModel->load(Yii::$app->request->post()) || !$formModel->validate()) {
            return $this->render('create', [
                'model' => $formModel,
            ]);
        }

        $project = $projectModel::find()->where(['name' => $formModel->name, 'parent_id' => $formModel->parent_id])->one();
        if ($project) {
            $session = Yii::$app->session;
            $session->setFlash('projectDeleted', 'Проект уже существует');
            return $this->redirect(['create']);
        }

        $projectModel->setAttributes($formModel->getAttributes() + ['user_id' => Yii::$app->user->getId()]);
        $projectModel = FileManager::setFile($formModel, $projectModel);
        if ($projectModel->save()) {
            $folderName = FileManager::unpacking($projectModel, $formModel);
            if (!FileManager::searchFile($folderName)) {
                $this->delete($projectModel);
                $session = Yii::$app->session;
                $session->setFlash('projectDeleted', 'Your project is not created!');
                return $this->redirect(['create']);
            }
        }
        return $this->redirect(['view', 'id' => $projectModel->id]);
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
        $formModel = new ProjectForm(['id' => $model->id, 'isNew' => false]);
        if (!$formModel->load(Yii::$app->request->post()) || !$formModel->validate()) {
            $formModel->setAttributes($model->getAttributes());
            return $this->render('update', [
                'model'       => $model,
                'projectForm' => $formModel,
            ]);
        }

        $pathTree = $model->getTree();
        if ($formModel->name != $model->name) {
            $model->name = $formModel->name;
            rename("./tmp/" . $pathTree, "./tmp/" . $model->getTree());
        }

        $model->setAttributes(array_filter($formModel->getAttributes(), function($item) { return !is_null($item); }));
        $model = FileManager::setFile($formModel, $model);
        if (!$model->save()) {
            return $this->render('update', [
                'model'       => $model,
                'projectForm' => $formModel,
            ]);
        }

        $tree = $model->getTree();
        $projectFolder = FileManager::updateArchive($model, $tree, $formModel);
        if ($formModel->file) {
            FileHelper::unlink(Yii::getAlias('@filePath') . '/' . $model->file);
        }

        if ($projectFolder && !Project::searchFile($projectFolder)) {
            $this->handleError($this->action->id);
        }
        return $this->redirect(['view', 'id' => $model->id]);
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
        $project = Project::findOne(['secret' => $id]);
        if (!$project) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        
        Yii::$app->getResponse()->getHeaders()->set('X-Accel-Redirect', '/tmp/' . $project->getTree());
    }

    public function actionProjectSelected()
    {
        $select = Yii::$app->request->post('selection');
        if ($select) {
            foreach ($select as $id) {
                $model = Project::findOne((int)$id);
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

    public function delete($projectModel)
    {
        $projectModel->delete();
    }

    /**
     * @param $action
     * @return \yii\web\Response
     */
    public function handleError($action)
    {
        $session = Yii::$app->session;
        $session->setFlash('projectDeleted', Yii::t('content', 'Your project is not created!'));
        return $this->redirect(['create']);
    }
}
