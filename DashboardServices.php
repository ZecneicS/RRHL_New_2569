<?php
namespace app\models;

use Yii;
use DateTime;

/**
 * This is the model class for data function services.
 */
class DashboardServices  
{
    public static function getReportdate($strDate) {
        $strYear = (int)substr($strDate,0,4)+543;
        $strMonth = (int)substr($strDate,4,2);
        $strDay = (int)substr($strDate,6,2);

        $strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
        $strMonthThai = $strMonthCut["$strMonth"];
        return "$strDay $strMonthThai $strYear";
    } //end actionReportdate
    
    public static function getThdate($strDate) {
        if($strDate){
            $date_d_m = Yii::$app->formatter->asDate($strDate, 'php:j M ');
            $date_y = Yii::$app->formatter->asDate($strDate, 'php:Y')+543;
            $date=$date_d_m.$date_y;
        }else{$date=null;}
        return $date;
    } //end getThdate
    
    public static function getThFulldate($strDate, $time = null) {
        $strYear = (int) substr($strDate, 0, 4) + 543;
        $strMonth = (int) substr($strDate, 5, 2);
        $strDay = (int) substr($strDate, 8, 2);
        $strMonthCut = Array("", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
        $strMonthThai = $strMonthCut[$strMonth];
        $date = "$strDay $strMonthThai $strYear";
        $date .= ($time) ? ' เวลา ' . substr($strDate, 11, 5) . ' น.' : '';
        return $date;
    }
    
    public static function getAge($strDate) {
        if($strDate){
            $from = new DateTime($strDate);
            $to   = new DateTime('today');
            $age = $from->diff($to)->y;
        }else{$age=null;}
        return $age;
    } //end getThdate
    
    
    public static function getTiming($year){
        $month = date('m');
        
        if($year==date('Y')){
            $data_year = $year;
            if($month == '1' || $month == '2' || $month == '3'){
                $data_quarter = 1;
            }else if($month == '4' || $month == '5' || $month == '6'){
                $data_quarter = 2;
            }else if($month == '7' || $month == '8' || $month == '9'){
                $data_quarter = 3;
            }else {
                $data_quarter = 4;
            }
        }else {
            if(($year==(date('Y')+1)) && ($month == '10' || $month == '11' || $month == '12')){
                //$data_year = $year-1;
                $data_year = $year;
                $data_quarter = 1;
            }else{
                $data_year = $year;
                $data_quarter = 4;
            }
            //$data_quarter = 4;
        }
        
        return ['data_year'=>$data_year,'data_quarter'=>$data_quarter];
        
    }
    
    public static function getTimingAdj($year){
        $month = date('m');
        $data_year = $year;
        if($year==date('Y')){
            
            if($month == '1' || $month == '2' || $month == '3'){
                $data_quarter = 2;
            }else if($month == '4' || $month == '5' || $month == '6'){
                $data_quarter = 3;
            }else if($month == '7' || $month == '8' || $month == '9'){
                $data_quarter = 4;
            }else {
                $data_quarter = 1;
            }
        }else {
            if(($year==(date('Y')+1)) && ($month == '10' || $month == '11' || $month == '12')){
                $data_quarter = 1;
            }else{
                $data_quarter = 4;
            }
        }
        
        return ['data_year'=>$data_year,'data_quarter'=>$data_quarter];
        
    }
}