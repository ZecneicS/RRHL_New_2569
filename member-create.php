<?php
/* views/member/create.php  (modules/manage/views/member/create.php) */

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\UserCreateForm */
/* @var $offices array id => office_name */
/* @var $provinces array code => name_th */

$this->title = 'เพิ่มผู้ใช้งาน';

$hasSelect2 = class_exists('\kartik\select2\Select2');
?>
<div class="card">
    <div class="card-header"><h3 class="card-title"><?= Html::encode($this->title) ?></h3></div>
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
    <div class="card-body">
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $msg): ?>
            <div class="alert alert-<?= Html::encode($type) ?>"><?= Html::encode($msg) ?></div>
        <?php endforeach; ?>

        <div class="row">
            <div class="col-md-4"><?= $form->field($model, 'username')->textInput(['autocomplete' => 'off']) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'email') ?></div>
            <div class="col-md-4"><?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'new-password'])->hint('เว้นว่างเพื่อให้ระบบสุ่มรหัสผ่านให้ (ถ้าเปิดใช้งานไว้)') ?></div>
        </div>
        <div class="row">
            <div class="col-md-2"><?= $form->field($model, 'prefix') ?></div>
            <div class="col-md-5"><?= $form->field($model, 'firstname') ?></div>
            <div class="col-md-5"><?= $form->field($model, 'lastname') ?></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php if ($hasSelect2): ?>
                    <?= $form->field($model, 'department_id')->widget(\kartik\select2\Select2::className(), [
                        'data' => $offices,
                        'options' => ['placeholder' => '-- เลือกสังกัด/หน่วยงาน --'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                <?php else: ?>
                    <?= $form->field($model, 'department_id')->dropDownList($offices, ['prompt' => '-- เลือกสังกัด/หน่วยงาน --']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4"><?= $form->field($model, 'position') ?></div>
            <div class="col-md-4"><?= $form->field($model, 'tel') ?></div>
            <div class="col-md-4"><?= $form->field($model, 'province')->dropDownList($provinces, ['prompt' => '-- เลือกจังหวัด --']) ?></div>
        </div>
        <?= $form->field($model, 'must_change_pw')->checkbox() ?>
    </div>
    <div class="card-footer">
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
        <?= Html::a('ปิดหน้าต่าง', 'javascript:window.close();', ['class' => 'btn btn-light']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
