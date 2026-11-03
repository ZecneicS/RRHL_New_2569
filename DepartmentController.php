<?php

namespace app\modules\manage\controllers;

//use app\models\SByear;
use Yii;
use app\models\Office;
use app\modules\manage\models\OfficeSearch;
use yii\widgets\ActiveForm;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * BestCateController implements the CRUD actions for RBestpracticesCategory model.
 */
class DepartmentController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                        [
                        'allow' => true,
                        'roles' => ['Admin'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all RBestpracticesCategory models.
     * @return mixed
     */
    public function actionIndex($b_year = null) {
        $searchModel = new OfficeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        // $dataProvider->query->where(['b_year'=>$b_year]);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single RBestpracticesCategory model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $model = $this->findModel($id);
        $member = $this->getMember($model->id);
        return $this->render('view', [
                    'model' => $model,
                    'member' => $member,
        ]);
    }

    /**
     * Creates a new RBestpracticesCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Office();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } else if ($model->load(Yii::$app->request->post())) {

            $model->save();
            if($model->leader_id) {
                Yii::$app->db->createCommand()->insert('s_department_member', ['did' => $model->id ,'uid' => $model->leader_id,'leader' => '1'])->execute();
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
                    'model' => $model,
        ]);
    }

    /**
     * Updates an existing ItemType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } else if ($model->load(Yii::$app->request->post())) {
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    protected function findModel($id) {
        if (($model = Office::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    protected function getMember($id) {
        $conn = Yii::$app->db;
        $sql = 'SELECT name,public_email,tel,position '
                . 'FROM profile '
                . 'WHERE department_id=:id ORDER BY name';

        $cmd = $conn->createCommand($sql);
        $cmd->bindValue(':id', $id);
        $member = $cmd->queryAll();

        return $member;
    }

}
