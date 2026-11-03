<?php

namespace app\modules\manage\controllers;

use Yii;
use app\models\Office;
use app\models\Provinces;
use app\models\UserCreateForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * หน้าเพิ่มผู้ใช้งานใหม่ (แยกจากหน้าจัดการผู้ใช้ของ dektrium)
 * เส้นทาง: /manage/member/create
 */
class MemberController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'roles' => ['Admin']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => ['create' => ['GET', 'POST']],
            ],
        ];
    }

    public function actionCreate()
    {
        $model = new UserCreateForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มผู้ใช้ ' . $model->username . ' เรียบร้อยแล้ว');
            return $this->redirect(['create']);
        }

        return $this->render('create', [
            'model' => $model,
            'offices' => ArrayHelper::map(
                Office::find()->orderBy(['office_region' => SORT_ASC, 'office_name' => SORT_ASC])->all(),
                'id',
                'office_name'
            ),
            'provinces' => ArrayHelper::map(
                Provinces::find()->orderBy('name_th')->all(),
                'code',
                'name_th'
            ),
        ]);
    }
}
