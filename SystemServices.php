<?php

namespace app\models;

use Yii;
use DateTime;
use DatePeriod;
use DateInterval;

/**
 * This is the model class for data function services.
 */
class SystemServices
{
    public static function getIsNumber($value=null,$dec=null,$text=null,$after=null)
    {
        if ($value !== null || $value > 0) {
            $result = number_format($value, $dec) . $after;
        } else {
            $result = $text !== null ? $text : '-';
        }
        return $result;
    } //end actionReportdate

    public static function getDashboarddate($strDate)
    {
        $strYear = (int)substr($strDate, 0, 4) + 543;
        $strMonth = (int)substr($strDate, 4, 2);
        $strDay = (int)substr($strDate, 6, 2);

        $strMonthCut = array("", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
        $strMonthThai = $strMonthCut["$strMonth"];
        return "$strDay $strMonthThai $strYear";
    } //end actionReportdate

    public static function getReportdate($strDate)
    {
        $date_d_m = Yii::$app->formatter->asDate($strDate, 'php:j M ');
        $date_y = Yii::$app->formatter->asDate($strDate, 'php:Y') + 543;
        $date = $date_d_m . $date_y;

        return $date;
    } //end actionReportdate

    public static function getThdate($strDate)
    {
        if ($strDate) {
            $date_d_m = Yii::$app->formatter->asDate($strDate, 'php:j M ');
            $date_y = Yii::$app->formatter->asDate($strDate, 'php:Y') + 543;
            $date = $date_d_m . $date_y;
        } else {
            $date = null;
        }
        return $date;
    } //end getThdate

    public static function getThShortdate($strDate, $time = null)
    {
        $strYear = (int)substr($strDate, 0, 4) + 543;
        $strMonth = (int)substr($strDate, 5, 2);
        $strDay = (int)substr($strDate, 8, 2);
        $strMonthCut = array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
        $strMonthThai = $strMonthCut[$strMonth];
        $date = "$strDay $strMonthThai $strYear";
        $date .= ($time) ? ' - ' . substr($strDate, 11, 5) . 'น.' : '';
        return $date;
    } //end actionReportdate

    public static function getThShortdate2($strDate, $time = null)
    {
        $strYear = (int)substr($strDate, 2, 2) + 43;
        $strMonth = (int)substr($strDate, 5, 2);
        $strDay = (int)substr($strDate, 8, 2);
        $strMonthCut = array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
        $strMonthThai = $strMonthCut[$strMonth];
        $date = "$strDay $strMonthThai $strYear";
        $date .= ($time) ? ' ' . substr($strDate, 11, 5) . 'น.' : '';
        return $date;
    } //end actionReportdate

    public static function getThFulldate($strDate, $time = null)
    {
        $strYear = (int)substr($strDate, 0, 4) + 543;
        $strMonth = (int)substr($strDate, 5, 2);
        $strDay = (int)substr($strDate, 8, 2);
        $strMonthCut = array("", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
        $strMonthThai = $strMonthCut[$strMonth];
        $date = "$strDay $strMonthThai $strYear";
        $date .= ($time) ? ' - ' . substr($strDate, 11, 5) . 'น.' : '';
        return $date;
    } //end actionReportdate

    public static function getThFullDM($strDate)
    {
        //$strYear = (int)substr($strDate,0,4)+543;
        $strMonth = (int)substr($strDate, 5, 2);
        $strDay = (int)substr($strDate, 8, 2);
        $strMonthCut = array("", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
        $strMonthThai = $strMonthCut[$strMonth];
        $date = "$strDay $strMonthThai ";
        //$date .= ($time)? ' - ' . substr($strDate,11,5) . 'น.':'';
        return $date;
    } //end actionReportdate

    public static function getThFullMY($strDate)
    {
        $strYear = (int)substr($strDate, 0, 4) + 543;
        $strMonth = (int)substr($strDate, 5, 2);
        $strMonthCut = array("", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
        $strMonthThai = $strMonthCut[$strMonth];
        $date = "$strMonthThai&nbsp;&nbsp;$strYear";
        return $date;
    }

    public static function getThRange($startDate = null, $endDate = null, $time = null)
    {
        if ($startDate !== null && $endDate !== null) {
            if ($startDate == $endDate) {
                $date = SystemServices::getThFullDate($startDate);
            } else {
                $strMonth1 = (int) substr($startDate, 5, 2);
                $strMonth2 = (int) substr($endDate, 5, 2);
                if ($strMonth1 == $strMonth2) {
                    $strDay = (int) substr($startDate, 8, 2);
                    $date = $strDay . ' - ' . SystemServices::getThFullDate($endDate);
                } else {
                    $strYear1 = (int) substr($startDate, 0, 4);
                    $strYear2 = (int) substr($endDate, 0, 4);
                    if ($strYear1 == $strYear2) {
                        $date = SystemServices::getThFullDM($startDate) . ' - ' . SystemServices::getThFullDM($endDate) . ($strYear1 + 543);
                    } else {
                        $date = SystemServices::getThFullDate($startDate) . ' - ' . SystemServices::getThFullDate($endDate);
                    }
                }
            }
        } else if ($startDate == null && $endDate == null) {
            $date = null;
        } else if ($startDate !== null) {
            $date = SystemServices::getThFullDate($startDate);
        } else {
            $date = SystemServices::getThFullDate($endDate);
        }

        return $date;
    }

    public static function getThRangeShort($startDate = null, $endDate = null, $time = null)
    {
        if (!$time) {
            $startDate = substr($startDate, 0, 10);
            $endDate = substr($endDate, 0, 10);
        }
        if ($startDate !== null && $endDate !== null) {
            if ($startDate == $endDate) {
                $date = SystemServices::getThShortdate($startDate);
            } else {
                $strMonth1 = (int) substr($startDate, 5, 2);
                $strMonth2 = (int) substr($endDate, 5, 2);
                if ($strMonth1 == $strMonth2) {
                    $strDay = (int) substr($startDate, 8, 2);
                    $date = $strDay . ' - ' . SystemServices::getThShortdate($endDate);
                } else {
                    $date = SystemServices::getThShortdate($startDate) . ' - ' . SystemServices::getThShortdate($endDate);
                }
            }
        } else if ($startDate == null && $endDate == null) {
            $date = null;
        } else if ($startDate !== null) {
            $date = SystemServices::getThShortdate($startDate);
        } else {
            $date = SystemServices::getThShortdate($endDate);
        }

        return $date;
    }

    public static function getThRangeShortFY($start = null, $end = null, $time = null)
    {
        $startDate = substr($start, 0, 10);
        $endDate = substr($end, 0, 10);
        if ($startDate !== null && $endDate !== null) {
            if ($startDate == $endDate) {
                $date = SystemServices::getThShortdate($startDate);
            } else {
                $strMonth1 = (int) substr($startDate, 5, 2);
                $strMonth2 = (int) substr($endDate, 5, 2);
                if ($strMonth1 == $strMonth2) {
                    $strDay = (int) substr($startDate, 8, 2);
                    $date = $strDay . ' - ' . SystemServices::getThShortdate($endDate);
                } else {
                    $date = SystemServices::getThShortdate($startDate) . ' - ' . SystemServices::getThShortdate($endDate);
                }
            }
        } else if ($startDate == null && $endDate == null) {
            $date = null;
        } else if ($startDate !== null) {
            $date = SystemServices::getThShortdate($startDate);
        } else {
            $date = SystemServices::getThShortdate($endDate);
        }
        if ($time) {
            $isTime = ' ' . substr($start, 11, 5) . ' - ' . substr($end, 11, 5) . 'น.';
            $date .= $isTime;
        }

        return $date;
    }

    public static function getAge($strDate)
    {
        if ($strDate) {
            $from = new DateTime($strDate);
            $to   = new DateTime('today');
            $age = $from->diff($to)->y;
        } else {
            $age = null;
        }
        return $age;
    } //end getThdate

    public static function formatTimeDiff($startStr = null, $endStr = null)
    {
        // if (!empty($startStr) && !empty($endStr)) {
        $start = new DateTime($startStr);
        $end = new DateTime($endStr);
        $diff = $start->diff($end);

        $parts = [];

        if ($diff->d > 0) {
            $parts[] = $diff->d . ' วัน';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' ชม.';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . ' นาที';
        }
        if (!empty($parts)) {
            $result = implode(', ', $parts);
        } else {
            $result = "";
        }

        return $result;
    }

    public static function businessTimeDiff($time_stamp_accept, $time_stamp_reply)
    {
        $ts_accept = $time_stamp_accept instanceof DateTime ? $time_stamp_accept : new DateTime($time_stamp_accept);
        $ts_reply = $time_stamp_reply instanceof DateTime ? $time_stamp_reply : new DateTime($time_stamp_reply);

        if ($ts_accept >= $ts_reply) return "0 วัน, 0 ชม., 0 นาที";

        // 1. คำนวณจำนวนวันทั้งหมด (รวมเสาร์-อาทิตย์)
        $totalDays = $ts_accept->diff($ts_reply)->days;

        // 2. หาจำนวนวันหยุด (เสาร์-อาทิตย์) ระหว่างช่วงวันที่
        // วิธีนี้จะแม่นยำและเร็วกว่าการ Loop ทุกวัน
        $workdays = 0;
        $period = new DatePeriod(
            (clone $ts_accept)->setTime(0, 0, 0),
            new DateInterval('P1D'),
            (clone $ts_reply)->setTime(23, 59, 59)
        );

        foreach ($period as $day) {
            $dayOfWeek = (int)$day->format('N');
            if ($dayOfWeek < 6) $workdays++; // 1-5 คือ จันทร์-ศุกร์
        }

        // 3. คำนวณเวลาที่หายไปของ "วันแรก" และ "วันสุดท้าย" (เพราะเรานับวันเต็มไปแล้ว)
        $totalSeconds = $workdays * 86400;

        // ถ้าวันเริ่มเป็นวันหยุด ไม่ต้องนับเวลาของวันนั้น
        if ((int)$ts_accept->format('N') < 6) {
            // หักเวลาที่ผ่านไปแล้วในวันแรกออก (นับเฉพาะเวลาที่เหลือของวัน)
            $startDaySeconds = ($ts_accept->format('H') * 3600) + ($ts_accept->format('i') * 60) + $ts_accept->format('s');
            $totalSeconds -= $startDaySeconds;
        }

        // ถ้าวันจบเป็นวันหยุด ไม่ต้องนับเวลาของวันนั้น
        if ((int)$ts_reply->format('N') < 6) {
            // หักเวลาที่ยังไม่ถึงในวันสุดท้ายออก
            $endDaySeconds = 86400 - (($ts_reply->format('H') * 3600) + ($ts_reply->format('i') * 60) + $ts_reply->format('s'));
            $totalSeconds -= $endDaySeconds;
        }

        // กรณีที่เริ่มและจบในวันทำงานวันเดียวกัน และหักลบกันจนติดลบ ให้ใช้การลบตรงๆ
        if ($totalSeconds < 0 && $ts_accept->format('Y-m-d') === $ts_reply->format('Y-m-d')) {
            $totalSeconds = $ts_reply->getTimestamp() - $ts_accept->getTimestamp();
        }

        // 4. แปลงวินาทีเป็น format ที่ต้องการ
        $d = floor($totalSeconds / 86400);
        $h = floor(($totalSeconds % 86400) / 3600);
        $i = floor(($totalSeconds % 3600) / 60);

        if ($d > 0) {
            $parts[] = $d . ' วัน';
        }
        if ($h > 0) {
            $parts[] = $h . ' ชม.';
        }
        if ($i > 0) {
            $parts[] = $i . ' นาที';
        }
    
        if (!empty($parts)) {
            $result = implode(', ', $parts);
        } else {
            $result = "";
        }

        return $result;
    }
}
