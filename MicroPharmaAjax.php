<?php
/**
 * Created by PhpStorm.
 * User: sancho
 * Date: 01.06.19
 * Time: 12:36
 */
//ini_set("display_errors",0);
if(!session_id()) session_start();

include_once $_SERVER["DOCUMENT_ROOT"]."/wp-load.php";
include_once "MicroCart.php";
class MicroPharmaAjax extends MicroCart {
    private $inputQuery = array();
    private $availableAction = array("set","remove","upgrade","changeShipping");
    function __construct(){
        try{
            parent::__construct(array());
            $this->setupShortCode();
            $newCartHtml = $this->render("cart");
            $shortCartHtml = $this->render("short_cart");
            echo json_encode(array(
                                    "status"=>"success",
                                    "html"=>array(
                                                    "base_cart"=>$newCartHtml,
                                                    "short_cart"=>$shortCartHtml,
                                                )
                                    )
                            );
        }catch (Exception $exp){
            exit(json_encode(array("status"=>"error","msg"=>"MICRO_AJAX_ERROR".$exp->getCode().":".$exp->getMessage())));
        }
    }
    function setupShortCode(){
            if(isset($_POST["action"])&&in_array($_POST["action"],$this->availableAction)){
                $this->shortCodeData["lang"] = isset($_POST["lang"])&&in_array($_POST["lang"],MicroFileDB::$configMicroFarma["available_lang"]) ? $_POST["lang"]:"en";
                $this->shortCodeData["curr"] = isset($_POST["currency"])&&in_array(strtoupper($_POST["currency"]),MicroFileDB::$configMicroFarma["available_currency"]) ? strtoupper($_POST["currency"]):"USD";
            }else{
                throw new Exception("incorrect action",1);
            }
    }
}
new MicroPharmaAjax();