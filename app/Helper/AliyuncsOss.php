<?php
namespace App\Helper;
use OSS\OssClient;
use OSS\Core\OssException;
class AliyuncsOss
{
    private $accessKeyId = ''; 
    private $accessKeySecret = '';
    private $endpoint = "https://oss-cn-hongkong.aliyuncs.com";

    private $ossClient;

    public function __construct()
    {
        try {
            $this->accessKeyId = env('OSS_ACCESS_KEY_ID');
            $this->accessKeySecret = env('OSS_ACCESS_KEY_SECRET');
            $this->endpoint = "https://oss-cn-hongkong.aliyuncs.com";
            $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            // Set the timeout for establishing a connection
            $this->ossClient->setConnectTimeout(300);
            // Set the number of failed request retries.
            $this->ossClient->setMaxTries(5);    
            // Set the timeout for transmitting data at the Socket layer
            $this->ossClient->setTimeout(30);
            // Set whether to enable SSL certificate verification
            $this->ossClient->setUseSSL(false);
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
    * Upload files
    *@param $bucket storage space name
    *@param $file Upload file name
    *@param $content Upload file content
    */
    public function uploads($bucket, $file, $content)
    {

        try {
            $this->ossClient->uploadFile($bucket, $file, $content);
            return true;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * delete file
     * @param $bucket Storage space name
     * @param $file Delete file name
     */
    public function delete($bucket, $file)
    {
        try {
            $this->ossClient->deleteObject($bucket, $file);
            return true;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Download files
     * @param $bucket Storage space name
     * @param $file Download file name
     */
    public function download($bucket, $file)
    {
        try {
            $content = $this->ossClient->getObject($bucket, $file);
            return $content;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * rename file
     * @param $bucket Storage space name
     * @param $oldFile Old file name
     * @param $newFile new file name
     */
    public function rename($bucket, $oldFile, $newFile)
    {
        $this->ossClient->copyObject($bucket, $newFile, $bucket, $oldFile);
        $this->ossClient->deleteObject($bucket, $oldFile);
        return true;
    }

    /**
     * Determine if the file exists
     * @param $bucket Storage space name
     * @param $file file name
     */
    public function exist($bucket, $file)
    {
        try {
            $exist = $this->ossClient->doesObjectExist($bucket, $file);
            return $exist ? true : false;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }
}