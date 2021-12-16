<?php
/**
 * Created by PhpStorm.
 * User: sancho
 * Date: 28.05.19
 * Time: 14:32
 */
ini_set("display_errors",0);

class Install{

        private $CanSaveLog = true;
        private $logFileName = "installLog.log";
        private $ConfigFile = "config.php";
        private $configData = null;
        private $include_tar_lib = false;

        function __construct(){
            try{
                $this->initConfig();
                $this->downloadPharmaData();
            }catch (Exception $exp){
                $this->saveErrorLog($exp->getMessage(),$exp->getCode());


            }
        }
        function initConfig(){
            $rootConfigFilePath  = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$this->ConfigFile;
            if(file_exists($rootConfigFilePath)){
                include_once $rootConfigFilePath;
                if(isset($configData)&&is_array($configData)&&!empty($configData)){
                    $this->configData = $configData;
                }else{
                    throw new Exception("Cannot install plugin. Config file has incorrect data",4);
                }
            }else{
                throw new Exception("Cannot install plugin. Config file not exist",3);
            }
        }



        function saveErrorLog($msg,$errorNum){
            $save = file_put_contents($this->logFileName,date("d/m/Y H:i")."---install_error ".$errorNum.":  ".$msg."\n",FILE_APPEND);
            if(!$save){
                if(file_exists($this->logFileName)){
                    exit("install_error 2: Cannot save install error log. File ".$this->logFileName." is not writable</br>install_error ".$errorNum.": ".$msg);
                }else{
                    exit("install_error 1: Cannot save install error log. directory ".dirname(__FILE__)." is not writable</br>install_error ".$errorNum.": ".$msg);
                }
            }
            exit("install_error ".$errorNum.": ".$msg);
        }
        function downloadPharmaData(){
            $this->CheckCache();
            if(isset($this->configData["install_url"])&&!empty($this->configData["install_url"])){
                if(isset($this->configData["file_download"])&&is_array($this->configData["file_download"])){
                    foreach($this->configData["file_download"] as $nameFileDnl =>$typeDataDnl){
                        $dnlUrl = $this->configData["install_url"] .(isset($typeDataDnl["params"])?"?".http_build_query($typeDataDnl["params"]):'');
                        $tarAnswer = $this->GetTarFile($dnlUrl);
                        file_put_contents(MICRO_PHARMA_CACHDIR.$nameFileDnl.'.tar',$tarAnswer);
                        $this->FileExtract(MICRO_PHARMA_CACHDIR.$nameFileDnl.'.tar');

                    }
                }
            }else{
                throw new Exception("Cannot install plugin. install url not exist",5);
            }
        }

    function CheckCache(){
        if (file_exists(MICRO_PHARMA_CACHDIR)){
            $this->RmDirRecursiv(MICRO_PHARMA_CACHDIR);
            if(!is_writable(MICRO_PHARMA_CACHDIR))throw new Exception(" cache folder is not writable",12);
        }else{
            if(!mkdir(MICRO_PHARMA_CACHDIR)) throw new Exception("Cannot create cache folder. Please change permission",11);
        }
    }
    function GetTarFile($url){
        extract(parse_url($url));
         $getPath = $path.(isset($query)&!empty($query)?"?".$query:"");
        if(is_resource($sock = fsockopen($host, !empty($port)?$port:80, $errno, $errstr,1))){
            $result='';
            $header = "GET $getPath HTTP/1.1\r\n";
            $header.= "Host: $host\r\n";
            $header.= "Connection: Close\r\n\r\n";
            fwrite($sock,$header);
            while($answ=fgets($sock)){
                $result.=$answ;
            }
            if($answ===false && !feof($sock)) {
                throw new Exception("Cannot install plugin. Something wrong",8);
            }
            fclose($sock);
            if(strpos($result, "\r\n\r\n")===false){
                $this->error=true;
                throw new Exception("Cannot install plugin. Something wrong",7);

            }else{
                return $result= substr($result, strpos($result, "\r\n\r\n") + 4);
            }
        }else if(!$sock) {
            throw new Exception("Cannot install plugin. $errstr ($errno)",6);

        }
    }
    function RmDirRecursiv($DirPath){
        $source=opendir($DirPath);
        while($innerFile=readdir($source)){
            if($innerFile=='.'||$innerFile=='..')
                continue;
            else if(is_dir($SubFile=$DirPath.'/'.$innerFile)){
                $this->RmDirRecursiv($SubFile);
                if (!rmdir($SubFile)) throw new Exception("Cannot install plugin. Cannot remove cache folder",9);
            }
            else
                if(!unlink($SubFile)) throw new Exception("Cannot install plugin. Cannot remove cache folder",10);
        }
    }
    function FileExtract($tarName){
        if(empty($this->include_tar_lib)){
            if (file_exists($this->configData['TarPath'])){
                include_once $this->configData['TarPath'];
            }else{
                throw new Exception("tar lib not exist",13);
            }
        }
        $this->_tar=new Archive_Tar($tarName);
        if(!$res=$this->_tar->extract(dirname($tarName))){
            throw new Exception("Can't extract: ".$tarName,14);
        }
        unlink($tarName);
    }


}
