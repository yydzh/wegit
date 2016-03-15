<?php
/**
 * This is the PHP SDK API For SAE Storage Service.
 *
 *
 * See COPYING for license information.
 *
 * @author lazypeople
 * @copyright Copyright (c) 2013, Sina App Engine.
 * @package sae
 */
 
include_once dirname(__FILE__) . '/swiftclient.php';
 
class SaeStorage extends SaeObject 
{
    private $accessKey  = '';
    private $secretKey  = '';
    private $errMsg     = 'success';
    private $errNum     = 0;
    private $appName    = '';
    private $restUrl    = '';
    private $filePath   = '';
    private $basedomain = 'stor.sinaapp.com';
    private $cdndomain  = 'sae.sinacdn.com';
    protected $swift_conn;
    
    /**
     * Class constructor
     *
     * @param string $accessKey AccessKey of Appname
     * @param string $secretKey SecretKey of Appname
     */
    function __construct($accessKey = NULL, $secretKey = NULL)
    {
 
        if (empty($accessKey)) {
            $this->accessKey = SAE_ACCESSKEY;
        } else {
            $this->accessKey = $accessKey;
        }
        if (empty($secretKey)) {
            $this->secretKey = SAE_SECRETKEY;
        } else {
            $this->secretKey = $secretKey;
        }
        $this->appName = $_SERVER[ 'HTTP_APPNAME' ];
        $this->swift_conn = new CF_Connection($this->accessKey,$this->secretKey,$this->appName);
    }
 
    /**
     * ��Ӧ����Ȩ����
     *
     * ����Ҫ��������APP������ʱʹ��
     *
     * @param string $akey��Ӧ�õ�accessKey 
     * @param string $skey��Ӧ�õ�secretKey 
     * @param string _appName, Ӧ����
     * @return void 
     * @ignore
     */
    public function setAuth( $akey , $skey , $_appName = '' )
    {
        if( $_appName == '') {
            $this->appName = $_SERVER[ 'HTTP_APPNAME' ];
        } else {
            $this->appName = $_appName;
        }
        $this->accessKey = $akey;
        $this->secretKey = $skey;
        $this->swift_conn = new CF_Connection($this->accessKey,$this->secretKey,$this->appName);
    }
 
 
 
    /**
     * ��ȡ������Ϣ
     * 
     * @desc
     * 
     * @access public
     * @return void 
     * @exception none
     */
    public function errmsg()
    {
        $ret = $this->errMsg." url(".$this->filePath.")";
        $this->restUrl = '';
        $this->errMsg = 'Success';
        return $ret;
    }
 
    /**
     * ��ȡ������
     * 
     * @desc
     * 
     * @access public
     * @return void 
     * @exception none
     */
    public function errno()
    {
        $ret = $this->errNum;
        $this->errNum = 0;
        return $ret;
    }
 
 
    /**
     * ��ȡ��ǰ���ڲ�����Ӧ����
     * 
     * @desc
     * 
     * @access public
     * @return void 
     * @exception none
     * @ignore
     */
    public function getAppname()
    {
        return $this->appName;
    }
 
    /**
     * ��ȡ�ļ�CDN ��ַ
     *
     * Example:
     * <code>
     * #Get a CDN url
     * $stor = new SaeStorage();
     * $cdn_url = $stor->getCDNUrl("domain","cdn_test.txt");
     * </code>
     *
     * @param string $domain Domain name
     * @param string $filename Filename you save
     * @return string. 
     */
    public function getCDNUrl( $domain, $filename ) 
    {
        $domain = strtolower(trim($domain));
        $filename = $this->formatFilename($filename);
 
        if ( SAE_CDN_ENABLED ) {
            $filePath = "http://".$this->appName.'.'.$this->cdndomain . "/.app-stor/$domain/$filename";
        } else {
            $domain = $this->getDom($domain);
            $filePath = "http://".$domain.'.'.$this->basedomain . "/$filename";
        }
        return $filePath;
    }
 
    /**
     * ��ȡ�ļ�storage���ʵ�ַ
     *
     * Example:
     * <code>
     * #Get the url of a stored file
     * $stor = new SaeStorage();
     * $file_url = $stor->getUrl("domain","cdn_test.txt");
     * </code>
     *
     * @param string $domain Domain name
     * @param string $filename Filename you save
     * @return string. 
     */
    public function getUrl( $domain, $filename ) 
    {
        $domain = strtolower(trim($domain));
        $filename = $this->formatFilename($filename);
        $domain = $this->getDom($domain);
 
        $this->filePath = "http://".$domain.'.'.$this->basedomain . "/$filename";
        return $this->filePath;
    }
 
    /**
     * Set File url.
     *
     * @param string $domain 
     * @param string $filename The filename you wanna set
     * @return string. 
     * @ignore
     */
    private function setUrl( $domain , $filename )
    {
        $domain = strtolower(trim($domain));
        $filename = $this->formatFilename($filename);
        $this->filePath = "http://".$domain.'.'.$this->basedomain . "/$filename";
    }
 
 
     /**
     * ������д��洢
     *
     * Example:
     * <code>
     * # Write some content into a storage file
     * #
     * $storage = new SaeStorage();
     * $domain = 'domain';
     * $destFileName = 'write_test.txt';
     * $content = 'Hello,I am from the method of write'
     * $attr = array('encoding'=>'gzip');
     * $result = $storage->write($domain,$destFileName, $content, -1, $attr, true);
     *
     * </code>
     *
     *
     * @param string $domain Domain name
     * @param string $destFileName The destiny fileName.
     * @param string $content The content of the file
     * @param int    $size The length of file content,the overflower will be truncated and by default there is no limit.
     * @param array  $attr File attributes, set attributes refer to SaeStorage :: setFileAttr () method
     * @param boolean $compress 
     *  #Note: Whether gzip compression.
     *         If true, the file after gzip compression and then stored in Storage,
     *         often associated with $attr=array('encoding'=>'gzip') used in conjunction
     * @return mixed 
     *  #Note: If success,return the url of the file
     *         If faild;return false
     */
    public function write( $domain, $destFileName, $content, $size = -1, $attr = array(), $compress = false )
    {
        $domain = $this->parseDomain(trim($domain));
        $destFileName = $this->formatFilename($destFileName);
 
        if (empty($domain) or empty($destFileName)) {
            $this->errMsg = 'The value of parameter (domain,destFileName,content) can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        if ( $size > -1 )
            $content = substr( $content, 0, $size );
 
        $srcFileName = tempnam(SAE_TMP_PATH, 'SAE_STOR_UPLOAD');
        if ($compress) {
            file_put_contents("compress.zlib://" . $srcFileName, $content);
        } else {
            file_put_contents($srcFileName, $content);
        }
 
        $re = $this->upload($domain, $destFileName, $srcFileName, $attr);
        unlink($srcFileName);
        return $re;
    }
 
    /**
     * ���ļ��ϴ���洢
     *
     * Example:
     * <code>
     * #
     * $storage = new SaeStorage();
     * $domain = 'domain';
     * $destFileName = 'write_test.txt';
     * $srcFileName = $_FILE['tmp_name']
     * $attr = array('encoding'=>'gzip');
     * $result = $storage->upload($domain,$destFileName, $srcFileName, -1, $attr, true);
     *
     * </code>
     *
     * The `domain` must be Exist
     *
     * @param string $domain Domain name
     * @param string $destFileName The destiny fileName.
     * @param string $srcFileName The source of the uoload file
     * @param array  $attr File attributes, set attributes refer to SaeStorage :: setFileAttr () method
     * @param boolean $compress 
     *  #Note: Whether gzip compression.
     *         If true, the file after gzip compression and then stored in Storage,
     *         often associated with $attr=array('encoding'=>'gzip') used in conjunction
     * @return mixed 
     *  #Note: If success,return the url of the file
     *         If faild;return false
     */
    public function upload( $domain, $destFileName, $srcFileName, $attr = array(), $compress = false )
    {
        $domain = $this->parseDomain(trim($domain));
        $destFileName = $this->formatFilename($destFileName);
 
        if ( empty($domain) or empty($destFileName) or empty($srcFileName)) {
            $this->errMsg = 'The value of parameter (domain,destFile,srcFileName) can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        if ($compress) {
            $srcFileNew = tempnam( SAE_TMP_PATH, 'SAE_STOR_UPLOAD');
            file_put_contents("compress.zlib://" . $srcFileNew, file_get_contents($srcFileName));
            $srcFileName = $srcFileNew;
        }
        $parseAttr = $this->parseFileAttr($attr);
        $this->setUrl( $this->getDom($domain), $destFileName );
 
        try {
            $container = $this->swift_conn->get_container($domain);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -122;
            return false;
        }
        
        try {
            $object = $container->create_object($destFileName);
            $object->__getMimeType($destFileName);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -123;
            return false;
        }
        
        try {
            $result = $object->load_from_filename($srcFileName);
            if (count($attr)) {
                $this->setFileAttr($domain,$destFileName,$attr);
            }
            return $this->getUrl($domain,$destFileName);
        } catch (Exception $e) {
            $this->errMsg = sprintf('Failed to store to filesystem!(%s)',$e->getMessage());
            $this->errNum = 121;
            return false;
        }
    }
 
    /**
     * ��ȡָ��domain�µ��ļ����б�
     *
     * <code>
     * <?php
     * // �г� Domain ������·����photo��ͷ���ļ�
     * $stor = new SaeStorage();
     *
     * $num = 0;
     * while ( $ret = $stor->getList("test", "photo", 100, $num ) ) {
     *      foreach($ret as $file) {
     *          echo "{$file}\n";
     *          $num ++;
     *      }
     * }
     * 
     * echo "\nTOTAL: {$num} files\n";
     * ?>
     * </code>
     *
     * @param string $domain    �洢��,�����߹���ƽ̨.storageҳ��ɽ��й���
     * @param string $prefix    ·��ǰ׺
     * @param int $limit        ��������,���100��,Ĭ��10��
     * @param int $offset       ��ʼ������limit��offset֮�����Ϊ10000�������˷�Χ�޷��г���
     * @return array ִ�гɹ�ʱ�����ļ��б����飬���򷵻�false
     */
    public function getList( $domain, $prefix=NULL, $limit=10, $offset = 0 )
    {
        $domain = $this->parseDomain(trim($domain));
        $limit += $offset;
 
        if ( $domain == '' ) {
            $this->errMsg = 'The value of parameter (domain) can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        try {
            $container = $this->swift_conn->get_container($domain);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -122;
            return false;
        }
 
        try {
            $list_detail = $container->get_objects($limit,NULL,$prefix);
            $list_detail_array = $this->std_class_object_to_array($list_detail);
            $list_detail_new = array();
            foreach($list_detail_array as $small) {
                $list_detail_new[] = $small['name']; 
            }
            $total_num = count($list_detail_new);
            $file_list = array();
            if ( $total_num < $offset ) return array();
            for ( $i = $offset; $i < $total_num; $i++) {
                $file_list[] = $list_detail_new[$i];
            }
            return $file_list;
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -110;
            return false;
        }
    }
 
    /**
     * ��ȡָ��Domain��ָ��Ŀ¼�µ��ļ��б�
     *
     * @param string $domain    �洢��
     * @param string $path      Ŀ¼��ַ
     * @param int $limit        ���η����������ƣ�Ĭ��100�����1000
     * @param int $offset       ��ʼ����
     * @param int $fold         �Ƿ��۵�Ŀ¼
     * @return array ִ�гɹ�ʱ�����б����򷵻�false
     */
    public function getListByPath( $domain, $path = NULL, $limit = 100, $offset = 0, $fold = true )
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $limit += $offset;
        $domain = $this->parseDomain(trim($domain));
 
        if ( $domain == '' ) {
            $this->errMsg = 'the value of parameter (domain) can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        try {
            $container = $this->swift_conn->get_container($domain);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -122;
            return false;
        }
 
        if($fold) {
            $delimiter = '/';
        } else {
            $delimiter = NULL;
        }
 
        try {
            if($path != ''){$path = $path."/";}
            $result = $container->get_objects($limit,NULL,$path,NULL,$delimiter,true);
            $file_list = array();
            $total_num = count($result);
            if ( $total_num < $offset ) return array();
            for ( $i = $offset; $i < $total_num; $i++) {
                $file_list[] = $result[$i];
            }
            $result = $file_list;
            if ($fold) {
                $list['dirNum'] = 0;
                $list['fileNum'] = 0;
                $list['dirs'] = array();
                $list['files'] = array();
                foreach ( $result as $item ) {
                    if ( isset( $item['subdir'] ) ) {
                        $list['dirs'][] = array(
                            'name' => basename($item['subdir']),
                            'fullName' => $item['subdir']
                            );
                        $list['dirNum'] ++;
                    } else {
                        $file = array(
                            'Name' => basename($item['name']),
                            'fullName' => $item['name'],
                            'length' => $item['bytes'],
                            'uploadTime' => strtotime($item['last_modified']) + 60 * 60 * 8
                            );
                        if ( isset($item['X-Sws-Object-Meta-Expires-Rule']) ) $file['expires'] = $headers['X-Sws-Object-Meta-Expires-Rule'];
                        $list['files'][] = $file;
                        $list['fileNum'] ++;
                    }
                }
            } else {
                $list = array();
                foreach ( $result as $item ) {
                    $list[] = array(
                        'Name' => basename($item['name']),
                        'fullName' => $item['name'],
                        'length' => $item['bytes'],
                        'uploadTime' => strtotime($item['last_modified'])
                        );
                }
            }
            return $list;
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -113;
            return false;
        }               
    }
 
    /**
     * ��ȡָ��domain�µ��ļ�����
     *
     *
     * @param string $domain    �洢��,�����߹���ƽ̨.storageҳ��ɽ��й���
     * @param string $path      Ŀ¼(��ûʵ��)
     * @return array ִ�гɹ�ʱ�����ļ��������򷵻�false
     */
    public function getFilesNum( $domain, $path = NULL )
    {
        $domain = $this->parseDomain(trim($domain));
 
        if ( $domain == '' ) {
            $this->errMsg = 'the value of parameter (domain) can not be empty!';
            $this->errNum = -101;
            return false;
        }
        try {
            $info = $this->swift_conn->get_container($domain);
            $info_array = $this->std_class_object_to_array($info);
            return $info_array['object_count']; 
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -114;
            return false;
        }        
    }
 
    /**
     * ��ȡ�ļ�����
     *
     * @param string $domain    �洢��
     * @param string $filename  �ļ���ַ
     * @param array  $attrKey    ����ֵ,�� array("fileName", "length")����attrKeyΪ��ʱ���Թ������鷽ʽ���ظ��ļ����������ԡ�
     * @return array ִ�гɹ������鷽ʽ�����ļ����ԣ����򷵻�false
     */
    public function getAttr( $domain, $filename, $attrKey=array() )
    {
        $domain = $this->parseDomain(trim($domain));
        $filename = $this->formatFilename($filename);
 
        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        try {
            $container = $this->swift_conn->get_container($domain);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -122;
            return false;
        }
 
        $this->setUrl( $this->getDom($domain), $filename );
 
        try {
            $object = $container->get_object($filename);
            $object = $this->std_class_object_to_array($object);
            if ( !empty($object['last_modified']) ) {
                $file_attr = array(
                    'fileName'=>$object['name'],
                    'datetime'=>strtotime($object['last_modified']),
                    'content_type'=>$object['content_type'],
                    'length'=>$object['content_length'],
                    'md5sum'=>$object['etag'],
                    'expires'=>array_key_exists('Expires', $object['metadata'])?$object['metadata']['Expires']:NULL
                    );
                if (count($attrKey) != 0) {
                    $tmp_array = array();
                    foreach ($attrKey as $small) {
                        $tmp_array[$small] = $file_attr[$small];
                    }
                    $file_attr = $tmp_array;
                }
            } else {
                $file_attr = false;
            }
            return $file_attr;  
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -115;
            return false;
        }              
    }
 
 
    /**
     * ����ļ��Ƿ����
     *
     * @param string $domain    �洢��
     * @param string $filename  �ļ���ַ
     * @return bool 
     */
    public function fileExists( $domain, $filename )
    {
        $domain = $this->parseDomain(trim($domain));
        $filename = $this->formatFilename($filename);
 
        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }
        $file_exist = $this->getAttr($domain,$filename);
        return ($file_exist === false)?false:true;
    }
 
    /**
     * ��ȡ�ļ�������
     *
     * @param string $domain 
     * @param string $filename 
     * @return string �ɹ�ʱ�����ļ����ݣ����򷵻�false
     */
    public function read( $domain, $filename )
    {
        $domain = $this->parseDomain(trim($domain));
        $filename = $this->formatFilename($filename);
 
        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }
        $this->setUrl( $this->getDom($domain), $filename );
 
        try {
            $container = $this->swift_conn->get_container($domain);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -122;
            return false;
        }
 
        try {
            $object = $container->get_object($filename);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -127;
            return false;
        }
        
        try {
            $data = $object->read();
            return $data; 
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -116;
            return false;
        }
               
    }
 
    /**
     * ɾ���ļ�
     *
     * @param string $domain 
     * @param string $filename 
     * @return bool 
     */
    public function delete( $domain, $filename )
    {
        $domain = $this->parseDomain(trim($domain));
        //$filename = $this->formatFilename($filename);
 
        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }
        $this->setUrl( $this->getDom($domain), $filename );
 
        try {
            $container = $this->swift_conn->get_container($domain);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -122;
            return false;
        }
 
        try {
            $result = $container->delete_object($filename);
            return $result;
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -117;
            return false;
        }
                
    }
 
    /**
     * �����ļ�����
     *
     * Ŀǰ֧�ֵ��ļ�����
     *  - expires: ��������泬ʱ�����ù����domain expires�Ĺ���һ��
     *  - encoding: ����ͨ��Webֱ�ӷ����ļ�ʱ��Header�е�Content-Encoding��
     *  - type: ����ͨ��Webֱ�ӷ����ļ�ʱ��Header�е�Content-Type��
     *  - private: �����ļ�Ϊ˽�У����ļ����ɱ����ء�
     *
     * <code>
     * <?php
     * $stor = new SaeStorage();
     * 
     * $attr = array('expires' => '1 y');
     * $ret = $stor->setFileAttr("test", "test.txt", $attr);
     * if ($ret === false) {
     *      var_dump($stor->errno(), $stor->errmsg());
     * }
     * ?>
     * </code>
     *
     * @param string $domain 
     * @param string $filename  �ļ���
     * @param array $attr       �ļ����ԡ���ʽ��array('attr0'=>'value0', 'attr1'=>'value1', ......);
     * @return bool 
     */
    public function setFileAttr( $domain, $filename, $attr = array() )
    {
        $domain = $this->parseDomain(trim($domain));
        $filename = $this->formatFilename($filename);
 
        if ( $domain == '' || $filename == '' ) {
            $this->errMsg = 'the value of parameter domain,filename can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        $parseAttr = $this->parseFileAttr($attr);
        if ($parseAttr == false) {
            $this->errMsg = 'the value of parameter attr must be an array, and can not be empty!';
            $this->errNum = -101;
            return false;
        }
        try {
            $container = $this->swift_conn->get_container($domain);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -122;
            return false;
        }
 
        try {
            $object = $container->get_object($filename);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -127;
            return false;
        }
        
        $object->metadata = $attr;
        try {
            $result = $object->sync_metadata();
            return $result;
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -118;
            return false;
        }
        
    }
 
    /**
     * ����Domain����
     *
     * Ŀǰ֧�ֵ�Domain����
     *  - expires: ��������泬ʱ
     *  ˵����
     *  - expires ��ʽ��[modified] TIME_DELTA������modified 1y����1y��modified�ؼ�������ָ��expireʱ��������ļ����޸�ʱ�䡣Ĭ��expireʱ���������access time�����TIME_DELTAΪ���� Cache-Control header�ᱻ����Ϊno-cache��
     *  - TIME_DELTA��TIME_DELTA��һ����ʾʱ����ַ��������磺 1y3M 48d 5s
     *  - Ŀǰ֧��s/m/h/d/w/M/y
     *  - expires_type ��ʽ:TYPE [modified] TIME_DELTA,TYPEΪ�ļ���mimetype������text/html, text/plain, image/gif������expires-type����֮���� , ���������磺text/html 48h,image/png modified 1y
     *  - allowReferer: ����Referer������
     *  - private: �Ƿ�˽��Domain
     *  - 404Redirect: 404��תҳ�棬ֻ���Ǳ�Ӧ��ҳ�棬��Ӧ��Storage���ļ�������http://appname.sinaapp.com/404.html��http://appname-domain.stor.sinaapp.com/404.png
     *  - tag: Domain��顣��ʽ��array('tag1', 'tag2')
     * <code>
     * <?php
     * // �����������
     * $expires = '1 d
     * ';
     *
     * // ����������
     * $allowReferer = array();
     * $allowReferer['hosts'][] = '*.elmerzhang.com';       // ������ʵ���Դ������ǧ��Ҫ�� http://��֧��ͨ���*��?
     * $allowReferer['hosts'][] = 'elmer.sinaapp.com';
     * $allowReferer['hosts'][] = '?.elmer.sinaapp.com';
     * $allowReferer['redirect'] = 'http://elmer.sinaapp.com/'; // ����ʱ��ת���ĵ�ַ����������ת����APP��ҳ�棬�Ҳ���ʹ�ö�����������������û������ô�����ֱ�Ӿܾ����ʡ�
     * //$allowReferer = false;  // ���Ҫ�ر�һ��Domain�ķ��������ܣ�ֱ�ӽ�allowReferer����Ϊfalse����
     * 
     * $stor = new SaeStorage();
     * 
     * $attr = array('expires'=>$expires, 'allowReferer'=>$allowReferer);
     * $ret = $stor->setDomainAttr("test", $attr);
     * if ($ret === false) {
     *      var_dump($stor->errno(), $stor->errmsg());
     * }
     *
     * ?>
     * </code>
     *
     * @param string $domain 
     * @param array $attr       Domain���ԡ���ʽ��array('attr0'=>'value0', 'attr1'=>'value1', ......);
     * @return bool 
     */
    public function setDomainAttr( $domain, $attr = array() )
    {
        $domain = $this->parseDomain(trim($domain));
 
        if ( $domain == '' )
        {
            $this->errMsg = 'The value of parameter domain can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        $parseAttr = $this->parseDomainAttr($attr);
 
        if ($parseAttr == false) {
            $this->errMsg = 'The value of parameter attr must be an array, and can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        try {
            $container = $this->swift_conn->get_container($domain);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -122;
            return false;
        }
 
        $container->metadata = $attr;
        try {
            $result = $container->sync_metadata();
            return($result);
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -119;
            return false;
        }
              
    }
 
    /**
     * ��ȡdomain��ռ�洢�Ĵ�С
     *
     * @param string $domain 
     * @return int 
     */
    public function getDomainCapacity( $domain )
    {
        $domain = $this->parseDomain(trim($domain));
        if (empty($domain)) {
            $this->errMsg = 'The value of parameter \'$domain\' can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        try {
           $info = $this->swift_conn->get_container($domain); 
        } catch (Exception $e) {
            $this->errMsg = $e->getMessage();
            $this->errNum = -120;
            return false;
        }
 
        $info_array = $this->std_class_object_to_array($info);
        return $info_array['bytes_used'];
    }
 
 
    /**
     * ����һ��domain
     * 
     * @desc
     * 
     * @access private
     * @param $domain='' 
     * @param $attr=array('private'=>false 
     * @return void 
     * @exception none
     * @ignore
     */
    public function createDomain( $domain='', $attr = array('private'=>false) )
    {
        $domain = strtolower($domain);
        if ( strlen( $domain ) > 100 || strlen( $domain ) < 5 ) {
            return array( 'errno'=>-102, 'errmsg'=>'Domain length invalid(5,100)!domain('.$domain.')' );
        }
 
        if ( Empty( $domain ) ) {
            $this->errMsg = 'The value of parameter \'domain\' can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        $domain_explode = explode("-", $domain);
        if (count($domain_explode) != 2) {
            $this->errMsg = 'The value of parameter \'domain\' is not legit!';
            $this->errNum = -101;
            return false;
        } else {
            $domain = $domain_explode[1];
        }
 
        try {
            $this->swift_conn->create_container($domain);
            try {
                if (count($attr)) {
                    $this->setDomainAttr($domain,$attr);
                }
                return true;
            } catch (Exception $e) {
                $this->errMsg = sprintf("Set domain attr failed, %s", $e->getMessage());
                $this->errNum = -107;
                return false;
            }
        } catch (Exception $e) {
            $this->errMsg = sprintf("Create domain failed, %s", $e->getMessage());
            $this->errNum = -104;
            return false;           
        }
    }
 
 
    /**
     * ��ȡdomain�б�
     * 
     * @desc
     * 
     * @access public
     * @return void 
     * @exception none
     */
    public function listDomains()
    {
        try {
            $ret = $this->swift_conn->get_containers(0);
            $ret = $this->std_class_object_to_array($ret);
            foreach ($ret as $small) {
                $retnew[] = $this->appName.'-'.$small['name'];
            }
            return $retnew;
        } catch (Exception $e) {
            $this->errNum = -108;
            $this->errMsg = $e->getMessage();
            return false;
        }
    }
 
 
    /**
     * ��ȡ��������
     * 
     * @desc
     * 
     * @access public
     * @param $domain='' 
     * @return void 
     * @exception none
     */
    public function getDomainAttr( $domain='' )
    {
        $domain = strtolower($domain);
        if (empty($domain)) {
            $this->errMsg = 'The value of parameter \'domain\' can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        $domain_explode = explode("-", $domain);
        if (count($domain_explode) != 2) {
            $this->errMsg = 'The value of parameter \'domain\' is not legit!';
            $this->errNum = -101;
            return false;
        } else {
            $domain = $domain_explode[1];
        }
 
        try {
            $ret = $this->swift_conn->get_container($domain);
            $info_array = $this->std_class_object_to_array($ret);
            $retmsg = array(
                'expires'=>$info_array['metadata']['X-Sws-Container-Meta-Expires'],
                'expires_type'=>$info_array['metadata']['X-Sws-Container-Meta-Expires-Type'],
                'fileNum'=>$info_array['object_count'],
                'tag'=>json_decode($info_array['metadata']['X-Sws-Container-Meta-Tags'],true),
                'dataSize'=>(int)$info_array['bytes_used'],
                );
            if ( isset( $info_array['read'] ) ) {
                $rrules = explode(',', $info_array['read']);
                if ( ! in_array('.r:*', $rrules) ) {
                    $retmsg['allowReferer'] = array();
                    $retmsg['allowReferer']['hosts'] = array();
                    foreach ( $rrules as $rrule ) {
                        if ( substr($rrule, 0, 3) == '.r:' ) {
                            $retmsg['allowReferer']['hosts'][] = substr($rrule, 3);
                        } elseif ( substr($rrule, 0, 4) == '.rd:' ) {
                            $retmsg['allowReferer']['redirect'] = substr($rrule, 4);
                        }
                    }
                    if ( !$retmsg['allowReferer']['hosts'] ) {
                        $retmsg['private'] = true;
                    }
                } else {
                    $retmsg['private'] = false;
                }
            } else {
                $retmsg['private'] = true;
            }
            return $retmsg;
        } catch (Exception $e) {
            $this->errNum = -109;
            $this->errMsg = $e->getMessage();
            return false;
        }
        
    }
 
 
    /**
     * ɾ��һ��domain
     * 
     * @desc
     * 
     * @access public
     * @param $domain 
     * @param $force=0 
     * @return void 
     * @exception none
     * @ignore
     */
    public function deleteDomain( $domain , $force = 0 )
    {
        $domain = strtolower($domain);
        if ( empty( $domain ) ) {
            $this->errMsg = 'The value of parameter \'domain\' can not be empty!';
            $this->errNum = -101;
            return false;
        }
 
        $domain_explode = explode("-", $domain);
        if (count($domain_explode) != 2) {
            $this->errMsg = 'The value of parameter \'domain\' is not legit!';
            $this->errNum = -101;
            return false;
        } else {
            $domain = $domain_explode[1];
        }
 
        // ѭ��ɾ��container���ļ�
        $files = $this->getList($domain);
        while ( is_array($files) && count($files) > 0 ) {
            foreach ($files as $file) {
                $this->delete($domain, $file);
            }
            $files = $this->getList($domain);
        }
 
        try {
            $this->swift_conn->delete_container($domain);
            return true;
        } catch (Exception $e) {
            $this->errNum = -105;
            $this->errMsg = $e->getMessage();
            return false;
        }       
    }
 
    /**
     * @ignore
     */
    public function runFile( $domain,  $filename)
    {
        $this->errMsg = 'this function is discarded';
        $this->errNum = -221;
        return false;
    }
 
    /**
     * domainƴ��
     * @param string $domain 
     * @param bool $concat 
     * @return string 
     * @author Elmer Zhang
     * @ignore
     */
    protected function getDom($domain, $concat = true) {
        $domain = strtolower(trim($domain));
 
        if ($concat) {
            if( strpos($domain, '-') === false ) {
                $domain = $this->appName .'-'. $domain;
            }
        } else {
            if ( ( $pos = strpos($domain, '-') ) !== false ) {
                $domain = substr($domain, $pos + 1);
            }
        }
        return $domain;
    }
 
 
    /**
     * Format Filename.
     *
     * @param string $filename 
     * @return string 
     * @ignore
     */
    private function formatFilename($filename) 
    {
        $filename = trim($filename);
        $encodings = array( 'UTF-8', 'GBK', 'BIG5' );
        $charset = mb_detect_encoding( $filename , $encodings);
        if ( $charset !='UTF-8' ) {
            $filename = mb_convert_encoding( $filename, "UTF-8", $charset);
        }
 
        $filename = preg_replace('/\/\.\//', '/', $filename);
        $filename = ltrim($filename, '/');
        $filename = preg_replace('/^\.\//', '', $filename);
        while ( preg_match('/\/\//', $filename) ) {
            $filename = preg_replace('/\/\//', '/', $filename);
        }
        return $filename;
    }
 
    /**
     * @ignore
     */
    protected function parseDomainAttr($attr) 
    {
        $parseAttr = array();
 
        if ( !is_array( $attr ) || empty( $attr ) ) {
            return false;
        }
 
        foreach ( $attr as $k => $a ) {
            switch ( strtolower( $k ) ) {
                case '404redirect':
                    if ( !empty($a) && is_string($a) ) {
                        $parseAttr['404Redirect'] = trim($a);
                    }
                    break;
                case 'private':
                    $parseAttr['private'] = $a ? true : false;
                    break;
                case 'expires':
                    $parseAttr['expires'] = $a;
                    break;
                case 'expires_type':
                    $parseAttr['expires_type'] = $a;
                    break;
                case 'allowreferer':
                    if ( isset($a['hosts']) && is_array($a['hosts']) && !empty($a['hosts']) ) {
                        $parseAttr['allowReferer'] = array();
                        $parseAttr['allowReferer']['hosts'] = $a['hosts'];
 
                        if ( isset($a['redirect']) && is_string($a['redirect']) ) {
                            $parseAttr['allowReferer']['redirect'] = $a['redirect'];
                        }
                    } else {
                        $parseAttr['allowReferer']['host'] = false;
                    }
                    break;
                case 'tag':
                    if (is_array($a) && !empty($a)) {
                        $parseAttr['tag'] = array();
                        foreach ($a as $v) {
                            $v = trim($v);
                            if (is_string($v) && !empty($v)) {
                                $parseAttr['tag'][] = $v;
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        }
 
        return $parseAttr;
    }
 
    /**
     * @ignore
     */
    protected function parseFileAttr($attr) 
    {
        $parseAttr = array();
 
        if ( !is_array( $attr ) || empty( $attr ) ) {
            return false;
        }
 
        foreach ( $attr as $k => $a ) {
            switch ( strtolower( $k ) ) {
                case 'expires':
                    $parseAttr['expires'] = $a;
                    break;
                case 'encoding':
                    $parseAttr['encoding'] = $a;
                    break;
                case 'type':
                    $parseAttr['type'] = $a;
                    break;
                case 'private':
                    $parseAttr['private'] = intval($a);
                    break;
                default:
                    break;
            }
        }
 
        return $parseAttr;
    }
 
     /**
     * @ignore
     */
    public function parseDomain( $domain ) 
    {
        $domain = strtolower($domain);
        if (strstr($domain,'-')) {
            list($account, $container) = explode('-', $domain);
            return $container;
        } else {
            return $domain;
        }
    }
 
     /**
     * @ignore
     */
    protected function std_class_object_to_array($stdclassobject)
    {
        $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
        $array = array();
        foreach ($_array as $key => $value) {
            $value = (is_array($value) || is_object($value)) ? $this->std_class_object_to_array($value) : $value;
            $array[$key] = $value;
        }
 
        return $array;
    }
}