<?php
/**
 * Created by PhpStorm.
 * User: sancho
 * Date: 05.06.19
 * Time: 12:25
 */
include_once "MicroFileDB.php";
class MicroTemplate extends  MicroFileDB{
    function __construct(){
        parent::__construct(array());

    }
    function getFooter(){
        $this->detectPageLangCurr();
        $footerTmplFile = MICRO_PHARMA_TMP_DIR."footer.tpl";
        if(file_exists($footerTmplFile)){
	        $ImgUrl = plugins_url("templates/img", __FILE__ );
            $translation = array(
                            "FOOTER_INFORMATION" => $this->getTranslate("FOOTER_INFORMATION",$this->shortCodeData["lang"]),
                            "CONTACT_MENUITEM" => $this->getTranslate("CONTACT_MENUITEM",$this->shortCodeData["lang"]),
                            "LEFTMENU_SUPPORT" => $this->getTranslate("LEFTMENU_SUPPORT",$this->shortCodeData["lang"]),
                            "FREE"=>$this->getTranslate("FREE",$this->shortCodeData["lang"]),
            );
            $footerMenu = '';
            if(has_nav_menu("micropharma-menu"))
                $footerMenu = wp_nav_menu( array( 'theme_location' => 'micropharma-menu',"menu_class"=>"accordeon-nested no-list","echo"=>false ));
            ob_start();
            include $footerTmplFile;
            $HtmlData = ob_get_contents();
            ob_end_clean();
            return $HtmlData;
        }else{
            throw new Exception("cannot show footer. Template file not exist",30);
        }
    }
}