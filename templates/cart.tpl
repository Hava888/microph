<div id="base_cart_wrap">

    <div class="loader ng-scope">
      <div class="lds-blocks" style="width:100%;height:100%">
        <div style="left:38px;top:38px;animation-delay:0s"></div>
        <div style="left:80px;top:38px;animation-delay:0.125s"></div>
        <div style="left:122px;top:38px;animation-delay:0.25s"></div>
        <div style="left:38px;top:80px;animation-delay:0.875s"></div>
        <div style="left:122px;top:80px;animation-delay:0.375s"></div>
        <div style="left:38px;top:122px;animation-delay:0.75s"></div>
        <div style="left:80px;top:122px;animation-delay:0.625s"></div>
        <div style="left:122px;top:122px;animation-delay:0.5s"></div>
      </div>
    </div>

    <div class="site clearfix">
        <h1 class="page-title"><?=$translation["CART_TITLE"]?></h1>
    </div>
    <section class="site-middle">
        <div class="site clearfix">
            <div class="tbl-cart scrollable">
                <?if(isset($data["product"])&&!empty($data["product"])):?>
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="product" colspan="2"><?=$translation["CART_PRODUCT"]?></th>
                            <!--th class="package"><?//=$translation["PRODUCT_PACKAGE"]?></th-->
                            <th class="qty"><?=$translation["CART_QTY"]?></th>
                            <th class="price"><?=$translation["PRODUCT_PRICE"]?></th>
                            <th class="subtotal"><?=$translation["CART_SUBTOTAL"]?></th>
                            <th class="remove"><?=$translation["CART_REMOVE"]?></th>
                        </tr>
                        </thead>
                            <?foreach($data["product"] as $oneProdData):?>
                                <tbody>
                                    <tr>
                                        <td class="product">
                                            <form method="post">
                                                <input type="hidden" name="id" value="<?=$oneProdData["id"]?>">
                                                <input type="hidden" name="upgradeData" value="<?=$oneProdData["id"]?>:<?=$oneProdData["closestId"]?>"/>
                                                <input type="hidden" name="action" value="">
                                                <input type="hidden" name="lang" value="<?=$lang?>">
                                                <input type="hidden" name="currency" value="<?=$curr?>">
                                                <input type="hidden" name="value" value="">
                                            </form>
                                            <div class="td-inner">
                                                <img class="" src="<?=$oneProdData["img_src"]?>" alt="<?=$oneProdData["name"]?>">
                                                
                                            </div>
                                        </td>
                                        <td class="package">
                                            <div class="td-inner"><span class="title">
                                                    <?=$oneProdData["title"]?> <span class="visible-mob"><?=$oneProdData["package"]?></span>
                                                </span> <?=$oneProdData["package"]?> </div>
                                        <?if(isset($oneProdData["closest_package"])&&!empty($oneProdData["closest_package"])):?>
                                                <form method="post">
                                                    <input type="hidden" name="id" value="<?=$oneProdData["id"]?>">
                                                    <input type="hidden" name="upgradeData" value="<?=$oneProdData["id"]?>:<?=$oneProdData["closestId"]?>"/>
                                                    <input type="hidden" name="action" value="">
                                                    <input type="hidden" name="lang" value="<?=$lang?>">
                                                    <input type="hidden" name="currency" value="<?=$curr?>">
                                                </form>
                                                <div>
                                                    <div>

                                                        <a href="#" class="icon icon-upgrade upgrHref"><?printf($translation["PACKAGE_UPGRADE"],$oneProdData["closest_package"],$oneProdData["closest_saving"])?></a>
                                                    </div>
                                                </div>
                                            <?endif?>
                                        </td>
                                        <td class="qty">
                                            <div class="td-inner">
                                                <div class="counter">
                                                    <button class="decrement">
                                                        <i class="rhicon rhi-chevron-down"></i>
                                                    </button>
                                                    <input class="cnt" type="text" value="<?=$oneProdData["items"]?>"  max="99" min="1">
                                                    <button class="increment">
                                                        <i class="rhicon rhi-chevron-up"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="price">
                                            <div class="td-inner">
                                                <?=$oneProdData["format_price"]?>
                                            </div>
                                        </td>
                                        <td class="subtotal">
                                            <div class="td-inner">
                                                <?=$oneProdData["format_subtotal_price"]?>
                                            </div>
                                        </td>
                                        <td class="delete">
                                            <div class="td-inner">
                                                <a class="icon icon-remove removeHref">
                                                    <i class="rhicon rhi-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                </tbody>
                            <?endforeach?>
                            <?if(isset($data["bonus"])&&!empty($data["bonus"])):?>
                            <tbody>
                                <tr class="row-bonus">
                                    <td class="product">
                                        <div class="td-inner">
                                            <img src="<?=$data["bonus"]["img_src"]?>" alt="bonus">
                                            

                                            
                                        
                                        </div>
                                    </td>
                                    <td class="package">
                                    	<div class="td-inner">
                                    	<span class="title">
											<?=$data["bonus"]["title"]?>
											<span class="visible-mob"><?=$data["bonus"]["package"]?></span>
										</span>
                                        <?=$data["bonus"]["package"]?>
                                            <span class="dosage">
                                            	<span><i class="icon icon-bonus"></i>bonus</span>
                                            </span>
                                        </div>

                                    </td>
                                    <td class="qty">
                                        <div class="td-inner">
                                            1</div>
                                    </td>
                                    <td class="price">
                                        <div class="td-inner"><?=$translation["FREE"]?></div>
                                    </td>
                                    <td class="subtotal">
                                        <div class="td-inner"><?=$translation["FREE"]?></div>
                                    </td>
                                    <td class="delete">
                                        <div class="td-inner"></div>
                                    </td>
                                </tr>
                            </tbody>
                        <?endif?>
                    </table>


                    <table class="tbl-method">
                    <?if(isset($data["shipping"])&&!empty($data["shipping"])):?>
                        <thead>
                            <tr>
                                <th class="package" colspan="6">
                                    <span class="title"><?=$translation["SHIPPING_METHOD"]?></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?foreach($data["shipping"] as $idShipping=>$dataShip):?>
                                <tr>
                                    <td colspan="5">
                                        <form method="post" name="shippingForm">
                                            <input type="hidden" name="action" value="changeShipping">
                                            <input type="hidden" name="lang" value="<?=$lang?>">
                                            <input type="hidden" name="currency" value="<?=$curr?>">
                                            <div class="td-inner">
                                                <div class="form-item">
                                                    <div class="custom-radio">
                                                        <input name="shipping_method_id" type="radio" <?=$dataShip["active"]=="active"?'checked="checked"':''?> value="<?=$idShipping?>" id="ship-met-<?=$idShipping?>">
                                                        <label for="ship-met-<?=$idShipping?>"><span class="radio-custom"></span><?=$dataShip["title"]?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="td-inner"><?=$dataShip["price"]?></div>
                                    </td>
                                </tr>
                            <?endforeach?>
                        </tbody>
                    <?endif?>
                        <tfoot>
                        <tr>
                            <td colspan="6">
                                <form name="checkout" method="post" action="<?=$checkoutData["url"]?>" target="_blank">
                                    <input type="hidden" name="data" value="<?=$checkoutData["data"]?>">
                                </form>
                                <div class="wrap-total">
                                    <div class="block-total">
                                        <?=$translation["CART_TOTAL"]?>:<span><?=$total?></span>
                                    </div>
                                    <div class="btn-block">
                                        <div class="btn-group">
                                            <button type="submit" class="btn btn-checkout" onclick="document.checkout.submit();return false">
                                                <span class="icon icon-checkout"><?=$translation["CART_CHECKOUT_BTN"]?></span></button>
                                        </div>
                                        <div class="sertified">
                                            <span>Our Billing is certified by:</span>
                                            <div class="sertified-group">
                                                <a href=""><img src="<?=$ImgUrl?>/sertified1.png" alt=""></a>
                                                <a href=""><img src="<?=$ImgUrl?>/sertified2.png" alt=""></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                <?else:?>
                <div class="site clearfix">
                    <div class="tbl-cart scrollable empty">
                        Your cart is empty
                    </div>
                </div>
                <?endif?>
            </div>
        </div>
    </section>
</div>