<?php
/*
Plugin Name: MicroPharma
Version: 1.01
Author: Sancho

available shortcode
1) [micropharm type="table|tile|cart|shor_cart" name="product" lang = "en" url="",dosage=""]
*/

//exit("asdasd");

//register_deactivation_hook(__FILE__,array("Install","unistall"));
if(!session_id()) session_start();
class MicroPharma{

    private $logFnam = "cache/MicroPharmaError.log";
    private $showLog = true;
    function __construct(){
        wp_enqueue_style( 'microcsss',  content_url("plugins/MicroPharma/templates/custom.css"),array(),'4.8.10');
        wp_enqueue_script('newscript', content_url("plugins/MicroPharma/templates/micro.js"),array(),'23');
        $this->SetReferrer();
        register_activation_hook(__FILE__,array($this,"install"));
        add_shortcode("micropharm",array($this,"shortCodeMap"));
        add_action("themify_header_end",array($this,"show_short_basket"));
        add_action("themify_footer_before",array($this,"show_footer"));
        register_nav_menus( array(
            'micropharma-menu' =>"MicroPharma Bottom Menu",
            )
        );
    }

    function install(){
        include __DIR__ . '/install/Install.php';
        new Install();
    }
    function shortCodeMap($atts){
        try{
            if(isset($atts["type"])){
                if($atts["type"] == "tile" || $atts["type"] == "table"){
                    unset($_SESSION["MICRO_PHARMA"]["last_add"]);
                    if(!class_exists("MicroProduct"))
                        include dirname(__FILE__).DIRECTORY_SEPARATOR."MicroProduct.php";
                    $MicroObj = new MicroProduct($atts);
                    return $MicroObj->render($atts["type"]);
                }elseif ($atts["type"] == "cart" || $atts["type"] == "short_cart" ){
                    if(!class_exists("MicroCart"))
                        include dirname(__FILE__).DIRECTORY_SEPARATOR."MicroCart.php";
                        $MicroObj = new MicroCart($atts);
                        return $MicroObj->render($atts["type"]);
                }else if( $atts["type"] == "footer"){
                    include dirname(__FILE__).DIRECTORY_SEPARATOR."MicroTemplate.php";
                    $MicroObj = new MicroTemplate();
                    return $MicroObj->getFooter();

                }else{
                    return "incorrect shortcode type";
                }
            }
        }catch (Exception $exp){
            return "micropharma_error: ".$exp->getCode();
        }
    }
    function MicroPharmaLog($msg,$code){
        if($this->showLog){
           if(!file_put_contents("",date("d/m/Y H:i")."| MicroPharmaError ".$code.": ".$msg."\n",FILE_APPEND)){
                exit("canot save log. Change permission to the file [".$this->logFnam."]");
           }
        }
    }
    function SetReferrer(){
        if(empty($_SESSION['MICRO_PHARMA']['referrer'])){
            $defaulReferre='NO_REFERRER[ENTRY='.getenv('HTTP_HOST').getenv('REQUEST_URI').']';
            $_SESSION['MICRO_PHARMA']['referrer']=!empty($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:$defaulReferre;

        }

    }
    function show_short_basket(){
        echo $this->shortCodeMap(array("type"=>"short_cart"));
    }
      function show_footer(){
        echo $this->shortCodeMap(array("type"=>"footer"));
    }
}
new MicroPharma();