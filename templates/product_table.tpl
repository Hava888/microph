<?if(!empty($selectorData)&&is_array($selectorData)):?>
    <div class="dosage-control">
        <select id="select-dosage">
        <?foreach($selectorData as $formatDos => $userDos):?>
            <option class="pills-<?=$formatDos?>" value="dosage_<?=$formatDos?>"><?=$userDos?></option>
        <?endforeach?>
        </select>
    </div>
<?endif?>
<div class="tbl-product js-tblProduct">
<?$count=0;?>
    <?foreach($data as $key=>$oneDoseIng):?>
    <?$count++;?>
        <table class="dosage_<?=$key?><?=($count == '1') ? ' active' : ''?>">
            <thead>
            <tr>

                <th class="package"><?=$translation["package"]?></th>
                <th class="price"><?=$translation["price"]?></th>
                <th class="perpill"><?=$translation["per_pill"]?></th>
                <th class="saving"><?=$translation["saving"]?></th>
                <th class="buy"><?=$translation["PRODUCT_BUY"]?></th>
            </tr>
            </thead>
            <!--tbody class="bonus"-->
            <?foreach($oneDoseIng as $idPRod=>$dataProd):?>
                <tbody>
                    <form action="<?=$cartUrl?>" method="post">
                        <tr>
                            <td class="package">
                                <span class="hide-mob"><?=$dataProd["pack_part1"]?></span>
                                <?=$dataProd["pack_part2"]?>

                                <?if(!empty($dataProd["ship_bonus"])||!empty($dataProd["prod_bonus"])):?>
                                    <ul>
                                        <?if(!empty($dataProd["prod_bonus"])):?>
                                            <li><span><?=$dataProd["prod_bonus"]?><?=$translation["product_bonus_tablets"]?></span></li>
                                        <?endif?>
                                        <?if(!empty($dataProd["ship_bonus"])):?>
                                            <li><span class="shipping"><?=$translation["product_bonus_shipping"]?></span></li>
                                        <?endif?>
                                    </ul>
                                <?endif?>
                            </td>
                            <td class="price">
                                <?=$dataProd["price_format"]?>
                                <span class="visible-mob"><?=$dataProd["per_pill_format"]?>
                                    <span><?=$translation["per_pill"]?></span>
                                </span>
                            </td>
                            <td class="per_pill">
                                <?=$dataProd["per_pill_format"]?>
                            </td>
                            <td class="saving"><?=$dataProd["saving"]>0?$dataProd["format_saving"]:''?></td>
                            <td class="order">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id" value="<?=$idPRod?>">
                                <button class="btn btn-rdd btn-inverse">
                                    <span class="icon icon-cart-plus-red"></span>
                                    <span class="hide-mob"><?=$translation["add_to_cart"]?></span>
                                </button>
                            </td>
                            
                        </tr>
                        </form>
                </tbody>
            <?endforeach?>
            <!-- tbl_product_row END -->
        </table>
    <?endforeach?>
</div>