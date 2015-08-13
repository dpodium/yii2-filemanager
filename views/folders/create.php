<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model dpodium\filemanager\models\Folders */

$this->title = 'Create Folder';
$this->params['breadcrumbs'][] = ['label' => 'Folders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="folders-create">

    <h1><?php echo Html::encode($this->title); ?></h1>

    <?php
    echo $this->render('_form', [
        'model' => $model,
    ]);
    ?>

</div>
