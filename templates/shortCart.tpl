<div class="smart-cart-box" id="shortCartWrap">
    <div class="smart-cart js-classSwitch" data-dbx-name=".smart-cart-ordered">
        <i class="smart-cart-icon icon icon-cart"></i>
        <div class="smart-cart-total">
            <div class="cart-text"><?=$data["translation"]["YOUR_CART"]?><span class="count"><?=$data["total_items"]?></span></div>
            <div class="amount"><?=$data["total"]?></div>
        </div>
    </div>
    <!--div class="btn-container">
        <a href="<?=$data["cart_url"]?>"><div class="btn-checkout"><?=$data["translation"]["CART"]?></div></a>
    </div-->
    <div class="smart-cart-ordered">
        <?if(isset($data["product"])&&!empty($data["product"])):?>
            <ul class="list-menu no-list">
                <?foreach($data["product"] as $oneProdData):?>
                    <li class="list-menu-item">
                        <div class="view">
                            <img class="" src="<?=$oneProdData["img_src"]?>" alt="<?=$oneProdData["name"]?>"></div>
                        <div class="body">
                            <div class="name"><?=($oneProdData["items"] > 1 ? $oneProdData["items"]." x ":"")?><?=$oneProdData["title"]?></div>
                            <div class="desc"><?=$oneProdData["package"]?></div>
                            <div class="price"><?=$oneProdData["format_price"]?></div>
                        </div>
                       <a class="icon icon-remove removeHref"><i class="fas fa-trash-alt"></i></a>
                       <form method="post">
							<input type="hidden" name="id" value="<?=$oneProdData["id"]?>">
							<input type="hidden" name="action" value="">
							<input type="hidden" name="lang" value="<?=$lang?>">
							<input type="hidden" name="currency" value="<?=$curr?>">
							<input type="hidden" name="value" value="">
						</form>
                    </li>
                <?endforeach?>
            </ul>
        <?endif?>
        <div class="smart-cart-total">
            <div class="amount-data">
                <div class="total-amount">
            <span class="amount-count">
                <?=$data["total_items"]?></span>
                    <span class="amount-text"><?=$data["translation"]["ITEMS"]?></span>
                </div>
                <div class="total-price"><?=$data["total"]?></div>
            </div>
            <div class="btn-checkout-container">
                <a href="<?=$data["cart_url"]?>" class="btn btn-acc btn-checkout"><?=$data["translation"]["CART"]?></a>
            </div>
        </div>
    </div>
</div>