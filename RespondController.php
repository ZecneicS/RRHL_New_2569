<?php

namespace app\modules\manage\controllers;

use Yii;
use app\models\News;
// use app\models\NewsAnswer;
use app\models\NewsAssign;
use app\models\NewsType;
use app\models\NewsSla;
use app\models\NewsFact;
use app\modules\manage\models\NewsRespond;
use app\modules\manage\models\UploadPreview;
use yii\web\UploadedFile;
use yii\imagine\Image;
// use app\models\Log;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
// use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use app\models\SystemServices;
use Da\QrCode\QrCode;
use yii\helpers\ArrayHelper;

/**
 * ItemsController implements the CRUD actions for Items model.
 */
class RespondController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    // 'change-status' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Items models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NewsRespond();
        $dataProvider = $searchModel->search(
            Yii::$app->request->queryParams
        );

        $dataProvider->query->andWhere(['status' => '2', 'news_assign.reply_timestamp' => null]);
        $dataProvider->pagination->pageSize = 30;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionSend()
    {
        $searchModel = new NewsRespond();
        $dataProvider = $searchModel->search(
            Yii::$app->request->queryParams
        );

        $dataProvider->query->andWhere(['status' => '1']);
        $dataProvider->pagination->pageSize = 30;

        return $this->render('send', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSuccess()
    {
        $searchModel = new NewsRespond();
        $dataProvider = $searchModel->search(
            Yii::$app->request->queryParams
        );

        $dataProvider->query->andWhere([
            'or',
            ['status' => '3'],
            ['is not', 'news_assign.reply_timestamp', null],
        ]);
        // $dataProvider->query->orWhere(['not', ['news_assign.reply_timestamp' => null]]); 

        $dataProvider->pagination->pageSize = 30;

        return $this->render('success', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    

    /**
     * Displays a single Items model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // $photo = [];
        // $newsPhoto = PublishnewsPhoto::find()->where(['news_id' => $id])->all();
        // $photoPath = Yii::getAlias('@web') . '/uploads/news/onepage_photo/';
        // foreach ($newsPhoto as $p) {
        //     $photo[$p['position']] = $photoPath . $p['filename'];
        // }

        $model = $this->findModel($id);
        $assigned = $this->findAssigned($id);

        if ($model->status == 3) {  // ถ้าสถานะเป็น "ดำเนินการแล้ว"
            if (empty($assigned->reply_timestamp)) {    // แต่ยังไม่มีการตอบกลับ
                $status['btn_view_style'] = ' disabled';
                $status['btn_view_text'] = 'หมดเวลาการตอบกลับ';
                $status['btn_update'] = ' d-none';
                $status['btn_update_text'] = '';
                $status['card_style'] = 'card-secondary';
                $status['card_text'] = 'ไม่ได้ตอบกลับ';
                $status['menu_route'] = 'success';
                $status['menu_text'] = 'ดำเนินการแล้ว';
                $status['showtime'] = '';
                $status['showresult'] = 'การตอบกลับ: <span class="text-danger">ไม่ได้ตอบกลับ</span>';
            } else {                                    // มีการตอบกลับแล้ว
                $status['btn_view_style'] = ' ';
                $status['btn_view_text'] = 'ดูการตอบกลับ';
                $status['btn_update'] = ' d-none';
                $status['btn_update_text'] = '';
                $status['card_style'] = 'card-success';
                $status['card_text'] = 'ตอบกลับแล้ว';
                $status['menu_route'] = 'success';
                $status['menu_text'] = 'ดำเนินการแล้ว';
                $status['showtime'] = 'ตอบกลับ: ' . SystemServices::getThShortdate($assigned->reply_timestamp, 1) .'<br>';
                $status['showresult'] = 'เวลาที่ตอบกลับ: ' . SystemServices::formatTimeDiff($assigned->assign_timestamp, $assigned->reply_timestamp);
            }
        } else {    // ถ้ารายการข่าวนี้ยังไม่เสร็จสิ้นการดำเนินการ
            if (empty($assigned->reply_timestamp)) {    // ยังไม่มีการตอบกลับ
                $status['btn_view_style'] = ' d-none';
                $status['btn_view_text'] = '';
                $status['btn_update'] = ' ';
                $status['btn_update_text'] = 'ตอบกลับ';
                $status['card_style'] = 'card-danger';
                $status['card_text'] = 'รอการตอบกลับ';
                $status['menu_route'] = 'index';
                $status['menu_text'] = 'รอการตอบกลับ';
                $status['showtime'] = '';
                $status['showresult'] = 'เวลาที่รอการตอบกลับ: ' . SystemServices::formatTimeDiff($assigned->assign_timestamp, date('Y-m-d H:i:s'));
            } else {                        // มีการตอบกลับแล้ว แต่ยังไม่เสร็จสิ้นการดำเนินการ (รายการอยู่ระหว่างดำเนินการ)
                $status['btn_view_style'] = ' d-none';
                $status['btn_view_text'] = '';
                $status['btn_update'] = ' ';
                $status['btn_update_text'] = 'การตอบกลับ';
                $status['card_style'] = 'card-success';
                $status['card_text'] = 'ตอบกลับแล้ว';
                $status['menu_route'] = 'success';
                $status['menu_text'] = 'ดำเนินการแล้ว';
                $status['showtime'] = 'ตอบกลับ: ' . SystemServices::getThShortdate($assigned->reply_timestamp, 1) .'<br>';
                $status['showresult'] = 'เวลาที่ตอบกลับ: ' . SystemServices::formatTimeDiff($assigned->assign_timestamp, $assigned->reply_timestamp);
            }
        }

        $status['btn_style'] = '';

        $imgUrl = Yii::$app->getBasePath() . '/' . Yii::getAlias('web') . '/uploads/news/' . $model->image;

        if (is_file($imgUrl)) {
            $image = Yii::getAlias('@web') . '/uploads/news/' . $model->image;
        } else {
            $image = Yii::getAlias('@web') . Yii::$app->params['noimg'];
        }

        return $this->render('view', [
            'model' => $model,
            'status' => $status,
            'image' => $image,
            'assigned' => $this->findAssigned($id)
        ]);
    }

    public function actionCreate()
    {
        $model = new News();
        $modelfile = new UploadPreview();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } else if ($model->load(Yii::$app->request->post())) {

            $model->time_stamp = date('Y-m-d H:i:s');
            $modelfile->fpreview = UploadedFile::getInstance($modelfile, 'fpreview');
            $create = date('YmdHis');
            $md5 = md5($create);

            if ($modelfile->fpreview) {
                $previewpath = Yii::getAlias('@uploads') . '/news/';
                $fpreview = $md5 . '.' . $modelfile->fpreview->extension;
                $save_path = $previewpath . $fpreview;
                if ($modelfile->fpreview->saveAs($save_path)) {
                    $model->image = $fpreview;
                    Image::resize($save_path, 1200, 660, true, false)->save($save_path);
                }
            }

            // --- ส่วนการจัดการเงื่อนไขก่อนบันทึก ---
            if ($model->sender != '2') {
                // ถ้าไม่ใช่หน่วยงาน ให้ล้างค่า office เป็นค่าว่าง
                $model->office = null;
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modelfile' => $modelfile,
            'type' => $this->getNewstype(),
            'sla' => $this->getSla(),
        ]);
    }

    // public function actionUpdate($id)
    // {
    //     $model = $this->findModel($id);
    //     $modelfile = new UploadPreview();

    //     if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         return ActiveForm::validate($model);
    //     } else if ($model->load(Yii::$app->request->post())) {
    //         // $model->update_at = date('Y-m-d H:i:s');
    //         // --- ส่วนการจัดการเงื่อนไขก่อนบันทึก ---
    //         if ($model->sender != '2') {
    //             // ถ้าไม่ใช่หน่วยงาน ให้ล้างค่า office เป็นค่าว่าง
    //             $model->office = null;
    //         }
    //         $modelfile->fpreview = UploadedFile::getInstance($modelfile, 'fpreview');
    //         if ($modelfile->fpreview) {
    //             $now = date('YmdHis');
    //             $md5 = md5($now);
    //             $previewpath = Yii::getAlias('@uploads') . '/news/';
    //             $curr_preview = !empty($model->image) ? $previewpath . $model->image : null;
    //             $fpreview = $md5 . '.' . $modelfile->fpreview->extension;
    //             $save_path = $previewpath . $fpreview;
    //             if ($modelfile->fpreview->saveAs($save_path)) {
    //                 $model->image = $fpreview;
    //                 Image::resize($save_path, 1200, 660, true, false)->save($save_path);

    //                 if (!empty($curr_preview) && is_file($curr_preview)) {
    //                     unlink($curr_preview);
    //                 }
    //             }
    //         }

    //         $model->save();

    //         return $this->redirect(['view', 'id' => $id]);
    //     }

    //     return $this->render('update', [
    //         'model' => $model,
    //         'modelfile' => $modelfile,
    //         'type' => $this->getNewstype(),
    //         'sla' => $this->getSla(),
    //     ]);
    // }

    public function actionConfirmAnswer($id)
    {
        $model = $this->findAssigedAnswer($id);
        $model->use_status = 1;
        $model->save(false);

        $news = $this->findModel($model->news_id);
        $news->content = $model->content_answer;
        $news->publish = $model->publish;
        $news->status = 3;
        $news->save(false);

        return $this->redirect(['view', 'id' => $model->news_id]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->image) {
            $filepath = Yii::getAlias('@uploads') . '/news/';
            $file = $filepath . $model->image;
            if (is_file($file)) {
                unlink($file);
                // \app\models\Log::write($model->id, 'ลบรายการแนบ: ref ' . $model->ref_id . ', id ' . $model->id, Yii::$app->user->id, $model->filename);
            }
        }
        // else {
        // \app\models\Log::write($model->id, 'ลบรายการลิงค์: ref ' . $model->ref_id . ', id ' . $model->id, Yii::$app->user->id, $model->url);
        // }

        $model->delete();

        return $this->redirect(['index']);
    }

    public function actionChangeStatus($id, $s)
    {
        $model = $this->findModel($id);
        $model->shared = $s;
        if ($s == '1') {
            $model->approve_date = date('Y-m-d H:i:s');
        } else if ($s == '3') {
            $model->progress_date = date('Y-m-d H:i:s');
        } else {
            $model->progress_date = null;
        }
        $model->save();

        // $message = 'ส่งข้อมูลแล้ว โปรดรอการดำเนินการตรวจสอบ';
        // Yii::$app->session->setFlash('success', $message);

        if ($s == '0') {
            return $this->redirect(['index']);
        } else {
            return $this->redirect(['view', 'id' => $id]);
        }
    }

    public function actionPopAnswer($id)
    {
        $model = $this->findAssigned($id);
        $news = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } else if ($model->load(Yii::$app->request->post())) {
            $model->reply_timestamp = date('Y-m-d H:i:s');
            $model->content_answer = $model->content_answer3 . $model->content_answer4;
            // $model->accept_timestamp = date('Y-m-d H:i:s');

            $model->save();

            // $lic = $this->getOnline($model->online_id);
            // Log::schedule($id, 'เพิ่มรายการห้องประชุมออนไลน์', Yii::$app->user->id ,$lic['name']);

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->renderAjax('pop-answer', [
            'model' => $model,
            'news' => $news,
            'fact' => $this->getFact(),
            // 'pub' => $this->getPub(),
        ]);
    }

    public function actionPopAssignedAnswer($id)
    {
        $model = $this->findAssigedAnswer($id);
        $news = $this->findModel($model->news_id);

        return $this->renderAjax('pop-assigned-answer', [
            'model' => $model,
            'news' => $news,
        ]);
    }

    /**
     * Finds the Items model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Items the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = News::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findAssigned($id)
    {
        $model = NewsAssign::find()->where(['news_id' => $id /*, 'assign_to_id' => Yii::$app->user->id*/])->one();

        return $model;
    }

    protected function findAssigedAnswer($id)
    {
        if (($model = NewsAssign::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function dateTh2En($str)
    {
        $year = substr($str, 0, 4);
        $yearEn = $year - 543;
        return $yearEn . substr($str, 4);
    }

    protected function dateEn2Th($str)
    {
        $year = substr($str, 0, 4);
        $yearTh = $year + 543;
        return $yearTh . substr($str, 4);
    }

    protected function getProfile($id = null, $not = null)
    {
        $conn = Yii::$app->db;
        if ($id) {
            $sql = 'SELECT name FROM profile WHERE user_id=:id ';
            $cmd = $conn->createCommand($sql);
            $cmd->bindValue(':id', $id);
            $query = $cmd->queryOne();
            $profile = $query['name'];
        } else if ($not) {
            $sql = 'SELECT user_id,name '
                . 'FROM profile '
                . 'WHERE user_id != :not AND department_id="doh404" AND workstatus="1" '
                . 'ORDER BY firstname,lastname';
            $cmd = $conn->createCommand($sql);
            $cmd->bindValue(':not', $not);
            $profile = $cmd->queryAll();
        } else {
            $sql = 'SELECT user_id ,CONCAT(department, " (", name, ")") as name '
                . 'FROM profile WHERE department IS NOT NULL '
                . 'ORDER BY department, name';
            $cmd = $conn->createCommand($sql);
            $profile = $cmd->queryAll();
        }

        return $profile;
    }

    protected function getNewstype()
    {
        $type = NewsType::find()
            ->select(['id', 'name'])
            ->orderBy('id')
            ->asArray()
            ->all();

        return $type;
    }

    protected function getSla()
    {
        $sla = NewsSla::find()
            ->select(['id', 'name', 'style'])
            ->orderBy('id')
            ->asArray()
            ->all();

        return $sla;
    }

    protected function getFact()
    {
        $fact = NewsFact::find()
            ->select(['id', 'name'])
            ->orderBy('id')
            ->asArray()
            ->all();

        return $fact;
    }

    protected function getPub()
    {
        $pub = [
            ['id' => '1', 'name' => 'เผยแพร่', 'style' => 'success'],
            ['id' => '0', 'name' => 'ไม่เผยแพร่', 'style' => 'danger'],
            ['id' => '2', 'name' => 'รอตรวจสอบ', 'style' => 'warning'],
        ];

        return $pub;
    }


    protected function genQrcode($param)
    {
        $qr_path = Yii::getAlias('@uploads') . '/news/qrcode/';
        $qr_filename = md5($param) . '.png';

        $qrCode = (new QrCode($param))
            ->setSize(320)
            ->setMargin(10)
            ->setForegroundColor(0, 0, 0)
            ->setBackgroundColor(255, 255, 255);

        $qrCode->writeFile($qr_path . $qr_filename);

        return $qr_filename;
    }
}
