Dpodium Filemanager extension
=============================
hello world

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist dpodium/yii2-filemanager "*"
```

or add

```
"dpodium/yii2-filemanager": "*"
```

to the require section of your `composer.json` file.

Execute migration here:
```
yii migrate --migrationPath=@vendor/dpodium/yii2-filemanager/migrations
yii migrate/down --migrationPath=@vendor/dpodium/yii2-filemanager/migrations
```

Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \dpodium\filemanager\AutoloadExample::widget(); ?>```