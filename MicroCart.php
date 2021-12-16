<?php
/**
 * Created by PhpStorm.
 * User: sancho
 * Date: 31.05.19
 * Time: 10:52
 */

include_once dirname(__FILE__).DIRECTORY_SEPARATOR."MicroProduct.php";
class MicroCart extends MicroProduct {
    private $totalWoShipping = 0;
    private $total = 0;
    private $totalItems = 0;
    function __construct($data){
        parent::__construct($data);
    }
    function render($type){
        switch($type){
            case "cart":
                return $this->cart();
            case"short_cart":
                return $this->short_cart();
                break;
        }

    }
    function cart(){
        $this->doCartAction();
        unset($_POST);
        $this->calCulateTotal();
        $dataCart = $this->getCartData();
        $checkData = $this->getCheckoutData();
        $translCart =$this->getCartTranslation();
        if(empty($dataCart))unset($_SESSION["MICRO_PHARMA"]["active_shipping_id"]);
        return $this->getHtmlCart($dataCart,$checkData,$translCart);
    }
    function short_cart(){
        $this->doCartAction();
        unset($_POST);
        $this->detectPageLangCurr();
        $this->calCulateTotal();
        $shortCartData = $this->getCartData(true);
        $shortCartData["total"] = $this->recalcPrice($this->total, $this->shortCodeData["curr"]);
        $shortCartData["total_items"] = $this->totalItems;
        $shortCartData["cart_url"]=$this->getShortCartUrl();
        $shortCartData["translation"]= array(
                                                'YOUR_CART' => $this->getTranslate("YOUR_CART",$this->shortCodeData["lang"]),
                                                'ITEMS' => $this->getTranslate("ITEMS",$this->shortCodeData["lang"]),
                                                'CART' => $this->getTranslate("CART",$this->shortCodeData["lang"]),
                                            );
        return $this->getHtmlShortCart($shortCartData);
    }

    function getCartData($shortCart=false){
        $cartData = array();
        if(isset($_SESSION["MICRO_PHARMA"]["cart"])&&!empty($_SESSION["MICRO_PHARMA"]["cart"])){
            foreach($_SESSION["MICRO_PHARMA"]["cart"] as $idProd=>$DataProd){
                $OneProdData = array();
                $OneProdData["name"] = $DataProd["name"];
                $OneProdData["id"] = $idProd;
                $OneProdData["img_src"] = $this->getImg(str_replace(" ","-",$DataProd["name"]));
                $OneProdData["title"] = $this->getTitle($DataProd["name"],$this->shortCodeData["lang"]);
                $OneProdData["package"] = $this->getProdPackage($DataProd["name"],$idProd);
                $OneProdData["items"] = $DataProd["items"];
                $price = $this->getTablePriceId($idProd);
                $OneProdData["price"] = $price;
                if(isset($DataProd["closestId"])&&!empty($DataProd["closestId"])&&!empty($DataProd["closestQty"])&&!$shortCart){
                    $savingPrice =  $this->getTablePriceId($DataProd["closestId"]);
                    $OneProdData["closest_package"] = $this->getProdPackage($DataProd["name"],$DataProd["closestId"]);
                    $OneProdData["closest_saving"] =  $this->recalcPrice($price/$DataProd["qty"]*$DataProd["closestQty"]-$savingPrice,$this->shortCodeData["curr"]);
                    $OneProdData["closestId"] = $DataProd["closestId"];
                }

                $OneProdData["format_price"] = $this->recalcPrice($price, $this->shortCodeData["curr"]);
                $OneProdData["format_subtotal_price"] = $this->recalcPrice($price*$DataProd["items"], $this->shortCodeData["curr"]);
                $cartData["product"][] =$OneProdData;
            }

            if(!$shortCart){
                $bonus = $this->calculateCartBunus();
                if(empty($bonus)){
                    unset($_SESSION["MICRO_PHARMA"]["bonus_id"]);
                }else{
                    $cartData["bonus"] = $bonus;
                }
                $cartData["shipping"] = $this->getShippingData();
            }
        }
        return $cartData;
    }
    function doCartAction(){
        if(isset($_POST)&&!empty($_POST)){
            if(isset($_POST["action"])&&!empty($_POST["action"])){
                switch ($_POST["action"]){
                    case "add":
                        $this->addProduct();
                        break;
                    case "set":
                        $this->setProduct();
                        break;
                    case "remove":
                        $this->removeProduct();
                        break;
                    case "upgrade":
                        $this->upgradeProduct();
                        break;
                    case "changeShipping":
                        $this->changeShippinMethod();
                        break;

                }
            }
        }
    }
    function addProduct($Count = false){
        if(isset($_POST["id"])&&!empty($_POST["id"])&&is_numeric($_POST["id"])&&$_POST["id"] != $_SESSION["MICRO_PHARMA"]["last_add"]||$Count){
            $AddProdData = $this->DataAddProdById($_POST["id"]);
            if(!empty($AddProdData)){
                $oldQty = 0;
                if(isset($_SESSION["MICRO_PHARMA"]["cart"][$_POST["id"]]["items"]))
                    $oldQty = $_SESSION["MICRO_PHARMA"]["cart"][$_POST["id"]]["items"];
                $AddProdData["items"] = $Count ? $Count :$oldQty+1;
                $_SESSION["MICRO_PHARMA"]["cart"][$_POST["id"]] = $AddProdData;
                $_SESSION["MICRO_PHARMA"]["last_add"] = $_POST["id"];
            }
        }
    }
    function setProduct(){
        if(isset($_POST["id"])&&!empty($_POST["id"])&&is_numeric($_POST["id"])&&$_POST["id"]>=0){
            if(isset($_POST["value"])&&is_numeric($_POST["value"])){
                $newItmes = intval($_POST["value"]);
                if($newItmes > 0){
                    if(isset($_SESSION["MICRO_PHARMA"]["cart"][$_POST["id"]])){
                        $_SESSION["MICRO_PHARMA"]["cart"][$_POST["id"]]["items"] = $newItmes;
                    }else{
                        $this->addProduct($newItmes);
                    }
                }else{
                    if(isset($_SESSION["MICRO_PHARMA"]["cart"][$_POST["id"]]))
                        unset($_SESSION["MICRO_PHARMA"]["cart"][$_POST["id"]]);
                }
            }

        }

    }
    function calCulateTotal(){
        $this->totalWoShipping = 0;
        $this->total = 0;
        $this->totalItems = 0;
        if(isset($_SESSION["MICRO_PHARMA"]["cart"])&&!empty($_SESSION["MICRO_PHARMA"]["cart"])){
            foreach($_SESSION["MICRO_PHARMA"]["cart"] as $idInCart=>$dataProd){
                $price = $this->getTablePriceId($idInCart);
                $this->totalItems+=$dataProd["items"];
                $this->totalWoShipping +=$price*$dataProd["items"];
            }
			$this->total = $this->totalWoShipping;
			$activeShipId = $_SESSION["MICRO_PHARMA"]["active_shipping_id"];
			if(empty($activeShipId)||!isset(MicroFileDB::$configMicroFarma["ship_method"][$activeShipId])){
				$activeShipId = MicroFileDB::$configMicroFarma["default_shipping_method_id"];
				$_SESSION["MICRO_PHARMA"]["active_shipping_id"] = $activeShipId;
			}

			if(!isset(MicroFileDB::$configMicroFarma["ship_method"][$activeShipId]["free"])||
				MicroFileDB::$configMicroFarma["ship_method"][$activeShipId]["free"]>$this->totalWoShipping
			)
			$this->total += MicroFileDB::$configMicroFarma["ship_method"][$activeShipId]["price"];
        }
  

    }
    function calculateCartBunus(){
        $qtyEdpills = 0;
        if(isset($_SESSION["MICRO_PHARMA"]["cart"])&&!empty($_SESSION["MICRO_PHARMA"]["cart"])){
            foreach($_SESSION["MICRO_PHARMA"]["cart"] as $idProd=>$DataProd){
                if(in_array($idProd,MicroFileDB::$configMicroFarma["ed_items_id"])){
                    $qtyEdpills += $DataProd["qty"]*$DataProd["items"];
                }
            }
        }
        return $this->productBonus($qtyEdpills,false,true);
    }
    function getShippingData(){
        $shipsData = array();
        if(isset(MicroFileDB::$configMicroFarma["ship_method"])&&!empty(MicroFileDB::$configMicroFarma["ship_method"])){
             foreach (MicroFileDB::$configMicroFarma["ship_method"] as $idShip=>$DataShip){
                 $shipTransl = $this->getTranslate("SHIPMETHOD_${idShip}_TITLE",$this->shortCodeData["lang"]);
                 if($shipTransl == "SHIPMETHOD_{$idShip}_TITLE") $shipTransl = $DataShip["title"];

                 $shipPrice =  isset(MicroFileDB::$configMicroFarma["ship_method"][$idShip]["free"])&&
                               MicroFileDB::$configMicroFarma["ship_method"][$idShip]["free"]<$this->totalWoShipping?
                                                    $this->getTranslate("FREE",$this->shortCodeData["lang"]):
                                                    $this->recalcPrice($DataShip["price"], $this->shortCodeData["curr"]);

                 $oneShipData = array(
                                        "title" => $shipTransl,
                                        "price" => $shipPrice,
                                        "active" => $_SESSION["MICRO_PHARMA"]["active_shipping_id"] == $idShip? "active":"not_active",

                 );

                 $shipsData[$idShip] = $oneShipData;
             }
        }
        return $shipsData;
    }
    function getHtmlCart($data,$checkoutData,$translation){
        $cartTmplRootPath = MICRO_PHARMA_TMP_DIR."cart.tpl";
        $total = $this->recalcPrice($this->total,$this->shortCodeData["curr"]);
        $lang = $this->shortCodeData["lang"];
        $curr = $this->shortCodeData["curr"];
        $ImgUrl = plugins_url("templates/img", __FILE__ );
        if(file_exists($cartTmplRootPath)){
            ob_start();
            include $cartTmplRootPath;
            $htmlData = ob_get_contents();
            ob_end_clean();
            return $htmlData;
        }else{
            throw new Exception("cart template file not exist",27);
        }
    }
    function removeProduct(){
        if(isset($_POST["id"])&&isset($_SESSION["MICRO_PHARMA"]["cart"][$_POST["id"]])){
            unset($_SESSION["MICRO_PHARMA"]["cart"][$_POST["id"]]);
        }
    }
    function upgradeProduct(){
        if(isset($_POST["upgradeData"])){
           $UpgradeIdArr  =  explode(":",$_POST["upgradeData"]);
           if(count($UpgradeIdArr)==2){
               $_POST["id"] = $UpgradeIdArr[0];
               $this->removeProduct();
               $_POST["id"] = $UpgradeIdArr[1];
               $this->addProduct();
           }
        }
    }
    function changeShippinMethod(){
        if(isset($_POST["shipping_method_id"])&&!empty($_POST["shipping_method_id"]))
            $_SESSION["MICRO_PHARMA"]["active_shipping_id"] = $_POST["shipping_method_id"];
    }
    function getCheckoutData(){
        $tempData = array(
                                "referrer"=>$_SESSION["MICRO_PHARMA"]["referrer"],
                                "ip"=>$_SERVER["REMOTE_ADDR"],
                                "lang"=> in_array($this->shortCodeData["lang"],MicroFileDB::$configMicroFarma["available_lang"])?$this->shortCodeData["lang"]:"en",
                                "version"=>"3.16073",
                                "host"=>$_SERVER['HTTP_HOST'],
                                "wm" => MICRO_PHARMA_WM,
                                "tr"=>MICRO_PHARMA_TR,
                                "pr"=>MICRO_PHARMA_PR,
                                "site_id"=>MICRO_PHARMA_SITE_ID,
                                "free_ship_method_id"=>0,
                                "sid"=>session_id(),
                                "currency"=>in_array(strtoupper($this->shortCodeData["curr"]),MicroFileDB::$configMicroFarma["available_currency"])?$this->shortCodeData["curr"]:"USD",
                                "shipping_method_id"=>$_SESSION["MICRO_PHARMA"]["active_shipping_id"],
                                "bonus_id"=>$_SESSION["MICRO_PHARMA"]["bonus_id"],

        );
        if(isset($_SESSION["MICRO_PHARMA"]["cart"])&&!empty($_SESSION["MICRO_PHARMA"]["cart"]))
            foreach($_SESSION["MICRO_PHARMA"]["cart"] as $idProd=>$DataProd){
                $tempData[$idProd]= $DataProd["items"];
            }
        $checkData = array(
                            "data"=>urlencode(serialize($tempData)),
                            "url"=>MICRO_PHARMA_CHECKOUT_SCRIPT,
                    );

        return $checkData;
    }
    function getCartTranslation(){
        return $translData = array(
                                    "CART_PRODUCT"=>$this->getTranslate("CART_PRODUCT",$this->shortCodeData["lang"]),
                                    "PRODUCT_PACKAGE"=>$this->getTranslate("PRODUCT_PACKAGE",$this->shortCodeData["lang"]),
                                    "CART_QTY"=>$this->getTranslate("CART_QTY",$this->shortCodeData["lang"]),
                                    "PRODUCT_PRICE"=>$this->getTranslate("PRODUCT_PRICE",$this->shortCodeData["lang"]),
                                    "CART_SUBTOTAL"=>$this->getTranslate("CART_SUBTOTAL",$this->shortCodeData["lang"]),
                                    "CART_REMOVE"=>$this->getTranslate("CART_REMOVE",$this->shortCodeData["lang"]),
                                    "FREE"=>$this->getTranslate("FREE",$this->shortCodeData["lang"]),
                                    "SHIPPING_METHOD"=>$this->getTranslate("SHIPPING_METHOD",$this->shortCodeData["lang"]),
                                    "CART_TOTAL"=>$this->getTranslate("CART_TOTAL",$this->shortCodeData["lang"]),
                                    "CART_CHECKOUT_BTN"=>$this->getTranslate("CART_CHECKOUT_BTN",$this->shortCodeData["lang"]),
                                    "CART_TITLE"=>$this->getTranslate("CART_TITLE",$this->shortCodeData["lang"]),
                                    "PACKAGE_UPGRADE" =>$this->getTranslate("PACKAGE_UPGRADE",$this->shortCodeData["lang"]),

        );
    }
    function getHtmlShortCart($data){
        $shortCartTmplRootPath = MICRO_PHARMA_TMP_DIR."shortCart.tpl";
        $lang = $this->shortCodeData["lang"];
        $curr = $this->shortCodeData["curr"];
        if(file_exists($shortCartTmplRootPath)){
            ob_start();
            include $shortCartTmplRootPath;
            $htmlData = ob_get_contents();
            ob_end_clean();
            return $htmlData;
        }else{
            throw new Exception("short cart template file not exist",28);
        }
    }
}