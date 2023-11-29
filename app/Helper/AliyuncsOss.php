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
    private $bucket;

    public function __construct($accessKeyId = '',$accessKeySecret = '',$endpoint = '',$bucket = '')
    {
        try {
            $this->accessKeyId = $accessKeyId ?? env('OSS_ACCESS_KEY_ID');
            $this->accessKeySecret = $accessKeySecret ?? env('OSS_ACCESS_KEY_SECRET');
            $this->endpoint = $endpoint ?? env('OSS_ACCESS_KEY_ENDPOINT');
            $this->bucket = $bucket ?? env('OSS_ACCESS_KEY_BUCKET');
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

    public function setBucket($bucket)
    {
        $this->bucket = $bucket ? $bucket : $this->bucket;
    }

    /**
    * Upload files
    *@param $file Upload file name
    *@param $content Upload file content
    */
    public function uploads($file, $content,$bucket = '')
    {
        try {
            $this->setBucket($bucket);
            $this->ossClient->uploadFile($this->bucket, $file, $content);
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
    public function delete($file,$bucket = '')
    {
        try {
            $this->setBucket($bucket);
            $this->ossClient->deleteObject($this->bucket, $file);
            return true;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Download files
     * @param $file Download file name
     * @param $bucket Storage space name
     */
    public function download($file,$bucket = '')
    {
        try {
            $this->setBucket($bucket);
            $content = $this->ossClient->getObject($this->bucket, $file);
            return $content;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * rename file
     * @param $oldFile Old file name
     * @param $newFile new file name
     * @param $bucket Storage space name
     * @return bool
     */
    public function rename($oldFile, $newFile,$bucket = '')
    {
        try {
            $this->setBucket($bucket);
            $this->ossClient->copyObject($this->bucket, $oldFile, $this->bucket, $newFile);
            if($oldFile != $newFile){
                $this->ossClient->deleteObject($this->bucket, $oldFile);
            }
            return true;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Determine if the file exists
     * @param $file file name
     * @param $bucket Storage space name
     */
    public function exist($file,$bucket = '')
    {
        try {
            $this->setBucket($bucket);
            $exist = $this->ossClient->doesObjectExist($this->bucket, $file);
            return $exist ? true : false;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * move file
     * @param $oldFile Old file name
     * @param $newFile new file name
     * @param $bucket Storage space name
     * @return bool
     */
    public function move($oldFile, $newFile,$bucket = '')
    {
        try {
            $this->setBucket($bucket);
            $this->ossClient->copyObject($this->bucket, $oldFile, $this->bucket, $newFile);
            if($oldFile != $newFile){
                $this->ossClient->deleteObject($this->bucket, $oldFile);
            }
            return true;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * create dir
     * @param $dir dir name
     * @param $bucket Storage space name
     * @return bool
     */
    public function CreateDir($dir,$bucket = '')
    {
        try {
            $dir = trim($dir,'/');
            if($dir == ''){
                return false;
            }
            $dir = $dir . '/';
            $this->setBucket($bucket);
            $this->ossClient->putObject($this->bucket, $dir, '');
            return true;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * delete dir
     * @param $dir dir name
     * @param $bucket Storage space name
     * @return bool
     */
    public function DeleteDir($dir,$bucket = '')
    {
        try {
            $this->setBucket($bucket);
            $option = array(
                OssClient::OSS_MARKER => null,
                // Fill in the complete path of the directory to be deleted, which does not include the bucket name.
                OssClient::OSS_PREFIX => trim($dir,'/').'/',
                OssClient::OSS_DELIMITER=>'',
             );
            $bool = true;
            while ($bool){
                $result = $this->ossClient->listObjects($this->bucket,$option);
                $objects = array();
                if(count($result->getObjectList()) > 0){
                    foreach ($result->getObjectList() as $key => $info){
                        $objects[] = $info->getKey();
                    }
                    // Delete directory and all files under it
                    $delObjects = $this->ossClient->deleteObjects($this->bucket, $objects);
                    foreach ($delObjects as $info){
                        strval($info);
                    }
                }
                if($result->getIsTruncated() === 'true'){
                    $option[OssClient::OSS_MARKER] = $result->getNextMarker();
                }else{
                    $bool = false;
                }
            }
            return false;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }
}