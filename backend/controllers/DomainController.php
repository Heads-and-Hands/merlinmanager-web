<?php
namespace backend\controllers;
use Alchemy\Zippy\Zippy;
use backend\models\DomainForm;
use function PHPSTORM_META\elementType;
use Yii;
use backend\models\ProjectDomain;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
/**
 * DomainController implements the CRUD actions for ProjectDomain model.
 */
class DomainController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    /**
     * Lists all ProjectDomain models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ProjectDomain::find(),
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Displays a single ProjectDomain model.
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
    /**
     * Updates an existing ProjectDomain model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
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
        $projectFolder = $this->updateArchive($domainForm);
        FileHelper::unlink(Yii::getAlias('@webPath') . '/' . $domainForm->file->name);
        }
        if (!$projectFolder){
            return $this->render('update', [
                'model' => $domainForm,
            ]);
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
     * @param integer $id
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
