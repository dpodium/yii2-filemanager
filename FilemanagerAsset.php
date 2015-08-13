<?php

namespace dpodium\filemanager;

use yii\web\AssetBundle;

class FilemanagerAsset extends AssetBundle {

    public $sourcePath = '@dpodium/filemanager/assets';
    public $css = [
        'css/filemanager.css',
    ];
    public $js = [
        'js/filemanager.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $publishOptions = [
        'forceCopy' => true
    ];

}
