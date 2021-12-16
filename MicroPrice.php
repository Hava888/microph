<?php
/**
 * Created by PhpStorm.
 * User: sancho
 * Date: 28.05.19
 * Time: 18:09
 */

class MicroPrice{

    private $cacheFNameTablePrice = "micropharma_price_table.data";
    private $cacheFNameTilePrice = "micropharma_price_tile.data";
    private $cacheFNameExchRate = "micropharma_exch_rate.data";
    public static $loadPriceTile = false;
    public static $loadExchRate = false;
    public static $tilePrice = null;
    public static $exchangeRates = null;
    public static $tablePrice = null;
    private $timeSavePrice = 1296000; //15 days
    private $timeSaveExchange = 3600; //1 hour
    function __construct(){
        $this->tileRootFPath = MICRO_PHARMA_CACHDIR.$this->cacheFNameTilePrice;
        $this->exchangeRootFPath = MICRO_PHARMA_CACHDIR.$this->cacheFNameExchRate;
        $this->tableRootFPath = MICRO_PHARMA_CACHDIR.$this->cacheFNameTablePrice;
    }
    function needLoadTilePrice(){
        if(!MicroPrice::$loadPriceTile){
            if(file_exists($this->tileRootFPath)){
                if($data = unserialize(file_get_contents($this->tileRootFPath))){
                    if(time()<($this->timeSavePrice+$data["time"])){
                        if(isset($data["price"])) return false;
                    }
                }
            }
            MicroPrice::$loadPriceTile = true;
            return true;
        }else{
            return false;
        }
    }
    function needLoadTablePrice($ids){
        $nedLoadIds = array();
        if(!empty($ids)&&is_array($ids)){
            if(file_exists($this->tableRootFPath)){
                $tablePrice = unserialize(file_get_contents($this->tableRootFPath));
                if($tablePrice){
                        foreach($ids as $oneID){
                            if(!isset($tablePrice[$oneID])|| time()>($this->timeSavePrice + $tablePrice[$oneID]["time"])){
                                $nedLoadIds[] = $oneID;
                            }
                        }
                }else{
                    $nedLoadIds = $ids;
                }

            }else{
                $nedLoadIds = $ids;
            }
        }
        return $nedLoadIds;

    }
    function loadPrice($priceId,$mod = false){
        $priceFullUrl = MICRO_PHARMA_SCRIPTS_HOST."/cgi-bin/prices_remote.cgi?pr=".MICRO_PHARMA_PR."&wm=".MICRO_PHARMA_WM."&tr=".MICRO_PHARMA_TR."&items=".implode(",",$priceId);
        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 5   // Timeout in seconds
            )
        ));
        $answer = $this->getDistanceData($priceFullUrl);
        $price = $this->parseLoadPrice($answer);
        if(!empty($price)){
            if($mod == "tile"){
                $dataSave = array("time"=>time(),"price"=>$price);
                if(!file_put_contents($this->tileRootFPath,serialize($dataSave))){
                    throw new Exception("cannot save tile price. Check permission",16);
                }
            }else if($mod == "table"){
                $newTablePrice = array();
                if (file_exists($this->tableRootFPath)){
                    if($oldTablePrice = unserialize(file_get_contents($this->tableRootFPath))){
                        if(is_array($oldTablePrice)){
                            $newTablePrice = $oldTablePrice;
                        }
                    }
                }
                foreach($price as $idProd=>$price){
                        $newTablePrice[$idProd]=array(
                            "price" => $price,
                            "time" => time(),
                        );
                }
                if(!file_put_contents($this->tableRootFPath,serialize($newTablePrice))){
                    throw new Exception("cannot save table price. Check permission",22);
                }
            }
        }
    }
    function parseLoadPrice($strPrice){
        $parseData = array();
        $FirLevelSplit = explode("&",$strPrice);
        foreach($FirLevelSplit as $onePriceRow){
            $finalSplitPrice = explode("=",$onePriceRow);
            if(count($finalSplitPrice)==2){
                $parseData[$finalSplitPrice[0]]=$finalSplitPrice[1];
            }
        }
        return $parseData;
    }
    function getTilePrice($id){
        $price = 0.00;
        if(!empty($id)){
            if(is_null(MicroPrice::$tilePrice))
                if(file_exists($this->tileRootFPath)){
                    if($data = unserialize(file_get_contents($this->tileRootFPath))){
                        MicroPrice::$tilePrice = $data["price"];
                    }
                }
            if(isset(MicroPrice::$tilePrice[$id])) $price = MicroPrice::$tilePrice[$id];
        }
        return $price;
    }
    function needLoadExchRate(){
        if(!MicroPrice::$loadExchRate){
            if(file_exists($this->exchangeRootFPath)){
                if($rateData = unserialize(file_get_contents($this->exchangeRootFPath))){
                    if(isset($rateData["time"])&&($rateData["time"]+$this->timeSaveExchange)>time()){
                        return false;
                    }
                }
            }
            MicroPrice::$loadExchRate = true;
            return true;
        }else{
            return false;
        }
    }
    function LoadExhcangeRate($rates){
            $loadData = array();
             $xchangeFullUrl = MICRO_PHARMA_SCRIPTS_HOST."/cgi-bin/prices_remote.cgi?pr=".MICRO_PHARMA_PR."&wm=".MICRO_PHARMA_WM."&tr=".MICRO_PHARMA_TR."&nocustom=&fixed_campaign_id=&items=".implode("%2C",$rates);
             $answer = $this->getDistanceData($xchangeFullUrl);
             if(!empty($answer)){
                 $parceRate = array();
                $firstLevSplit = explode("&",$answer);
                foreach($firstLevSplit as $oneRate){
                    $rateVal = explode("=",$oneRate);
                    if(is_array($rateVal)&&count($rateVal) == 2){
                        $parceRate[$rateVal[0]] = $rateVal[1];
                    }
                }
                 $loadData = array("time"=>time(),"rate"=>$parceRate);
                 if(!file_put_contents($this->exchangeRootFPath,serialize($loadData))){
                     throw new Exception("cannot save exchange rate. Check permission",19);
                 }
             }
    }
    function getDistanceData($url){
        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 5   // Timeout in seconds
            )
        ));
        if($answer = file_get_contents($url, 0, $context)){
            return $answer;
        }else{
            $error = error_get_last();
            throw new Exception($error['message'],15);
        }
    }
    function recalcPrice($price,$currency){
        $correctCurr = !empty($currency)&&in_array(strtoupper($currency),MicroFileDB::$configMicroFarma["available_currency"])? strtoupper($currency):"USD";
        if(is_null(MicroPrice::$exchangeRates)){
            if(file_exists($this->exchangeRootFPath)){
                if($answer = unserialize(file_get_contents($this->exchangeRootFPath))){
                    if(isset($answer["rate"])){
                        MicroPrice::$exchangeRates = $answer["rate"];
                    }
                }
            }
        }
        $symbolRate = isset(MicroFileDB::$configMicroFarma["currencies_symbol"])&&isset(MicroFileDB::$configMicroFarma["currencies_symbol"][$correctCurr])?
                            MicroFileDB::$configMicroFarma["currencies_symbol"][$correctCurr] :$correctCurr;

        $rate = $correctCurr!="USD"?(!is_null(MicroPrice::$exchangeRates)&&MicroPrice::$exchangeRates["xchg_USD_".$correctCurr]?MicroPrice::$exchangeRates["xchg_USD_".$correctCurr]:0):1;
        return sprintf('%s %.2f',$symbolRate,$price*$rate);

    }
    function getTablePriceId($id){
            $price = 0;
            if(is_null(MicroPrice::$tablePrice)){
                MicroPrice::$tablePrice = array();
                if(file_exists($this->tableRootFPath)){
                    $price = unserialize(file_get_contents($this->tableRootFPath));
                    if($price&&is_array($price))
                        MicroPrice::$tablePrice = $price;
                }
            }
        return isset(MicroPrice::$tablePrice[$id])&&isset(MicroPrice::$tablePrice[$id]["price"])?MicroPrice::$tablePrice[$id]["price"]:$price;
    }
    function calculateSaving(&$data,$currency){
            $maxPerpil =  $data["gratestPP"];
                unset($data["gratestPP"]);
            foreach($data as $id=> &$idData){
                $saving = $idData["qty"]*$maxPerpil -  $idData["price"];
                $idData["saving"] = $saving > 0.01 ? $saving:0.00;
                $idData["format_saving"] = $this->recalcPrice( $idData["saving"] ,$currency);
            }
    }
}