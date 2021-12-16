<?php
/**
 * Created by PhpStorm.
 * User: sancho
 * Date: 28.05.19
 * Time: 14:11
 */
include_once "MicroFileDB.php";
include_once "parser.php";
class MicroProduct extends MicroFileDB{
	private $selectDossData = array();
	protected $defCartPageSlug = "cart";
    function __construct($data){
            parent::__construct($data);
    }
    function render($type){
        switch($type){
            case "tile";
                return $this->tile();
            case "table":
                return $this->table();
        }
    }
    function tile(){
        if(isset($this->shortCodeData["url"])&&!empty($this->shortCodeData["url"])) {
            $data = $this->getTileData($this->shortCodeData);
            $data["buy_url"] = $this->shortCodeData["url"];
            return $this->getHtmlTile($data);
        }else{
            throw new Exception("please setup product page url",29);
        }
    }
    function table(){
       // if(isset($this->shortCodeData["url"])&&!empty($this->shortCodeData["url"])) {
        	if(isset($this->shortCodeData["dosage"])&&!empty($this->shortCodeData["dosage"]))
	        	$this->shortCodeData["dosage"] = explode(",",$this->shortCodeData["dosage"]);
            $tblData = $this->getTableData();
            $tblTranslData["add_to_cart"] = $this->getTranslate("ADD_TO_CART", $this->shortCodeData["lang"]);
            $tblTranslData["package"] = $this->getTranslate("PRODUCT_PACKAGE", $this->shortCodeData["lang"]);
            $tblTranslData["free"] = $this->getTranslate("FREE", $this->shortCodeData["lang"]);
            $tblTranslData["product_bonus_tablets"] = $this->getTranslate("PRODUCT_BONUS_TABLETS", $this->shortCodeData["lang"]);
            $tblTranslData["package"] = $this->getTranslate("PRODUCT_PACKAGE", $this->shortCodeData["lang"]);
            $tblTranslData["price"] = $this->getTranslate("PRODUCT_PRICE", $this->shortCodeData["lang"]);
            $tblTranslData["per_pill"] = $this->getTranslate("CART_PERPILL", $this->shortCodeData["lang"]);
            $tblTranslData["saving"] = $this->getTranslate("PRODUCT_SAVING", $this->shortCodeData["lang"]);
            $tblTranslData["PRODUCT_BUY"] = $this->getTranslate("PRODUCT_SAVING", $this->shortCodeData["lang"]);
            $tblTranslData["product_bonus_shipping"] = $this->getTranslate("PRODUCT_BONUS_SHIPPING", $this->shortCodeData["lang"]);

                                           
                                   
        $array_data = 'var pre_arr ='.json_encode(array("lang"=> in_array($this->shortCodeData["lang"],MicroFileDB::$configMicroFarma["available_lang"])?$this->shortCodeData["lang"]:"en", "ip"=>$_SERVER["REMOTE_ADDR"],"version"=>"3.16073",
            "host"=>$_SERVER['HTTP_HOST'], "wm" => MICRO_PHARMA_WM, "tr"=>MICRO_PHARMA_TR, "pr"=>MICRO_PHARMA_PR,"site_id"=>MICRO_PHARMA_SITE_ID, "free_ship_method_id"=>0, "sid"=>session_id(), "currency"=>in_array(strtoupper($this->shortCodeData["curr"]), MicroFileDB::$configMicroFarma["available_currency"])?$this->shortCodeData["curr"]:"USD",
            "shipping_method_id"=>$_SESSION["MICRO_PHARMA"]["active_shipping_id"],));

            file_put_contents('./js/file.js', $array_data);            
           

            return $this->getHtmlTable($tblData, $tblTranslData);
        // }else{
//             throw new Exception("please setup cart url",26);
//         }
    }
    function getTileData($ShortCodeData){
        $TileInfo = array();
        if($this->needLoadTilePrice()){
            $ids = $this->getAllTileId();
            $this->loadPrice($ids,"tile");
        }
        if($this->needLoadExchRate()){
            $rateName = $this->getAllXchangeName();
            if(!empty($rateName))
                $this->LoadExhcangeRate($rateName);
        }
        if (isset(MicroFileDB::$dbData[$ShortCodeData["name"]])&&!empty(MicroFileDB::$dbData[$ShortCodeData["name"]])){
            $modifName = str_replace(" ","-",$ShortCodeData["name"]);
            $idQty =  $this->getLowestIdQty($ShortCodeData["name"]);
            $price = $this->getTilePrice($idQty["id"]);
            $perpill =  $price > 0 && isset($idQty["qty"])? $price/$idQty["qty"] :0;
            $TileInfo["formatPrice"] = $this->recalcPrice($perpill,$ShortCodeData["curr"]);
            $TileInfo["descr"] = $this->getDesc($modifName,"short",$ShortCodeData["lang"]);
            $TileInfo["title"] = $this->getTitle($ShortCodeData["name"],$ShortCodeData["lang"]);
            $TileInfo["srcImg"] = $this->getImg($modifName);
            $TileInfo["buyTxt"] = $this->getTranslate("BUY_NOW",$ShortCodeData["lang"]);
            $TileInfo["MORE_INFO"] = $this->getTranslate("MORE_INFO", $this->shortCodeData["lang"]);
            $TileInfo["per_container"] = $this->getProductPerCont($ShortCodeData["name"],$ShortCodeData["lang"]);
        }else{
            throw new Exception("incorrect product name",17);
        }
        return $TileInfo;
    }
    function getDesc($name,$type,$lang){
        $descr = "";
        $availableType = array("short" => "pre-","long"=>"");
        $filePrefix = isset($availableType[$type])? $availableType[$type]:"short-";
        if(!empty($name)){
            $userLang = MICRO_PHARMA_CACHDIR.$lang."/descr/".$filePrefix.$name.".html";
            if(file_exists($userLang)){
                $descr =  file_get_contents($userLang);
            }else if($lang != "en"){
                $defLang = MICRO_PHARMA_CACHDIR."en/descr/".$filePrefix.$name.".html";
                if (file_exists($defLang))
                    $descr = file_get_contents($defLang);
            }
        }
        return $descr;
    }

    function getTitle($name,$lang){
        $title = "";
        $ActiveProdDAta = MicroFileDB::$dbData[$name];
        if(!empty($ActiveProdDAta)){
            if(isset($ActiveProdDAta["title"])){
                $title = $ActiveProdDAta["title"];
            }else{
                $brand =  isset($ActiveProdDAta["brand"])?$ActiveProdDAta["brand"]:false;
                $generic = $ActiveProdDAta["generic"];
                $nbng = in_array($name, MicroFileDB::$configMicroFarma['nbng_pills']);
                if(!empty($generic)&&empty($brand)&&!$nbng){
                    $title = $this->getTranslate("GENERIC",$lang)." ";
                }
            }
        }
        $title .= ucwords($brand
            ? preg_replace('/^(\w+)( (?:soft|oral jelly|sx|gold|effervescent|duralong|force|polo|shampoo))?$/i', '$1<sup>&reg;</sup>$2',
                preg_replace('/^(brand) (.+)$/i', '$2<sup>&reg;</sup>', //real brand
                    preg_replace('/^(.+)\s+brand$/i', '$1<sup>&reg;</sup>', //real brand
                        preg_replace('/^(super|extra super|top) (\w+)?$/i', '$1 $2<sup>&reg;</sup>',  //for example "super pillname®"
                            preg_replace('/(hard on)(.*)/','$1<sup>&reg;</sup>$2',$name))))) //for example "hard on®"
            : $name);

        return $title;

    }
    function getImg($name){
        $RootImgDir = MICRO_PHARMA_CACHDIR."pills/";
        $defImgRootPth = $RootImgDir."default.gif";
        $UserImgRootPth = $RootImgDir.$name.".gif";
        $relativeDirPath = str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__));
        return file_exists($UserImgRootPth) ?$relativeDirPath."/cache/pills/".$name.".gif":$relativeDirPath."pharma/cache/pills/default.gif";

    }
    function getHtmlTile($data){
        $tileTmplRootPath = MICRO_PHARMA_TMP_DIR."tile.tpl";
        if(file_exists($tileTmplRootPath)){
            ob_start();
            include $tileTmplRootPath;
            $htmlData = ob_get_contents();
            ob_end_clean();
            return $htmlData;
        }else{
            throw new Exception("tile template file not exist",21);
        }
    }
    function  getProductPerCont($name,$lang){
        if(isset(MicroFileDB::$dbData[$name])&&!empty(MicroFileDB::$dbData[$name]))
            $container_key = strtoupper(MicroFileDB::$dbData[$name]["container"]);
        if (empty($container_key))
                $container_key =  "ITEM";

         $container = $this->getTranslate("PRODUCT_PER".$container_key,$lang);
         if($container == "PRODUCT_PER".$container_key)
             $container =  $this->getTranslate("PRODUCT_PERPILL",$lang);

        return $container;
    }
    function getTableData(){
	    $this->selectDossData=array();
        if (isset(MicroFileDB::$dbData[$this->shortCodeData["name"]])&&!empty(MicroFileDB::$dbData[$this->shortCodeData["name"]])) {
            $TableData = array();
            $activeProdData = MicroFileDB::$dbData[$this->shortCodeData["name"]];

            $ids = $this->getProductID($this->shortCodeData["name"]);
            $idsLoad = $this->needLoadTablePrice($ids);
            if (!empty($idsLoad)) {
                $this->loadPrice($idsLoad, "table");
            }
            if ($this->needLoadExchRate()) {
                $rateName = $this->getAllXchangeName();
                if (!empty($rateName))
                    $this->LoadExhcangeRate($rateName);
            }
            $LangContKey = strtoupper(isset($activeProdData["container"])?$activeProdData["container"]:"pills");
            $TransContainer = $this->getTranslate($LangContKey,$this->shortCodeData["lang"]);
            if ($TransContainer == $LangContKey)$TransContainer = strtolower($LangContKey);

            if(isset($activeProdData["items"])){
            	if(!isset($this->shortCodeData["dosage"])||empty($this->shortCodeData["dosage"])||
            			(	isset($this->shortCodeData["dosage"])&&!empty($this->shortCodeData["dosage"])&&
            				is_array($this->shortCodeData["dosage"])&&
            				in_array($activeProdData['dosage'],$this->shortCodeData["dosage"])
            			)
            			){
					
							$NotFormDosKey =$activeProdData['dosage'].(isset($activeProdData["unit"])?$activeProdData["unit"]:"mg");
							$dosageKey = preg_replace("/[^[:word:]]/","_",$NotFormDosKey);
							$this->selectDossData[$dosageKey] = $NotFormDosKey;
							$TableData[$dosageKey] = $this->getOneDossageData(array(  "items"=>$activeProdData["items"],
																			"dosage"=>$activeProdData['dosage'],
																			"unit" => isset($activeProdData["unit"])?$activeProdData["unit"]:"mg",
																			"container" => $TransContainer,
																		)
											);
				}
            	
            }else if(isset($activeProdData["variants"])&&is_array($activeProdData["variants"])){
                foreach($activeProdData["variants"] as $oneDosageData){
                	if($this->shortCodeData["dosage"]&&!empty($this->shortCodeData["dosage"])&&is_array($this->shortCodeData["dosage"])){
                		if(!in_array($oneDosageData['dosage'],$this->shortCodeData["dosage"]))continue;
                	}
                	$NotFormDosKey = $oneDosageData['dosage'].(isset($activeProdData["unit"])?$activeProdData["unit"]:"mg");
                    $dosageKey = preg_replace("/[^[:word:]]/","_",$NotFormDosKey);
                    $this->selectDossData[$dosageKey] = $NotFormDosKey;
                    $oneDosageData["unit"] = isset($activeProdData["unit"])?$activeProdData["unit"]:"mg";
                    $oneDosageData["container"] = $TransContainer;
                    $TableData[$dosageKey] = $this->getOneDossageData($oneDosageData);
                }
            }else{
                throw new Exception("incorrect product allitems data",24);
            }
             if(empty($TableData))throw new Exception("incorrect dossage",31);
            return $TableData;
        }else{
            throw new Exception("incorrect product name",23);
        }
    }



    function getOneDossageData($data){
        $dosageData = array();
        $this->setupDosgeTable($data,$dosageData,$this->shortCodeData["curr"]);
        $this->calculateSaving($dosageData,$this->shortCodeData["curr"]);
        return  $dosageData;
    }

    function setupDosgeTable($dosaData,&$answerData,$currency){
        $greaеPerPillPrice = -99999;
        $dosage = !in_array($this->shortCodeData["name"],MicroFileDB::$configMicroFarma["wo_dosage"])?$dosaData["dosage"].$dosaData["unit"]." &times; ":"";
        foreach($dosaData["items"] as $id=>$DataQty){
            $price = $this->getTablePriceId($id);
            if ($price > 0){
                $answerData[$id]["price"] = MicroPrice::$tablePrice[$id]["price"];
                $perPill =  isset($DataQty["qty"])&&is_numeric($DataQty["qty"])&&$DataQty["qty"]>0 ?
                    MicroPrice::$tablePrice[$id]["price"]/$DataQty["qty"]:0;
                $answerData[$id]["per_pill"] =  $perPill;
                $answerData[$id]["per_pill_format"] = $this->recalcPrice($perPill, $currency);
                $answerData[$id]["qty"] = $DataQty["qty"];
                $answerData[$id]["package"] =$dosage.$DataQty["qty"].' '.$dosaData["container"];
                $answerData[$id]["pack_part1"] = $dosage;
                $answerData[$id]["pack_part2"] = $DataQty["qty"].' '.$dosaData["container"];
                $answerData[$id]["price_format"] = $this->recalcPrice($answerData[$id]["price"], $currency);
                $answerData[$id]["prod_bonus"] = $this->productBonus($DataQty["qty"],$id);
                $answerData[$id]["ship_bonus"] = $this->shipBonus($answerData[$id]["price"]);
                if($greaеPerPillPrice < $perPill) $greaеPerPillPrice = $perPill;
            }else{
                $answerData[$id] = array("price"=>0,"per_pill"=>0,"qty"=>0);
            }
        }
        $answerData["gratestPP"] = $greaеPerPillPrice;
    }

   function productBonus($qty,$id,$cart = false){
        $bonusData = MicroFileDB::$configMicroFarma['bonuses'];
        krsort($bonusData);
        if(!empty($bonusData)){
            if(in_array($id, MicroFileDB::$configMicroFarma["ed_items_id"])||$cart){
                foreach($bonusData as $bonusId=>$bonusInfo){
                    if($bonusInfo["qty_target"]<=$qty){
                        //print_r($bonusId);
                        if($cart){
                            $_SESSION["MICRO_PHARMA"]["bonus_id"] = $bonusId;
                            return array(
                                            "title" => $bonusInfo["title"],
                                            "package" => $bonusInfo["package"]." ".$this->getTranslate(strtoupper($bonusInfo["container"]),$this->shortCodeData["lang"]),
                                            "img_src"=> $this->getImg(strtolower(str_replace(" ","-",$bonusInfo["title"]))),
                                        );
                        }else{
                             return $bonusInfo["title"]." ".$bonusInfo["package"]." ".$this->getTranslate(strtoupper($bonusInfo["container"]),$this->shortCodeData["lang"]);
                        }
                    }
                }
            }

        }
    }
    function shipBonus($price){
       $shipBonus = "";
       if (isset(MicroFileDB::$configMicroFarma["ship_method"])){
           foreach(MicroFileDB::$configMicroFarma["ship_method"] as $shipId => $onShipMethod){
               if(isset($onShipMethod["free"])&&$price>$onShipMethod["free"]){
                    return "exist";
               }
           }
       }
        return $shipBonus;
    }
    function getHtmlTable($data,$translation){
	    $selectorData = $this->selectDossData;
        $cartUrl = isset($this->shortCodeData["url"])?$this->shortCodeData["url"]:$this->getShortCartUrl();
        $tileTmplRootPath = MICRO_PHARMA_TMP_DIR."product_table.tpl";
        if(file_exists($tileTmplRootPath)){
            ob_start();
            include $tileTmplRootPath;
            $data = ob_get_contents();
            ob_end_clean();
            return $data;
        }else{
            throw new Exception("table template file not exist",25);
        }
    }
    function getProdPackage($name,$id){
        $packStr = '';
        $tempDosage = '';
        $qty = '';
        if(isset(MicroFileDB::$dbData[$name])){
            $LangContKey = strtoupper(isset(MicroFileDB::$dbData[$name]["container"])?MicroFileDB::$dbData[$name]["container"]:"pills");
            $TransContainer = $this->getTranslate($LangContKey,$this->shortCodeData["lang"]);
            if ($TransContainer == $LangContKey)$TransContainer = strtolower($LangContKey);
            $unit = isset(MicroFileDB::$dbData[$name]["unit"])?MicroFileDB::$dbData[$name]["unit"]:"mg";
            if(isset(MicroFileDB::$dbData[$name]["items"])){
                if(isset(MicroFileDB::$dbData[$name]["items"][$id])){
                    $tempDosage = MicroFileDB::$dbData[$name]["dosage"];
                    $qty = MicroFileDB::$dbData[$name]["items"][$id]["qty"];
                }
            }else if(isset(MicroFileDB::$dbData[$name]["variants"])){
                foreach(MicroFileDB::$dbData[$name]["variants"] as $oneDosInfo){
                    if(isset($oneDosInfo["items"][$id])){
                        $tempDosage = $oneDosInfo["dosage"];
                        $qty = $oneDosInfo["items"][$id]["qty"];
                        break;
                    }
                }
            }
            $dosage = !in_array($name,MicroFileDB::$configMicroFarma["wo_dosage"])&&!empty($tempDosage)?$tempDosage.$unit." &times; ":"";
            $packStr = $dosage.$qty." ".$TransContainer;
        }
        return $packStr;
    }
    function getShortCartUrl(){ return get_site_url().'/shoppingcart';
        $cartUrl = get_site_url().'/'.$this->defCartPageSlug;
        $CarPageWpData = get_page_by_path( $this->defCartPageSlug);
        if(!empty($CarPageWpData)){
            $cartUrl = get_permalink($CarPageWpData->ID);
            $PLtermsData = get_the_terms($CarPageWpData->ID,"post_translations");
             if (!is_wp_error($PLtermsData))
            if(!empty($PLtermsData)&&isset($PLtermsData[0])&&isset($PLtermsData[0]->description)){
                $postByLang = unserialize($PLtermsData[0]->description);
                if(!empty($postByLang)&&is_array($postByLang)){
                    if(isset($postByLang[$this->shortCodeData["lang"]])){
                        $cartPostId = $postByLang[$this->shortCodeData["lang"]];
                        $cartUrl = get_permalink($cartPostId);
                    }

                }
            }
        }
        return $cartUrl;
    }
}