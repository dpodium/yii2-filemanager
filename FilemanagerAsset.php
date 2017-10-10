<?php

namespace dpodium\filemanager;

use yii\web\AssetBundle;

class FilemanagerAsset extends AssetBundle {

    public $sourcePath = '@dpodium/filemanager/assets';
    public $css = [
        'css/filemanager.css'
    ];
    public $js = [
        'js/filemanager.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'kartik\file\FileInputAsset',
        'kartik\editable\EditableAsset',
        'kartik\select2\Select2Asset',
        'rmrevin\yii\fontawesome\AssetBundle'
    ];

    /**
     * uncomment in localhost for debug purpose
     */
//    public $publishOptions = [
//        'forceCopy' => true
//    ];
}
