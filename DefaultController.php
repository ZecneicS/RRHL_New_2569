<?php

namespace app\modules\manage\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\db\Query;
/**
 * Default controller for the `modules` module
 */
class DefaultController extends Controller
{
	
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                //'only' => ['create', 'update', 'view', 'delete'],
                'rules' => [
                        [
                        'allow' => true,
                        'roles' => ['@'],
                        // 'roles' => ['Admin','Management'],
                    ],
                ],
            ],
        ];
    }
	
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        // $deptReport = $this->getDeptReport();
        return $this->render('index',[
            'overAll'=>$this->getOverAll(),
            'userStats'=>$this->getUserStats(Yii::$app->user->identity->profile->department_id),
            'deptReport'=>$this->getDeptReport(),
        ]);
    }
    
    protected function getOverAll()
    {
        $db = Yii::$app->db;
        $sql = 'SELECT COUNT(id) as allnews,'
        . 'sum(CASE WHEN `status`=1 THEN 1 END) as new,'
        . 'sum(CASE WHEN `status`=2 THEN 1 END) as prog,'
        . 'sum(CASE WHEN `status`=3 THEN 1 END) as succ  '
        . 'FROM news';
        $command = $db->createCommand($sql);
        $data = $command->queryOne();
        return $data;
    }

    protected function getUserStats($id)
    {
        $db = Yii::$app->db;
        $sql = 'SELECT COUNT(a.id) as allnews,'
        . 'sum(CASE WHEN a.reply_timestamp IS NULL AND n.`status` = 2 THEN 1 END) as wait,'
        . 'sum(CASE WHEN a.reply_timestamp IS NOT NULL THEN 1 END) as reply,'
        . 'sum(CASE WHEN a.reply_timestamp IS NULL AND n.`status`=3 THEN 1 END) as notreply '
        . 'FROM news_assign a INNER JOIN news n ON n.id=a.news_id '
        . 'WHERE a.assign_to_office=:id';
        $command = $db->createCommand($sql);
        $command->bindParam(':id', $id);
        $data = $command->queryOne();
        return $data;
    }

    protected function getDeptReport()
    {
        $queryBase = (new Query())->from('news');

        $deptReport = (clone $queryBase)
            ->select([
                'COALESCE(office.office_name, news_assign.assign_to_office) as name',
                'COUNT(*) as count',
                'SUM(CASE WHEN news_assign.reply_timestamp IS NOT NULL THEN 1 ELSE 0 END) as completed',
                'SUM(CASE WHEN news_assign.reply_timestamp IS NULL THEN 1 ELSE 0 END) as pending',
                'ROUND(SUM(CASE WHEN news_assign.reply_timestamp IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as percent'
            ])
            ->leftJoin('news_assign', 'news_assign.news_id = news.id')
            ->innerJoin("office", "office.id = news_assign.assign_to_office")
            ->andWhere(['news_assign.use_status' => '1'])
            ->groupBy(['news_assign.assign_to_office', 'office.office_name'])
            ->orderBy(['ROUND(SUM(CASE WHEN news_assign.reply_timestamp IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*) * 100, 2)' => SORT_DESC])
            ->all();

        // foreach ($deptReport as &$item) {
        //     $item['percent'] = $item['count'] > 0 ? round(($item['completed'] / $item['count']) * 100, 2) : 0;
        // }

        return $deptReport;
    }
}