var micro = {
    incrementBtnIdent:"increment",
    decrementBtnIdent:"decrement",
    actionInputIdent:"[name=action]",
    shippingRBtnIdent:"shipping_method_id",
    removeBtnIdent:"removeHref",
    upgrBtnIdent:"upgrHref",
    AjaxUrl:"/wp-content/plugins/MicroPharma/MicroPharmaAjax.php",
    SupportUrl:"/wp-content/plugins/MicroPharma/out/?id=live_support_url",
    baseCartWrapIdent:"#base_cart_wrap",
    baseShortCartWrapIdent:"#shortCartWrap",
    smartCart:"#shortCartWrap .js-classSwitch",
    selectDosage:"select-dosage",
    windowWidth:500,
    windowHeight:560,
    openWindow:null,
    initCartScript:function(){
        var incrBtnList = document.getElementsByClassName(micro.incrementBtnIdent),
            decBtnList = document.getElementsByClassName(micro.decrementBtnIdent),
            removeHref = document.getElementsByClassName(micro.removeBtnIdent),
            upgrHref = document.getElementsByClassName(micro.upgrBtnIdent),
            shippingRbtnElem = document.getElementsByName(micro.shippingRBtnIdent),
            smartCart = document.querySelector(micro.smartCart),
            selectDosage = document.getElementById(micro.selectDosage);


        if(incrBtnList.length){
            for(var i=0; i<incrBtnList.length;i++){
                incrBtnList[i].addEventListener("click",function(event){
                    event.preventDefault();
                    micro.incDecrFuntion("inc",this);
                });
            }
        }
        if(decBtnList.length){
            for(var i=0; i<decBtnList.length;i++){
                decBtnList[i].addEventListener("click",function(event){
                    event.preventDefault();
                    micro.incDecrFuntion("dec",this);
                });
            }
        }
        if(removeHref.length){
            for(var i=0; i<removeHref.length;i++){
                removeHref[i].addEventListener("click",function(event){
                    event.preventDefault();
                    micro.changeCartEventProd(this,"rem");
                });
            }
        }
        if(upgrHref.length){
            for(var i=0; i<upgrHref.length;i++){
                upgrHref[i].addEventListener("click",function(event){
                    event.preventDefault();
                    micro.changeCartEventProd(this,"upg");
                });
            }
        }
        if(shippingRbtnElem.length){
            for(var i=0; i<shippingRbtnElem.length;i++){
                shippingRbtnElem[i].addEventListener("click",function(event){
                    event.preventDefault();
                    micro.changeCartEventProd(this,"ship");
                });
            }
        }
        if (smartCart) {
            const blockContent = smartCart.parentNode.querySelector(".smart-cart-ordered");
            document.addEventListener('click', function(event) {
                if (smartCart.classList.contains('active') && !event.target.closest('#shortCartWrap')) {
                    smartCart.classList.remove('active');
                    blockContent.classList.remove('active');
                }
            });
            smartCart.addEventListener("click",function(){
                blockContent.classList.toggle('active');
                smartCart.classList.toggle('active');
            });
        }
        if (selectDosage) {
            selectDosage.addEventListener('change', function(event) {
                const currentValue = event.target.value;
                const blockTables = document.getElementsByClassName('js-tblProduct')[0];
                blockTables
                    .querySelector('table.active')
                    .classList
                    .remove('active');
                blockTables
                    .querySelector(`.${currentValue}`)
                    .classList
                    .add('active');
            });
        }
    },
	openSupportWindow:function(){
		if(!this.openWindow || this.openWindow.closed){
			var l=(screen.availWidth  - this.windowWidth) / 2, t=(screen.availHeight - this.windowHeight) / 2
			,op='toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,copyhistory=no,width='+this.windowWidth+',height='+this.windowHeight+',left='+l+',top='+t;
				window.open(this.SupportUrl,"support_window",op)
		}else{
			this.openWindow.focus();
		}
	},    
    incDecrFuntion:function(mod,element){
        var qtyInput,wrapIncDecrBlcok,oldQty,actionInput,form,BaseTr,hiddenVal,submitForm = false;
        BaseTr = element.closest("tr");
        if(BaseTr){
            form = BaseTr.getElementsByTagName("form");
            if(form.length){
                wrapIncDecrBlcok = element.closest(".td-inner");
                hiddenVal = form[0].querySelector("[name=value]");
                actionInput = form[0].querySelector("[name=action]");
                if(wrapIncDecrBlcok&&hiddenVal&&actionInput){
                    actionInput.value="set";
                    qtyInput = wrapIncDecrBlcok.getElementsByTagName("input");
                    if(qtyInput.length){
                        oldQty = isNaN(parseInt(qtyInput[0].value))?1:parseInt(qtyInput[0].value);
                        if(mod == "inc"){
                            hiddenVal.value = oldQty+1;
                            submitForm = true;
                        }else if(mod == "dec"&&oldQty>1){
                            submitForm = true;
                            hiddenVal.value  = oldQty-1;
                        }
                        if(submitForm){
                            var data = new FormData(form[0]);
                            this.sendRequest(data,this.updateCart);
                        }
                    }
                }
            }
        }
    },
    updateCart:function(data){
        var baseWrapcartElem = document.querySelector(micro.baseCartWrapIdent);
        var baseWrapShortCartElem = document.querySelector(micro.baseShortCartWrapIdent);
        if(data&&data.html){
			var updateEvent = false;
            if(data.html.base_cart&&baseWrapcartElem){
                var newCartBlocElem,helpDiv = document.createElement('div');
                helpDiv.innerHTML = data.html.base_cart;
                newCartBlocElem = helpDiv.querySelector(micro.baseCartWrapIdent)
                if(newCartBlocElem){
                    baseWrapcartElem.replaceWith(newCartBlocElem);
                    updateEvent = true;
                }
            }
            if(data.html.short_cart&&baseWrapShortCartElem){
                var newShortCartBlocElem,helpshortDiv = document.createElement('div');
                helpshortDiv.innerHTML = data.html.short_cart;
                newShortCartBlocElem = helpshortDiv.querySelector(micro.baseShortCartWrapIdent)
                if(newShortCartBlocElem){
	                shortBasketWrap = document.querySelector(micro.smartCart); 
                    baseWrapShortCartElem.replaceWith(newShortCartBlocElem);
                	if(shortBasketWrap&&shortBasketWrap.classList.contains("active")){
               			 smartCartWrapAfterModif = document.querySelector(micro.smartCart);
               			 if(smartCartWrapAfterModif){
	               			 smartCartWrapAfterModif.classList.toggle('active');
	               			 cartItemsElem = smartCartWrapAfterModif.parentNode.querySelector(".smart-cart-ordered");
	               			 if(cartItemsElem){
	               			 	cartItemsElem.classList.toggle('active');
	               			 }
               			 }
                	}
                    updateEvent = true;
                }
            }
            if(updateEvent){
	             micro.initCartScript();
			}
        }
    },
    changeCartEventProd:function(el,mod){
        var BaseTr,form,actionInput,submit = false;
		BaseTr = el.closest("tr")||el.closest("li");
        if(BaseTr){
            form = BaseTr.getElementsByTagName("form");
            if(form.length){
                actionInput = form[0].querySelector("[name=action]");
                if(actionInput){
                    if(mod == "rem"){
                        submit = true;
                        actionInput.value = "remove";
                    }else if(mod == "upg"){
                        submit = true;
                        actionInput.value = "upgrade";
                    }else if(mod == "ship"){
                        submit = true;
                        actionInput.value = "changeShipping";
                    }
                    if(submit){
                        var data = new FormData(form[0]);
                        this.sendRequest(data,this.updateCart);
                    }
                }
            }
        }
    },
    sendRequest:function(data,CBFunction){
        var xmlhttp = this.getXmlHttp();
        var loader = document.querySelector('.loader');
        xmlhttp.responseType = 'json';
        xmlhttp.open("POST", this.AjaxUrl, true);
        if(loader)loader.classList.add('loading');
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState != 4) return
            if (xmlhttp.status == 200) {
                if(xmlhttp.response&&xmlhttp.response.status&&xmlhttp.response.status=="success"){
                    if(CBFunction){
                        CBFunction(xmlhttp.response);
                        if(loader)loader.classList.remove('loading');
                    }
                }else{
                    console.log("error");
                    if(loader)loader.classList.remove('loading');
                }
            } else {

            }
        }
        xmlhttp.send(data);
    },
    getXmlHttp:function(){
        var xmlhttp;
        try {
            xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (E) {
                xmlhttp = false;
            }
        }
        if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
            xmlhttp = new XMLHttpRequest();
        }
        return xmlhttp;
    }



}
document.addEventListener("DOMContentLoaded", micro.initCartScript);
