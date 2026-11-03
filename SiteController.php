<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\ContactForm;
use yii\db\Query;
use app\models\News;
use app\models\NewsType;
use app\models\Visitor;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

class SiteController extends Controller
{

    public function actions()
    {
        return [

            'error' => [
                'class' => 'yii\web\ErrorAction',
                'layout' => 'error',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $session = Yii::$app->session;
        // Check if the session is already active. If not, open it.
        if (!$session->isActive) {
            $session->open();
        }

        // เขียน Logic เก็บสถิติที่นี่
        $stats = new Visitor();
        $stats->sid = Yii::$app->session->getId();
        $stats->ip = Yii::$app->request->userIP;
        
        $stats->track_type = $this->id . '/' . $action->id;
        $stats->date_visit = date('Y-m-d');
        $stats->date_created = date('Y-m-d H:i:s');
        $stats->save(false);

        return true;
    }

    public function actionIndex()
    {
        $query = new Query;
        $query
            ->select(['n.id id', 'n.news_title news_title', 'n.image image', 'n.time_stamp time_stamp', 'f.image_overlay image_overlay', 't.id t_id', 't.name typename', 't.color typecolor'])->from('news n')
            ->join('LEFT JOIN', 'news_assign a', 'n.id = a.news_id')
            ->join('INNER JOIN', 'news_fact f', 'a.fact = f.id')
            ->join('LEFT JOIN', 'news_type t', 'n.news_type = t.id')
            ->where(['n.status' => 3, 'n.publish' => 1])
            ->andWhere('a.fact IS NOT NULL')
            ->orderBy(['n.time_stamp' => SORT_DESC]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 15, // 4x4 = 16 ข่าวต่อหน้า
        ]);

        $publicNews = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'publicNews' => $publicNews,
            'pagination' => $pagination,
            'tags' => $this->getTag(),
        ]);
    }

    public function actionIndextype($newstype = 0)
    {
        $query = (new Query())
            ->select([
                'n.id id',
                'n.news_title news_title',
                'n.image image',
                'n.time_stamp time_stamp',
                'f.image_overlay image_overlay',
                't.id t_id',
                't.name typename',
                't.color typecolor'
            ])
            ->from('news n')
            ->join('LEFT JOIN', 'news_assign a', 'n.id = a.news_id')
            ->join('INNER JOIN', 'news_fact f', 'a.fact = f.id')
            ->join('LEFT JOIN', 'news_type t', 'n.news_type = t.id')
            ->where(['n.status' => 3, 'n.publish' => 1])
            ->andWhere('a.fact IS NOT NULL');

        // 👉 ใส่เฉพาะตอนเลือกประเภท
        if ((int)$newstype !== 0) {
            $query->andWhere(['n.news_type' => $newstype]);
        }

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 15,
        ]);

        $publicNews = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy(['n.time_stamp' => SORT_DESC])
            ->all();

        return $this->render('indextype', [
            'publicNews' => $publicNews,
            'pagination' => $pagination,
            'newstype' => $newstype, // เผื่อใช้ทำ active ปุ่ม
            'tags' => $this->getTag(),
        ]);
    }

    public function actionSearch($q = null)
    {
        if (empty($q)) {
            return $this->redirect('index');
        } else {
            $keywords = preg_split('/\s+/', trim($q));
            $keywords = array_filter($keywords);

            $escaped = array_map(function ($kw) {
                return preg_quote($kw, '/');
            }, $keywords);
            $pattern = implode('|', $escaped);
        }
        $query = (new Query())
            ->select([
                'n.id id',
                'n.news_title news_title',
                'n.image image',
                'n.time_stamp time_stamp',
                'f.image_overlay image_overlay',
                't.id t_id',
                't.name typename',
                't.color typecolor'
            ])
            ->from('news n')
            ->join('LEFT JOIN', 'news_assign a', 'n.id = a.news_id')
            ->join('LEFT JOIN', 'news_fact f', 'a.fact = f.id')
            ->join('LEFT JOIN', 'news_type t', 'n.news_type = t.id')
            ->where(['n.status' => 3, 'n.publish' => 1])
            ->andWhere('a.fact IS NOT NULL')
            ->andWhere([
                'or',
                ['REGEXP', 'n.news_title', $pattern],
                ['REGEXP', 'a.content_answer', $pattern],
            ]);

        // 👉 ใส่เฉพาะตอนเลือกประเภท
        // if ((int)$newstype !== 0) {
        //     $query->andWhere(['n.news_type' => $newstype]);
        // }

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 15,
        ]);

        $publicNews = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy(['n.time_stamp' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'publicNews' => $publicNews,
            'pagination' => $pagination,
            // 'newstype' => $newstype, // เผื่อใช้ทำ active ปุ่ม
            'tags' => $this->getTag(),
            'getQ' => trim($q),
        ]);
    }

    public function actionContent($id = null)
    {
        if (($model = News::findOne($id)) !== null) {

            $session = Yii::$app->session;

            // Check if the session is already active. If not, open it.
            if (!$session->isActive) {
                $session->open();
            }

            $visit = $model->visit + 1;
            $model->updateAttributes(['visit' => $visit]);

            Yii::$app->db->createCommand()->insert('news_visit', [
                'sid' => Yii::$app->session->getId(),
                'news_id' => $id,
                'date_created' => date('Y-m-d H:i:s')
            ])->execute();

            return $this->render('content', [
                'model' => $model,
                'visit' => $visit,
            ]);
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    /**
     * Login action.
     *
     * @return Response|string
     */
    // public function actionLogin()
    // {
    //     if (!Yii::$app->user->isGuest) {
    //         return $this->goHome();
    //     }

    //     $model = new LoginForm();
    //     if ($model->load(Yii::$app->request->post()) && $model->login()) {
    //         return $this->goBack();
    //     }

    //     $model->password = '';
    //     return $this->render('login', [
    //         'model' => $model,
    //     ]);
    // }


    // public function actionLogout()
    // {
    //     Yii::$app->user->logout();

    //     return $this->goHome();
    // }

    public function actionPrivacy_notice()
    {
        return $this->render('privacy_notice');
    }

	public function actionPreview($id = null)
    {
        if (($model = News::findOne($id)) !== null) {

            $visit = $model->visit;

            return $this->render('preview', [
                'model' => $model,
                'visit' => $visit,
            ]);
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    private function getTag()
    {
        $data = NewsType::find()->select(['id', 'name'])->asArray()->all();
        array_unshift($data, ['id' => 0, 'name' => 'แสดงทั้งหมด']);

        return $data;
    }
}
