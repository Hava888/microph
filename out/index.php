<?php
include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.php';
class Out {

	function __construct(){
		if(empty($_GET['id']))
			return false;
		$this->query=array(
							'id'=>htmlspecialchars($_GET['id']),
							'pill'=>!empty($_GET['pill'])?htmlspecialchars($_GET['pill']):false,
						);
		$this->QuerySettings();
		$this->setCheckedOpt();

		if(array_key_exists($this->query['id'], $this->hidden_imgs)){
			$this->GetRequestedData();}
		else if(array_key_exists($this->query['id'], $this->hidden_urls))
			$this->RedirectURL();

	}

	function QuerySettings(){

		$this->extFile=array(
			'gif'=>array('filesize'=>2048,'time_out'=>60*60*24,'cache_dir'=>MICRO_PHARMA_CACHDIR.'out/imgs/','rempath'=>'imgs','header'=>'Content-Type: image/gif','pr_req'=>0),
			'php'=>array('filesize'=>2048,'time_out'=>60*60*12,'cache_dir'=>MICRO_PHARMA_CACHDIR.'out/','rempath'=>array('we_accept_methods'=>'shared/imgs/paymentmethods','urgent_message'=>'.'),'header'=>'content-type: text/html','pr_req'=>array('urgent_message'=>1,'we_accept_methods'=>0)),
			'jpg'=>array('filesize'=>6144,'time_out'=>60*60*24*30,'cache_dir'=>MICRO_PHARMA_CACHDIR.'out/imgs/blisters/','rempath'=>'shared/blisters','header'=>'content-type: image/jpeg','pr_req'=>0),
		);
		$this->hidden_urls = array(
			'special_offer_url' 		=> 'http://'.MICRO_PHARMA_FEED_HOST.'/special-offer.html',
			'certificates_url' 			=> preg_replace('/checkout.php/i', 'certificates.php', MICRO_PHARMA_CHECKOUT_SCRIPT),
			'live_support_url' 			=> 'http://livechatrx.com/hnb/',

		);

		$this->hidden_imgs = array(
			'special_offer_banner' 		=> array( 'name' => 'special-offer-banner', 'ext' => 'gif'),
			'certificates_banner_big' 	=> array( 'name' => 'certificates_big', 'ext' => 'gif'),
			'urgent_message'            => array( 'name' => 'urgent-message', 'ext' => 'php'),
			'certificates_banner_small' => array( 'name' => 'certificates', 'ext' => 'gif'),
			'we_accept_methods'			=> array( 'name' => 'index', 'ext' => 'php'),
			'we_accept_banner'			=> array( 'name' => 'we-accept', 'ext' => 'gif'),
			'htmlvalid'					=> array( 'name' => 'htmlvalid', 'ext' => 'gif'),
			'blisters'                  => array( 'name' => $this->query['pill'], 'ext' => 'jpg'),
		);
		if(!empty($this->hidden_imgs[$this->query['id']]))
			$this->CurImgName=$this->hidden_imgs[$this->query['id']]['name'];
		$this->CurImgHost	= "http://".MICRO_PHARMA_FEED_HOST;
	}

	function setCheckedOpt(){

		$this->CurImgExt=$this->hidden_imgs[$this->query['id']]['ext'];

		if(empty($this->extFile[$this->CurImgExt]))
			return ;

		foreach (array_keys($this->extFile[$this->CurImgExt]) as $OptName){
			$tempVar=$this->extFile[$this->CurImgExt];
			$this->$OptName=is_array($tempVar[$OptName])?(!empty($tempVar[$OptName][$this->query['id']])?$tempVar[$OptName][$this->query['id']]:0)
				:(!empty($tempVar[$OptName])?$tempVar[$OptName]:0);
		}
	}
	function NeedOutdatedFile(){
		if( (!file_exists($this->cache_dir.$this->CurImgName.'.'.$this->CurImgExt)) # item not exists
			|| ( (filesize($this->cache_dir.$this->CurImgName.'.'.$this->CurImgExt) < $this->filesize) && (filemtime($this->cache_dir.$this->CurImgName.'.'.$this->CurImgExt)+$this->time_out < time()) ) # no image on feedhost - check for new image /1 days/
			|| (filemtime($this->cache_dir.$this->CurImgName.'.'.$this->CurImgExt)+$this->time_out < time()))
			return true;
		else return false;

	}

	function CheckPerm(){
		if(!file_exists($this->cache_dir)){
			if(!mkdir($this->cache_dir,0777,true)){
				exit('Can\'t create dir '.$this->cache_dir);
			}
		}
		$flag='check_permissions_'.rand(1, 9999);
		if($check_perm = fopen($this->cache_dir.$flag, "w")){
			fclose($check_perm);
			unlink($this->cache_dir.$flag);
			return true;
		}
		return false;
	}
	function GetRequestedData(){
		if(!$this->NeedOutdatedFile()){
			 $fullremotepath = $this->CurImgHost."/".$this->rempath."/".(($this->query['pill'])
																			?"?wm=".MICRO_PHARMA_WM."&tr=".MICRO_PHARMA_TR."&site_id=".MICRO_PHARMA_SITE_ID."&pill={$this->query['pill']}"
																			:($this->CurImgName.($this->CurImgExt ? '.'.$this->CurImgExt : '').($this->pr_req ? '?pr='.MICRO_PHARMA_PR : '')));


			$this->data= $this->getContents($fullremotepath, false);
			if($this->CheckPerm()) {
				$handle = fopen($this->cache_dir . $this->CurImgName . '.' . $this->CurImgExt, "w");
				fwrite($handle, $this->data);
				fclose($handle);
			}
		}
		else {
			$filename = $this->cache_dir . $this->CurImgName . '.' . $this->CurImgExt;
			$handle = fopen($filename, "r");
			$contents = fread($handle, filesize($filename));
			fclose($handle);
			$this->data = $contents;
		}
		header($this->header);
	 	echo  $this->data;
	}
	function getContents($url, $useCurl = false, $file_use_include_path = false)
	{
		if(stristr($url, 'out/?id=urgent_message') !== FALSE)
		{
			if (is_readable($_ugm=$this->cache_dir.'urgent-message.php') && strlen($contents=file_get_contents($_ugm)))
				return $contents;
		}

		if (function_exists('file_get_contents') && (ini_get('allow_url_fopen') || $file_use_include_path) && !$useCurl) {
			return $contents = file_get_contents($url, $file_use_include_path);
		}

		$url_parts = parse_url($url);

		if (stristr($url, "http") === FALSE)
			$url = "http://".$url;

		if ($useCurl && extension_loaded('curl'))
		{
			$c = curl_init($url);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			$contents = curl_exec($c);
			curl_close($c);
		}
		elseif (is_resource($sock = @fsockopen(gethostbyname($url_parts['host']), 80, $errno, $errstr)))
		{
			@fputs($sock, "GET ".$url_parts['path']."?".$url_parts['query']." HTTP/1.0\r\nHost:".$url_parts['host']."\r\n\r\n");
			while ($answer = @fgets($sock))
				$contents .= $answer;
			@fclose($sock);
			$contents = substr($contents, strpos($contents, "\r\n\r\n") + 4);
		}
		else
			return false;

		return $contents;
	}
	function RedirectURL(){
		Header('HTTP/1.1 301 Moved Permanently');
		Header('Location: '. $this->hidden_urls[$this->query['id']] .($this->query['lang'] ? '?lang='. $this->query['lang'] : ''));
	}


}
new Out();