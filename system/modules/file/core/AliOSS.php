<?php

Ibos::import('application.modules.file.extensions.alioss.aliyun', true);

namespace application\modules\file\core;

use Aliyun\OSS\OSSClient;
use application\core\utils\Ibos;
use CException;

class AliOSS extends CloudOSS
{

    private $_client; // 客户端实例
    private $_accessKeyId; // 验证key
    private $_accessKeySecret; // 验证码
    private $_endPoint; // 终端
    private $_bucket; // 唯一文件夹

    /**
     * 初始化，实例一个客户端入口
     */
    public function __construct($config)
    {
        if (!isset($config['bucket']) || !isset($config['endpoint']) || !isset($config['keyid']) || !isset($config['keysecret'])) {
            throw new CException(Ibos::t('file.default', 'Cloud service not open success'));
        }
        $this->_bucket = $config['bucket'];
        $this->_endPoint = $config['endpoint'];
        $this->_accessKeyId = $config['keyid'];
        $this->_accessKeySecret = $config['keysecret'];
        $array = array(
            'Endpoint' => $this->_endPoint,
            'AccessKeyId' => $this->_accessKeyId,
            'AccessKeySecret' => $this->_accessKeySecret,
        );
        $this->_client = $this->createClient($array);
    }

    /**
     * 创建client客户端入口
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return object
     */
    public function createClient($config)
    {
        return OSSClient::factory($config);
    }

    /**
     * 获取accessKeyId
     * @return string
     */
    public function getAccessKeyId()
    {
        return $this->_accessId;
    }

    /**
     * 设置accessKeyId
     */
    public function setAccessKeyId($value)
    {
        $this->_accessId = $value;
    }

    /**
     * 获取accessKeySecret
     * @return string
     */
    public function getAccessKeySecret()
    {
        return $this->_accessKey;
    }

    /**
     * 设置accessKeySecret
     */
    public function setAccessKeySecret($value)
    {
        $this->_accessKey = $value;
    }

    /**
     * 获取bucket列表
     * @return array 返回bucket对象集合的数组
     */
    public function listBuckets()
    {
        return $this->_client->listBuckets();
    }

    /**
     * 创建一个bucket
     * @param string $bucket bucket名
     */
    public function createBucket($bucket)
    {
        return $this->_client->createBucket(array(
            'Bucket' => $bucket,
        ));
    }

    /**
     * 获取bucketAcl
     * @param string $bucket bucket名
     */
    public function getBucketAcl($bucket)
    {
        return $this->_client->getBucketAcl(array(
            'Bucket' => $bucket,
        ));
    }

    /**
     * 删除bucket
     * @param string $bucket bucket名
     */
    public function deleteBucket($bucket)
    {
        return $this->_client->deleteBucket(array(
            'Bucket' => $bucket,
        ));
    }

    /**
     * 获取某个文件夹下的列表
     * @param array $config 配置参数(必要参数：Bucket，可选参数：Prefix（前缀，即哪个文件夹）,Delimiter（分隔符）...等)
     * @return array 返回对象的数组集合数组
     */
    public function listObject($config)
    {
        $arr['Bucket'] = $this->_bucket;
        if (isset($config['prefix'])) {
            $arr['Prefix'] = $config['prefix'];
        }
        if (isset($config['delimiter'])) {
            $arr['Delimiter'] = $config['delimiter'];
        }
        $listObj = $this->_client->listObjects($arr);
        return $listObj;
    }

    /**
     * 上传文件或创建文件、文件夹
     * @param array $config 配置参数（必要参数：Bucket，Key（带路径的上传后文件名），Content（内容），若Content为资源，则ContentLength（写入大小）项必须）
     * @return type
     */
    public function putObject($config)
    {
        $arr = array(
            'Bucket' => $this->_bucket,
            'Key' => $config['key'],
            'Content' => $config['content']
        );
        if (isset($config['contentLength'])) {
            $arr['ContentLength'] = $config['contentLength'];
        }
        $result = $this->_client->putObject($arr);
        return $result;
    }

    /**
     * 复制文件
     * @param array $config 配置参数（必要参数：SourceBucket（源bucket），SourceKey（源路径），DestBucket（目标bucket），DestKey（目标路径））
     * @return type
     */
    public function copyObject($config)
    {
        $arr = array(
            'SourceBucket' => $config['sourceBucket'],
            'SourceKey' => $config['sourceKey'],
            'DestBucket' => $config['destBucket'],
            'DestKey' => $config['destKey'],
        );
        $result = $this->_client->copyObject($arr);
        return $result;
    }

    /**
     * 删除文件
     * @param array $config 配置参数（必要参数：Bucket,Key（带路径的文件名）)
     * @return type
     */
    public function deleteObject($config)
    {
        $arr = array(
            'Bucket' => $this->_bucket,
            'Key' => $config['key'],
        );
        $result = $this->_client->deleteObject($arr);
        return $result;
    }

    /**
     * 删除多个文件
     * @param array $config 配置参数（必要参数：Keys（要删除的文件数组）)
     * @return boolean
     */
    public function deleteObjects($config)
    {
        foreach ($config['keys'] as $key) {
            $this->deleteObject(array('Key' => $key));
        }
        return true;
    }

    /**
     * 获取某个文件对象
     * @param array $config 配置参数（必要参数：Bucket,Key（带路径的文件名）)
     * @return string
     */
    public function getObject($config)
    {
        $arr = array(
            'Bucket' => $this->_bucket,
            'Key' => $config['key'],
        );
        $object = $this->_client->getObject($arr);
        return $object;
    }

    /**
     * 获取文件内容
     * @param array $config 配置参数（必要参数：Bucket,Key（带路径的文件名）)
     * @return string
     */
    public function getObjectContent($config)
    {
        $object = $this->getObject($config);
        return stream_get_contents($object->getObjectContent());
    }

    /**
     * 取得存储文件的网站地址，包含bucket文件夹
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->_endPoint . '/' . $this->_bucket . '/';
    }

}
