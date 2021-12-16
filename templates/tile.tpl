<div class="prod-wrap">
    <div class="inside">
        <div class="prod-head row">
            <div class="pic col-xs-3">
                <a rel="nofollow" href="<?=$data["buy_url"]?>">
                    <img class="" src="<?=$data['srcImg']?>" alt="Viagra" width="80" height="65">
                </a>
            </div>
        
            <div class="name-wrapper col-xs-9">
                <h3>
                    <a href="<?=$data["buy_url"]?>"><?=$data["title"]?></a>
                </h3>
            </div>
        </div>
        <div class="descr-row">
            <div class="desc1">
                <div>
                    <p>
                       <?=$data["descr"]?>
                    </p>
                </div>
            </div>
            <div class="prod-more">
                <a rel="nofollow" href="<?=$data["buy_url"]?>"><?=$data["MORE_INFO"]?></a>
            </div>
        </div>
        <div class="row controls-row">
            <div class="col-xs-7 price">
                <span class="prod-price"><?=$data["formatPrice"]?></span>
                <span class="prod-per"><?=$data["per_container"]?></span>
            </div>
            <div class="col-xs-5 add-inf">
                <a rel="nofollow" href="<?=$data["buy_url"]?>"><span><?=$data["buyTxt"]?></span></a>
            </div>
        </div>
    </div>
</div>
