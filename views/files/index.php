<?php
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model dpodium\filemanager\models\FilesSearchs */

$this->title = Yii::t('filemanager', 'Media Gallery');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header clearfix">
    <div class="pull-left">
        <h1><?php echo $this->title; ?></h1>
    </div>
    <?php
    $listActive = ($view === 'list') ? 'btn-warning' : 'btn-default';
    $gridActive = ($view === 'grid') ? 'btn-warning' : 'btn-default';
    ?>
    <div class="pull-right">
        <a href="<?php echo \Yii::$app->urlManager->createUrl(['/filemanager/files/upload']); ?>" class="btn btn-sm btn-primary">
            <i class="fa-icon fa fa-cloud-upload"></i><?php echo Yii::t('filemanager', 'Upload New'); ?>
        </a>
    </div>
</div>
<?php
echo $this->render('_search', [
    'model' => $model,
    'folderArray' => $folderArray
]);
?>
<div class="row">
    <div class="col-xs-12">
        <div class="pull-left">
            <a href="<?php echo \Yii::$app->urlManager->createUrl(['/filemanager/files']); ?>" class="btn btn-sm <?php echo $listActive; ?>">
                <i class="fa fa-align-justify"></i>
            </a>
            <a href="<?php echo \Yii::$app->urlManager->createUrl(['/filemanager/files/index', 'view' => 'grid']); ?>" class="btn btn-sm <?php echo $gridActive; ?>">
                <i class="fa fa-th-large"></i>
            </a>
        </div>        
    </div>
</div>
<div class="space-10"></div>
<?php
echo $this->render("_{$view}-view", [
    'model' => $model,
    'dataProvider' => $dataProvider,
    'uploadType' => $uploadType,
    'view' => $view,
    'viewFrom' => $viewFrom
]);
