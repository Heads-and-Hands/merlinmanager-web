<?php

namespace backend\controllers;

use Alchemy\Zippy\Zippy;
use backend\forms\DomainForm;
use common\components\FileManager;
use Yii;
use common\models\ProjectDomain;
use backend\models\DomainSearch;
use backend\components\Controller;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * DomainController implements the CRUD actions for ProjectDomain model.
 */
class DomainController extends Controller
{
    /**
     * Lists all ProjectDomain models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DomainSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProjectDomain model.
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
     * Updates an existing ProjectDomain model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $domainForm = new DomainForm();
        $domainForm->id = $model->id;
        $domainForm->domain = $model->domain;

        if (!$domainForm->load(Yii::$app->request->post())) {
            return $this->render('update', [
                'model' => $domainForm,
            ]);
        }

        $domainForm->file = UploadedFile::getInstance($domainForm, 'file');
        if (!$domainForm->validate()) {
            return $this->render('update', [
                'model' => $domainForm,
            ]);
        }

        $model->domain = $domainForm->domain;
        if (!$model->save()) {
            return $this->render('update', [
                'model' => $domainForm,
            ]);
        }

        if ($domainForm->file->name){
            $this->updateArchive($domainForm);
            FileHelper::unlink(Yii::getAlias('@webPath') . '/' . $domainForm->file->name);
        }

        return $this->redirect(['view', 'id' => $domainForm->id]);
    }

    public function updateArchive($domainForm)
    {
        $domainForm->file->saveAs(Yii::getAlias('@webPath') . '/' . $domainForm->file->name);
        $zippy = Zippy::load();
        $zipAdapter = $zippy->getAdapterFor('zip');
        $archive = $zipAdapter->open(Yii::getAlias('@webPath') . '/' . $domainForm->file->name);
        $archive->extract(Yii::getAlias('@webPath'));
        return true;
    }

    /**
     * Finds the ProjectDomain model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ProjectDomain the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProjectDomain::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
