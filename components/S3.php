<?php

namespace dpodium\filemanager\components;

use Aws\Common\Aws;
use Aws\S3\Exception\S3Exception;
use yii\base\InvalidConfigException;

class S3 {

    private $key;
    private $secret;
    private $bucket;
    protected $s3;

    public function __construct() {
        $module = \Yii::$app->controller->module;

        $this->key = isset($module->storage['s3']['key']) ? $module->storage['s3']['key'] : '';
        $this->secret = isset($module->storage['s3']['secret']) ? $module->storage['s3']['secret'] : '';
        $this->bucket = isset($module->storage['s3']['bucket']) ? $module->storage['s3']['bucket'] : '';

        if ($this->key == '') {
            throw new InvalidConfigException('Key cannot be empty!');
        }
        if ($this->secret == '') {
            throw new InvalidConfigException('Secret cannot be empty!');
        }
        if ($this->bucket == '') {
            throw new InvalidConfigException('Bucket cannot be empty!');
        }

        $param = ['key' => $this->key, 'secret' => $this->secret];

        if (isset($module->storage['s3']['region'])) {
            $param['region'] = $module->storage['s3']['region'];
        }

        if (isset($module->storage['s3']['proxy'])) {
            $param['request.options']['proxy'] = $module->storage['s3']['proxy'];
        }

        $aws = Aws::factory($param);
        $this->s3 = $aws->get('S3');
    }

    public function upload($file, $fileName, $path) {
        $result['status'] = false;

        try {
            $uploadResult = $this->s3->putObject([
                'Bucket' => $this->bucket,
                'Key' => $path . '/' . $fileName,
                'SourceFile' => $file->tempName,
                'ContentType' => $file->type,
                'ACL' => 'public-read',
            ]);

            $result['status'] = true;
            $result['objectUrl'] = $uploadResult['ObjectURL'];
            $result['uploadResult'] = $uploadResult;
        } catch (S3Exception $e) {
            echo $e . "\nThere was an error uploading the file.\n";
        }

        return $result;
    }

    public function delete($files) {
        $result['status'] = false;
        $objects = [];
        
        foreach($files as $fileKey) {
            $objects[]['Key'] = $fileKey;
        }
        try {
            $deleteResult = $this->s3->deleteObjects([
                'Bucket' => $this->bucket,
                'Objects' => $objects,
            ]);
            $result['status'] = true;
            $result['data'] = $deleteResult;
        } catch (S3Exception $e) {
            echo $e . "\nThere was an error uploading the file.\n";
        }

        return $result;
    }

    public function listObject() {
        $iterator = $this->s3->getIterator('ListObjects', array(
            'Bucket' => $this->bucket
        ));

        foreach ($iterator as $object) {
            echo $object['Key'] . "\n";
        }
    }

}
