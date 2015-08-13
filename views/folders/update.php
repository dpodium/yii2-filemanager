<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model dpodium\filemanager\models\Folders */

$this->title = 'Update Folder: ' . ' ' . $model->category;
$this->params['breadcrumbs'][] = ['label' => 'Folders', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->category, 'url' => ['view', 'id' => $model->folder_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="folders-update">

    <h1><?php echo Html::encode($this->title); ?></h1>

    <?php
    echo $this->render('_form', [
        'model' => $model,
    ]);
    ?>

</div>
