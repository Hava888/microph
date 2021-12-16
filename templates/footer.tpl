<div class="site footer-content clearfix">
	<div class="pagewidth">
		<?if(!empty($footerMenu)):?>
			 <div class="col-lg col-lg-4 footer-info">
				 <h2 class="heading-footer js-classSwitch" data-dbx-name="next"></h2>
				 <div class="js-dropped">
					 <div class="categories-xs accordeon">
						 <div class="accordeon-item opened">
							 <div class="accordeon-text heading-footer"><?=$translation["FOOTER_INFORMATION"]?></div>
							 <?=$footerMenu?>
						 </div>
					 </div>
				 </div>
			 </div>
		<?endif?>
		<div class="col-lg col-lg-4 footer-contacts">
			<h2 class="heading-footer js-classSwitch" data-dbx-name="next"><?=$translation["CONTACT_MENUITEM"]?></h2>
			<div class="js-dropped">
				<ul class="list-contacts no-list">
					<li>
						<span class="icon icon-flag-us">
							<img src="<?=$ImgUrl?>/free.png" alt=""><span><?=$translation["FREE"]?></span>
						</span>
					</li>
					<li>
						<span class="icon icon-flag-us"><img src="<?=$ImgUrl?>/us.png" alt=""></span>
					</li>
					<li>
						<span class="icon icon-flag-uk"><img src="<?=$ImgUrl?>/uk.png" alt=""></span>
					</li>
					<li>
						<span class="">
							<img src="<?=$ImgUrl?>/bit.png" alt="">
							<img src="<?=$ImgUrl?>/cert.png" alt="">
						</span>
					</li>
				</ul>

			</div>
		</div>
		<div class="col-lg col-lg-4 support-container">
			<a href="javascript:micro.openSupportWindow();"><div class="support clearfix">
					<div class="support-img">
						<img class="support-lg" src="<?=$ImgUrl?>/support.png" alt="">
						<img class="support-xs" src="<?=$ImgUrl?>/support-img-xs.png" alt="">
					</div>
					<div class="support-text">
						<?=$translation["LEFTMENU_SUPPORT"]?>
						<div class="status">chat now</div>
					</div>


				</div></a>
			<div class="certificate">
				<img src="<?=$ImgUrl?>/mc.png" alt="">
				<img src="<?=$ImgUrl?>/geo.png" alt="">
			</div>
		</div>
    </div>
</div>