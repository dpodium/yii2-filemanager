<?php

namespace dpodium\filemanager;

use yii\web\AssetBundle;

class FilemanagerAsset extends AssetBundle {

    public $sourcePath = '@vendor/dpodium/yii2-filemanager/assets';
    public $css = [
        'css/filemanager.css',
    ];
    public $js = [
        'js/jquery.endless-scroll.js',
        'js/jquery.jscroll.js',
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
