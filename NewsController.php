<?php

namespace app\modules\manage\controllers;

use Yii;
use app\models\News;
// use app\models\NewsAnswer;
use app\models\NewsAssign;
use app\models\NewsType;
use app\models\NewsSla;
use app\models\NewsFact;
use app\modules\manage\models\NewsSearch;
use app\modules\manage\models\NewsProgressSearch;
use app\modules\manage\models\UploadPreview;
use yii\web\UploadedFile;
use yii\imagine\Image;
use yii\helpers\Json;
// use app\models\Log;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\SystemServices;

use Da\QrCode\QrCode;
use kartik\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * ItemsController implements the CRUD actions for Items model.
 */
class NewsController extends Controller
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
                        'roles' => ['Admin', 'KM'],
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
        $searchModel = new NewsSearch();
        $dataProvider = $searchModel->search(
            Yii::$app->request->queryParams
        );

        $dataProvider->query->andWhere(['status' => '1']);
        $dataProvider->pagination->pageSize = 30;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSuccess()
    {
        $searchModel = new NewsSearch();
        $dataProvider = $searchModel->search(
            Yii::$app->request->queryParams
        );

        $dataProvider->query->andWhere(['status' => '3']);
        $dataProvider->pagination->pageSize = 30;

        return $this->render('success', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionProgress()
    {
        $searchModel = new NewsProgressSearch();
        $dataProvider = $searchModel->search(
            Yii::$app->request->queryParams
        );

        $dataProvider->query->andWhere(['status' => '2']);
        $dataProvider->pagination->pageSize = 30;

        return $this->render('progress', [
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

        $model = $this->findModel($id);

        if ($model->status == '1') {
            $status['btn_receive'] = '';
            $status['btn_assign'] = ' disabled';
            $status['btn_answer'] = ' disabled';
            $status['btn_delete'] = '';
            $status['alert_style'] = 'd-none';
            $status['alert_text'] = '';
            $status['menu_route'] = 'index';
            $status['btn_assigned_style'] = '';
            $status['btn_upd_url'] = 'update';
        } else if ($model->status == '2') {
            $status['btn_receive'] = ' disabled';
            $status['btn_assign'] = '';
            $status['btn_answer'] = '';
            $status['btn_delete'] = ' d-none';
            $status['alert_style'] = 'd-none';
            $status['alert_text'] = '';
            $status['menu_route'] = 'progress';
            $status['btn_assigned_style'] = 'pop-approve-answer';
            $status['btn_upd_url'] = 'update';
        } else if ($model->status == '3') {
            $status['btn_receive'] = ' d-none';
            $status['btn_assign'] = ' d-none';
            $status['btn_answer'] = ' d-none';
            $status['btn_delete'] = ' d-none';
            $status['btn_assigned_style'] = 'pop-assigned-answer';
            $status['btn_upd_url'] = 'updatepub';

            $status['menu_route'] = 'success';
            if ($model->publish == '1') {
                $status['alert_style'] = 'alert-success';
                $status['alert_text'] = 'เผยแพร่แล้ว ' . ($model->publish_timestamp ? '(' . SystemServices::getThShortdate($model->publish_timestamp, 1) . ')' : '') . ' ' . Html::a('<i class="fas fa-globe-asia"></i> ดูหน้าเว็บ', ['/site/preview/', 'id' => $model->id], ['class' => 'btn btn-primary', 'target' => '_blank', 'style' => 'text-decoration: none;']);
                $status['btn_publish'] = ' d-none';
                $status['btn_unpublish'] = '';
            } else if ($model->publish == '2') {
                $status['alert_style'] = 'alert-warning';
                $status['alert_text'] = 'ระงับการเผยแพร่/รอตรวจสอบ ' . ($model->publish_timestamp ? '(' . SystemServices::getThShortdate($model->publish_timestamp, 1) . ')' : '') . ' ' . Html::a('<i class="fas fa-globe-asia"></i> ดูหน้าเว็บ', ['/site/preview/', 'id' => $model->id], ['class' => 'btn btn-primary', 'target' => '_blank', 'style' => 'text-decoration: none;']);
                $status['btn_publish'] = '';
                $status['btn_unpublish'] = ' d-none';
            } else {
                $status['alert_style'] = 'alert-danger';
                $status['alert_text'] = 'ไม่เผยแพร่';
                $status['btn_publish'] = '';
                $status['btn_unpublish'] = ' d-none';
            }
        }
        //$status['btn_style'] = '';

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

        $model->office = 'ศูนย์ต่อต้านข่าวปลอม';

        return $this->render('create', [
            'model' => $model,
            'modelfile' => $modelfile,
            'type' => $this->getNewstype(),
            'sla' => $this->getSla(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $modelfile = new UploadPreview();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } else if ($model->load(Yii::$app->request->post())) {
            // $model->update_at = date('Y-m-d H:i:s');
            // --- ส่วนการจัดการเงื่อนไขก่อนบันทึก ---
            if ($model->sender != '2') {
                // ถ้าไม่ใช่หน่วยงาน ให้ล้างค่า office เป็นค่าว่าง
                $model->office = null;
            }
            $modelfile->fpreview = UploadedFile::getInstance($modelfile, 'fpreview');
            if ($modelfile->fpreview) {
                $now = date('YmdHis');
                $md5 = md5($now);
                $previewpath = Yii::getAlias('@uploads') . '/news/';
                $curr_preview = !empty($model->image) ? $previewpath . $model->image : null;
                $fpreview = $md5 . '.' . $modelfile->fpreview->extension;
                $save_path = $previewpath . $fpreview;
                if ($modelfile->fpreview->saveAs($save_path)) {
                    $model->image = $fpreview;
                    Image::resize($save_path, 1200, 660, true, false)->save($save_path);

                    if (!empty($curr_preview) && is_file($curr_preview)) {
                        unlink($curr_preview);
                    }
                }
            }

            $model->save();

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('update', [
            'model' => $model,
            'modelfile' => $modelfile,
            'type' => $this->getNewstype(),
            'sla' => $this->getSla(),
        ]);
    }

    public function actionUpdatepub($id)
    {
        $model = $this->findModel($id);
        $modelfile = new UploadPreview();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } else if ($model->load(Yii::$app->request->post())) {
            // $model->update_at = date('Y-m-d H:i:s');
            // --- ส่วนการจัดการเงื่อนไขก่อนบันทึก ---
            if ($model->sender != '2') {
                // ถ้าไม่ใช่หน่วยงาน ให้ล้างค่า office เป็นค่าว่าง
                $model->office = null;
            }
            $modelfile->fpreview = UploadedFile::getInstance($modelfile, 'fpreview');
            if ($modelfile->fpreview) {
                $now = date('YmdHis');
                $md5 = md5($now);
                $previewpath = Yii::getAlias('@uploads') . '/news/';
                $curr_preview = !empty($model->image) ? $previewpath . $model->image : null;
                $fpreview = $md5 . '.' . $modelfile->fpreview->extension;
                $save_path = $previewpath . $fpreview;
                if ($modelfile->fpreview->saveAs($save_path)) {
                    $model->image = $fpreview;
                    Image::resize($save_path, 1200, 660, true, false)->save($save_path);

                    if (!empty($curr_preview) && is_file($curr_preview)) {
                        unlink($curr_preview);
                    }
                }
            }

            $model->save();

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('updatepub', [
            'model' => $model,
            'modelfile' => $modelfile,
            'type' => $this->getNewstype(),
            'sla' => $this->getSla(),
            'fact' => $this->getFact(),
            'pub' => $this->getPub(),
            // 'link' => $link,
        ]);
    }

    // public function actionConfirmAnswer($id)
    // {
    //     $model = $this->findAssigedAnswer($id);
    //     $model->use_status = 1;
    //     $model->save(false);

    //     $news = $this->findModel($model->news_id);
    //     $news->content = $model->content_answer;
    //     $news->publish = $model->publish;
    //     $news->status = 3;
    //     $news->save(false);

    //     return $this->redirect(['view', 'id' => $model->news_id]);
    // }

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

    public function actionChangeStatus($id)
    {
        $model = $this->findModel($id);
        // $model->shared = $s;
        if ($model->publish == '1') {
            $model->publish = '2';
            $model->publish_timestamp = date('Y-m-d H:i:s');
        } else {
            $model->publish = '1';
            $model->publish_timestamp = date('Y-m-d H:i:s');
        }
        $model->save(false);

        // $message = 'ส่งข้อมูลแล้ว โปรดรอการดำเนินการตรวจสอบ';
        // Yii::$app->session->setFlash('success', $message);

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionPopAssign($id)
    {
        $model = new NewsAssign();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } else if ($model->load(Yii::$app->request->post())) {
            $model->news_id = $id;
            $model->assign_by_id = yii::$app->user->id;
            $model->assign_by_fullname = yii::$app->user->identity->name;
            $model->assign_timestamp = date('Y-m-d H:i:s');
            $model->assign_to_fullname = $this->getProfile($model->assign_to_id);

            $model->save(false);

            // $lic = $this->getOnline($model->online_id);
            // Log::schedule($id, 'เพิ่มรายการห้องประชุมออนไลน์', Yii::$app->user->id ,$lic['name']);

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->renderAjax('pop-assign', [
            'model' => $model,
            'office' => ArrayHelper::map($this->getOffice(), 'id', 'office_name'),
            'profile' => [],
        ]);
    }

    public function actionPopAccept($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } else if ($model->load(Yii::$app->request->post())) {
            $model->user_id = yii::$app->user->id;
            $model->user_fullname = yii::$app->user->identity->name;
            // $model->accept_timestamp = date('Y-m-d H:i:s');
            $model->status = '2';

            $model->save(false);

            // $lic = $this->getOnline($model->online_id);
            // Log::schedule($id, 'เพิ่มรายการห้องประชุมออนไลน์', Yii::$app->user->id ,$lic['name']);

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->renderAjax('pop-accept', [
            'model' => $model,
        ]);
    }

    public function actionPopAnswer($id)
    {
        $model = new NewsAssign();
        $news = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } else if ($model->load(Yii::$app->request->post())) {
            $model->news_id = $id;

            if (Yii::$app->user->can('Admin')) {
                $model->assign_by_id = yii::$app->user->id;
                $model->assign_by_fullname = yii::$app->user->identity->name;
                $model->assign_to_id = yii::$app->user->id;
                $model->assign_to_office = Yii::$app->user->identity->profile->department_id;
                $model->assign_to_fullname = yii::$app->user->identity->name;
                $model->assign_timestamp = date('Y-m-d H:i:s');
                $model->accept_timestamp = date('Y-m-d H:i:s');
                $model->reply_timestamp = date('Y-m-d H:i:s');
            }

            $model->content_answer = $model->content_answer3 . $model->content_answer4;
            $model->use_status = '1';
            // $model->accept_timestamp = date('Y-m-d H:i:s');
            $news->status = '3';

            $model->save();

            // $lic = $this->getOnline($model->online_id);
            // Log::schedule($id, 'เพิ่มรายการห้องประชุมออนไลน์', Yii::$app->user->id ,$lic['name']);

            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->renderAjax('pop-answer', [
            'model' => $model,
            'news' => $news,
            'fact' => $this->getFact(),
            'pub' => $this->getPub(),
        ]);
    }

    public function actionPopApproveAnswer($id)
    {
        $model = $this->findAssigedAnswer($id);
        $news = $this->findModel($model->news_id);
        $news->scenario = 'special_form';

        if (Yii::$app->request->isAjax && $news->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($news);
        } else if ($news->load(Yii::$app->request->post())) {
            $news->status = '3';
            if ($news->publish == '1') {
                $news->publish_timestamp = date('Y-m-d H:i:s');
            }

            if ($news->save(false)) {
                $model->use_status = '1';
                $model->save(false);
            }

            return $this->redirect(['view', 'id' => $news->id]);
        }


        if (empty($news->content) && !empty($model->content_answer)) {
            $news->content = $model->content_answer;
        }
        if (empty($news->url_reference) && !empty($model->url_reference)) {
            $news->url_reference = $model->url_reference;
        }
        $model->url_reference ? $link = $this->splitUrls($model->url_reference) : $link = null;

        return $this->renderAjax('pop-approve-answer', [
            'model' => $model,
            'news' => $news,
            'fact' => $this->getFact(),
            'pub' => $this->getPub(),
            'link' => $link,
        ]);
    }

    public function actionPopAssignedAnswer($id)
    {
        $model = $this->findAssigedAnswer($id);
        $news = $this->findModel($model->news_id);
        // $news->scenario = 'special_form';


        if (empty($news->content) && !empty($model->content_answer)) {
            $news->content = $model->content_answer;
        }
        if (empty($news->url_reference) && !empty($model->url_reference)) {
            $news->url_reference = $model->url_reference;
        }
        $model->url_reference ? $link = $this->splitUrls($model->url_reference) : $link = null;

        return $this->renderAjax('pop-assigned-answer', [
            'model' => $model,
            'news' => $news,
            // 'fact' => $this->getFact(),
            // 'pub' => $this->getPub(),
            'link' => $link,
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
        $model = NewsAssign::find()->where(['news_id' => $id])->all();
        // print_r($model);
        // die();
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

    protected function splitUrls($input)
    {
        // ใช้ Regular Expression เพื่อแยกด้วย: ขึ้นบรรทัดใหม่ (\n), ลูกน้ำ (,), หรือ ช่องว่าง (\s)
        // และกรองค่าว่างออกด้วย ARRAY_FILTER
        $urlArray = preg_split('/[\n,\s]+/', $input);

        // ลบช่องว่างที่อาจติดมา และกรองทิ้งหากมีรายการที่เป็นค่าว่าง
        $urlArray = array_filter(array_map('trim', $urlArray));

        return $urlArray;
    }

    protected function getOffice($id = null)
    {
        $conn = Yii::$app->db;

        if ($id) {
            $sql = 'SELECT o.* FROM office WHERE id=:id';
            $cmd = $conn->createCommand($sql)->bindValue(':id', $id);
            $data = $cmd->queryOne();
        } else {
            $sql = 'SELECT o.id, CONCAT(o.office_name, " (", COUNT(p.user_id), ")") as office_name FROM office o INNER JOIN profile p ON o.id = p.department_id GROUP BY o.id ORDER BY o.office_region, o.office_name';
            $cmd = $conn->createCommand($sql);
            $data = $cmd->queryAll();
        }

        return $data;
    }

    public function actionGetUserByOffice()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            $id = $parents[0];
            $out = $this->getOfficeProfile($id);
            return Json::encode(['output' => $out, 'selected' => '']);
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    public function getOfficeProfile($id)
    {
        $conn = Yii::$app->db;

        $sql = 'SELECT user_id , name '
            . 'FROM profile WHERE department_id = :id '
            . 'ORDER BY name';
        $cmd = $conn->createCommand($sql);
        $cmd->bindValue(':id', $id);
        $data = $cmd->queryAll();

        $obj = [];
        foreach ($data as $v) {
            array_push($obj, ['id' => $v['user_id'], 'name' => $v['name']]);
        }
        return $obj;
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
                . 'WHERE user_id != :not AND workstatus="1" '
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
}
