<?php
/**
 * Created by PhpStorm.
 * User: sancho
 * Date: 28.05.19
 * Time: 14:18
 */

include_once dirname(__FILE__).DIRECTORY_SEPARATOR."MicroPrice.php";
class MicroFileDB extends MicroPrice{
    protected $configFName = "config.php";
    protected $dbFName = "allitems.php";
    public static  $dbData = null;
    public static $configMicroFarma = null;
    public static $translation = null;
    function __construct($data){
            $this->shortCodeData = $data;
        if(is_null(MicroFileDB::$dbData))
            $this->includeBaseFile();
        parent::__construct();
    }
   private function includeBaseFile(){
        $configRootPath = dirname(__FILE__).DIRECTORY_SEPARATOR.$this->configFName;
        if(file_exists($configRootPath)){
            include  $configRootPath;
            if(isset($configData)&&!empty($configData)){
                MicroFileDB::$configMicroFarma = $configData;
                $allitemFilePath = MICRO_PHARMA_CACHDIR.$this->dbFName;
                if(isset($translation)&&is_array($translation)){
                    MicroFileDB::$translation = $translation;
                    if(file_exists($allitemFilePath)){
                        include $allitemFilePath;
                        MicroFileDB::$dbData = $allitems;
                    }else{
                        throw new Exception("file allitems.php not exist",2);
                    }
                }else{
                    throw new Exception("incorrect config file data. Translation variable not exist",20);
                }

            }else{
                throw new Exception("incorrect config file data",18);
            }
        }else{
            throw new Exception("file config not exist",1);
        }
    }
     function getAllTileId(){
        $tileId = array();
        foreach (MicroFileDB::$dbData as $nameProd=>$DataOneProd){
            if(isset($DataOneProd["category_id"])&&is_array($DataOneProd["category_id"])){
                $tileId[]= array_shift(array_values($DataOneProd["category_id"]));
            }
        }
        $tileId = array_unique($tileId);
        return $tileId;
    }
    function getLowestIdQty($name){
        $answer = array();
        $activeProdInfo = MicroFileDB::$dbData[$name];
        if(!empty($activeProdInfo)){
            $answer["id"] = array_shift(array_values($activeProdInfo["category_id"]));
            if(isset($activeProdInfo["items"])){
                if(isset($activeProdInfo["items"][$answer["id"]]["qty"]))
                $answer["qty"] = $activeProdInfo["items"][$answer["id"]]["qty"];
            }else if(isset($activeProdInfo["variants"])){
                foreach($activeProdInfo["variants"] as $oneDosInfo){
                    if (isset($oneDosInfo["items"])&&isset($oneDosInfo["items"][$answer["id"]])){
                        if(isset( $oneDosInfo["items"][$answer["id"]]["qty"])){
                            $answer["qty"] = $oneDosInfo["items"][$answer["id"]]["qty"];
                        }
                        break;
                    }
                }
            }
        }
        return $answer;
    }
    function getAllXchangeName(){
        $RateName = array();
        $currency = MicroFileDB::$configMicroFarma["available_currency"];
        if(!empty($currency)&&is_array($currency)){
            foreach($currency as $oneCurr){
                if(strtolower($oneCurr) == "usd") continue;
                $RateName[] = "xchg_USD_".strtoupper($oneCurr);
            }
        }
        return $RateName;
    }
    function getTranslate($world,$lang){
        $translWorld = $world;
        $CorrectLang = in_array($lang,MicroFileDB::$configMicroFarma["available_lang"])?$lang:"en";
        if(!empty($world)){
            if(isset(MicroFileDB::$translation[$CorrectLang][$world])){
                $translWorld =  MicroFileDB::$translation[$CorrectLang][$world];
            }else if($CorrectLang!="en"&&isset(MicroFileDB::$translation["en"][$world])) {
                $translWorld =  MicroFileDB::$translation["en"][$world];
            }

        }
        return $translWorld;
    }
    function getProductID($nameProd){
        $ids = array();
        $activeProdData = MicroFileDB::$dbData[$nameProd];
        if(!empty($activeProdData)&&is_array($activeProdData)){
            if(isset($activeProdData["items"])){
                foreach ($activeProdData["items"] as $id=>$dataItem){
                    $ids[] = $id;
                }
            }elseif(isset($activeProdData["variants"])){
                foreach($activeProdData["variants"] as $OneDosData){
                    if(isset($OneDosData["items"])&&is_array($OneDosData["items"])){
                        foreach($OneDosData["items"] as $id=>$DataItem){
                            $ids[] = $id;
                        }
                    }
                }
            }
        }
        return $ids;
    }
    function DataAddProdById($id){
        $data = array();
        if(isset(MicroFileDB::$dbData)&&!empty(MicroFileDB::$dbData)){
            foreach (MicroFileDB::$dbData as $nameProduct=>$prodData){
                if(isset($prodData["items"])&&isset($prodData["items"][$id])){
                    $clossest = $this->GetClosestData($prodData["items"],$prodData["items"][$id]["qty"]);
                    $data = array(
                        "name" => $nameProduct,
                        "qty" => $prodData["items"][$id]["qty"],
                    );
                    if(!empty($clossest)){
                        $data["closestId"]  = $clossest["closestId"];
                        $data["closestQty"] = $clossest["closestQty"];
                    }
                }
                else if (isset($prodData["variants"])&&is_array($prodData["variants"])){
                    foreach($prodData["variants"] as $oneDosData){
                        if(isset($oneDosData["items"])&&isset($oneDosData["items"][$id])){
                            $clossest = $this->GetClosestData($oneDosData["items"],$oneDosData["items"][$id]["qty"]);
                            $data = array(
                                            "name" => $nameProduct,
                                            "qty" => $oneDosData["items"][$id]["qty"],


                                    );
                            if(!empty($clossest)){
                                $data["closestId"]  = $clossest["closestId"];
                                $data["closestQty"] = $clossest["closestQty"];
                            }

                        }
                    }
                }

            }
        }
        return $data;
    }
    function GetClosestData($items,$qty){
        $data = array();
        $closestQty= -1;
        $closestId = -1;
        foreach($items as $id=>$qtyData){
            if($qtyData["qty"]>$qty&& ($closestQty == -1 || $closestQty > $qtyData["qty"])){
                $closestQty = $qtyData["qty"];
                $closestId = $id;
            }
        }
        if($closestQty != -1 && $closestQty != -1){
            $data = array(
                        "closestId"  => $closestId,
                        "closestQty" => $closestQty
                    );
        }
        return $data;
    }
      function detectPageLangCurr(){
        if(!isset($this->shortCodeData["lang"])){
            $this->shortCodeData["lang"] = "en";
            $PostData = get_the_terms(get_the_ID(),"language");
            if(isset($PostData)&&!empty($PostData)&&is_array($PostData)&&isset($PostData[0])&&isset($PostData[0]->description)){
                $LocalePost =unserialize($PostData[0]->description);
                if($LocalePost&&isset($LocalePost["flag_code"])){
                if(isset(MicroFileDB::$configMicroFarma["lang_map"][$LocalePost["flag_code"]]))$LocalePost["flag_code"] = MicroFileDB::$configMicroFarma["lang_map"][$LocalePost["flag_code"]];
                if(in_array($LocalePost["flag_code"],MicroFileDB::$configMicroFarma["available_lang"]))
                    $this->shortCodeData["lang"] = $LocalePost["flag_code"];
                }
                
            }
        }
        if(!isset($this->shortCodeData["curr"])){
            if(isset(MicroFileDB::$configMicroFarma["lang_currency"][$this->shortCodeData["lang"]])){
                $this->shortCodeData["curr"] = MicroFileDB::$configMicroFarma["lang_currency"][$this->shortCodeData["lang"]];
            }else{
                $this->shortCodeData["curr"] = "USD";
            }
        }
    }
}