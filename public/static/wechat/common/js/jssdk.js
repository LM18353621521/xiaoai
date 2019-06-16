var config_obj={
	config_init:function(){
		wx.config({
		    debug: false,// 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印
		    appId: config.appid,// 必填，公众号的唯一标识
		    timestamp: config.timestamp, // 必填，生成签名的时间戳
		    nonceStr: config.nonceStr,// 必填，生成签名的随机串
		    signature: config.signature,// 必填，签名
		    jsApiList: [// 必填，需要使用的JS接口列表，所有JS接口列表见附录2
		        //分享接口
		        'onMenuShareTimeline',//分享到朋友圈
		        'onMenuShareAppMessage',//分享给朋友
		        'onMenuShareQQ',//分享到QQ
		        'onMenuShareWeibo',//分享到腾讯微博
		        'onMenuShareQZone',//分享到QQ空间
		        
		        //音频接口
		        "startRecord",	//开始录音接口
		        "stopRecord",	//停止录音接口
		        "onVoiceRecordEnd",	//监听录音自动停止接口
		        "playVoice",	//播放语音接口
		        "pauseVoice",	//暂停播放接口
		        "stopVoice",	//停止播放接口
		        "onVoicePlayEnd",	//监听语音播放完毕接口
		        "uploadVoice",	//上传语音接口
		        "downloadVoice",	//下载语音接口
		        
		        //图像接口
		        'chooseImage',//拍照或从手机相册中选图接口
		        'previewImage',//预览图片接口
		        'uploadImage',//上传图片接口
		        'downloadImage',//下载图片接口
		        
		        //智能接口
		        'translateVoice',	//识别音频并返回识别结果接口
		        
		        //设备信息
		        'getNetworkType',	//获取网络状态接口
		        
		        //地理位置
		        'openLocation',//使用微信内置地图查看位置接口
		        'getLocation',//获取地理位置接口    
		        
		        //界面操作
		        'hideOptionMenu',//隐藏右上角菜单接口
		        'showOptionMenu',//显示右上角菜单接口
		        'hideMenuItems',//批量隐藏功能按钮接口
		        'showMenuItems',//批量显示功能按钮接口
		        'hideAllNonBaseMenuItem',//隐藏所有非基础按钮接口
		        'showAllNonBaseMenuItem',//显示所有功能按钮接口
		        'closeWindow',//关闭当前网页窗口接口
		        
		        //微信扫一扫
		        'scanQRCode',	//调起微信扫一扫接口
		        
		        //微信支付
		        'chooseWXPay',	//发起一个微信支付请求
		        
		        //微信小店
		        'openProductSpecificView',	//跳转微信商品页接口
		        
		        //微信卡券
		        'addCard',		//批量添加卡券接口
		        'chooseCard',	//拉取适用卡券列表并获取用户选择信息
		        'openCard',	//查看微信卡包中的卡券接口
		        
		        //摇一摇周边
		        //'startSearchBeacons',	//开启查找周边ibeacon设备接口
		        //'stopSearchBeacons',	//关闭查找周边ibeacon设备接口
		        //'onSearchBeacons'		//监听周边ibeacon设备接口
		        
		        'checkJsApi',
                'openAddress',//收货地址共享
		    ]
		});
	},
}

var share_obj={
	share_init:function(){
		if(!share.imgurl){
			share.imgurl = './Public/Wechat/images/share_logo.jpg';
		}
		(typeof(share.link)=='undefined' || !share.link) && (share.link=window.location.href); // 分享链接
		(typeof(share.title)=='undefined' || !share.title) && (share.title=document.title);// 分享标题
		(typeof(share.desc)=='undefined' || !share.desc) && (share.desc=share.title);  // 分享描述
		(typeof(share.trans)=='undefined' || !share.trans) && (share.trans=1);
			

		wx.ready(function(){
			//分享到朋友圈
			wx.onMenuShareTimeline({
				title: share.title, 
				link: share.link,
				imgUrl: share.imgurl, 
				success: function () { 
					// 用户确认分享后执行的回调函数
				},
				cancel: function () { 
					// 用户取消分享后执行的回调函数
				}
			});
		  
			//分享给朋友
			wx.onMenuShareAppMessage({
				title: share.title, 
				desc: share.desc, // 分享描述
				link: share.link,
				imgUrl: share.imgurl,
				type: '', // 分享类型,music、video或link，不填默认为link
				dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
				success: function () { 
					// 用户确认分享后执行的回调函数
				},
				cancel: function () { 
					// 用户取消分享后执行的回调函数
				}
			});
			
			//分享到QQ
			wx.onMenuShareQQ({
			    title: share.title, // 分享标题
			    desc: share.desc, // 分享描述
			    link: share.link, // 分享链接
			    imgUrl: share.imgurl, // 分享图标
			    success: function () { 
			       // 用户确认分享后执行的回调函数
			    },
			    cancel: function () { 
			       // 用户取消分享后执行的回调函数
			    }
			});
			
			//分享到腾讯微博
			wx.onMenuShareWeibo({
			    title: share.title, // 分享标题
			    desc: share.desc, // 分享描述
			    link: share.link, // 分享链接
			    imgUrl: share.imgurl, // 分享图标
			    success: function () { 
			       // 用户确认分享后执行的回调函数
			    },
			    cancel: function () { 
			        // 用户取消分享后执行的回调函数
			    }
			});
			
			//分享到QQ空间
			wx.onMenuShareQZone({
			    title: share.title, // 分享标题
			    desc: share.desc, // 分享描述
			    link: share.link, // 分享链接
			    imgUrl: share.imgurl, // 分享图标
			    success: function () { 
			       // 用户确认分享后执行的回调函数
			    },
			    cancel: function () { 
			        // 用户取消分享后执行的回调函数
			    }
			});
		});
	},
}

var image_obj={
	image_init:function(){
		image_obj.image_width();
		html='<div class="upload_box choose_image">'+$('.choose_image').html()+'</div>';
		$(document).on("click",".choose_image",function(){
			wx.chooseImage({
			    count: number, // 默认9
			    sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
			    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
			    success: function (res) {
			        var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片 
			        
			        for(var i=0;i<localIds.length;i++){	
			        	$(".choose_image").find("img").attr("src",localIds[i]);
			        	$(".choose_image").find("input[name=localid\\[\\]]").val(localIds[i]);
			        	$(".choose_image").removeClass("choose_image");

			    		number --;  	
			        	if(number > 0){	
			        		$(".upload_img").append(html);
			        		image_obj.image_width();
					    }else{
				        	return false;
				        }			        	
			        }
			    }
			});
		});
		
		$(document).on("click",".upload_box:not(.choose_image) .img",function(){
			var url = $(this).parent().find("input[name=localid\\[\\]]").val();
			var urls = new Array();
			$(".upload_box:not(.choose_image)").each(function(){
				urls.push($(this).find("input[name=localid\\[\\]]").val());
			})
			image_obj.preview_image(url,urls);
		});
		
		$(document).on("click",".upload_box:not(.choose_image) .del",function(){
			$(this).parents(".upload_box").remove();
			number ++;
			if(!$(".choose_image").length){	
        		$(".upload_img").append(html);
		        
        		image_obj.image_width();
		    }
		});
	},
	
	image_width:function(){
		$(".upload_box").height($(".upload_box").width());
		var width = $('.upload_box').width();
		$('.upload_box').height(width);
		$('.upload_box .img').css({'max-width':width,'max-height':width});
		$('.upload_box .img img').css({'width':"200px",'height':"auto"});
		$('.upload_box .img').css({"clip":"rect(0px "+width+'px'+" "+width+'px'+" 0px)"});
	},
	
	preview_image:function(url,urls){	
		wx.previewImage({
			current: url, // 当前显示图片的http链接
			urls: urls, // 需要预览的图片http链接列表
		});
	},
	
	upload_image:function(localIds){
		if((localIds == '') || (typeof localIds == 'undefined')){
			localIds = new Array();
			$(".upload_box:not(.choose_image)").each(function(){
				localIds.push($(this).find("input[name=localid\\[\\]]").val());
				
			});
		}
		
		if(localIds.length > 0){		
			var localid = localIds.shift();
		}else{
			upload_success();return false;
		}
		
		wx.uploadImage({
		    localId: localid, // 需要上传的图片的本地ID，由chooseImage接口获得
		    isShowProgressTips: 1, // 默认为1，显示进度提示
		    success: function (res) {
		    	var serverId = res.serverId; // 返回图片的服务器端ID	此处获得的 serverId 即 media_id
		    	$("input[name=serverid\\[\\]]").each(function(){
		    		if($(this).val() == ''){
		    			$(this).val(serverId);return false;
		    		}
		    	})		    	
		        if(localIds.length > 0){
		        	image_obj.upload_image(localIds);
		        }else{
		        	upload_success();
		        	return false;
		        }
		    }
		});
	},
	
	down_image:function(){
		wx.downloadImage({
		    serverId: '', // 需要下载的图片的服务器端ID，由uploadImage接口获得
		    isShowProgressTips: 1, // 默认为1，显示进度提示
		    success: function (res) {
		        var localId = res.localId; // 返回图片下载后的本地ID
		    }
		});
	},
}

var location_obj={
	location_init:function(){
		wx.ready(function(){
			wx.getLocation({
			    type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
			    success: function(res){
			        var location = new Array();
			        location['lat'] = res.latitude; // 纬度，浮点数，范围为90 ~ -90
			        location['lng'] = res.longitude;// 经度，浮点数，范围为180 ~ -180。
			        location['lng'] = res.speed; // 速度，以米/每秒计
			        location['lng'] = res.accuracy; // 位置精度
			        
			        get_success(location);
			        return false;
			    }
			});
		});
	},

	open_location:function(location){
		wx.openLocation({
		    latitude: location.lat, // 纬度，浮点数，范围为90 ~ -90
		    longitude: location.lng, // 经度，浮点数，范围为180 ~ -180。
		    name: location.name, // 位置名
		    address: location.address, // 地址详情说明
		    scale: 15, // 地图缩放级别,整形值,范围从1~28。默认为最大
		    infoUrl: location.infourl // 在查看位置界面底部显示的超链接,可点击跳转
		});
	},
}

var window_obj={
	close_window:function(){
		wx.closeWindow();
	},
	
	hide_menu:function(){
		wx.ready(function(){
			wx.hideOptionMenu();
		});
	},
	
	show_menu:function(){
		wx.ready(function(){
			wx.showOptionMenu();
		});
	},
	
	hide_item:function(){
		wx.ready(function(){
			wx.hideMenuItems({
				menuList: [
				    "menuItem:share:timeline"
			    ] // 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮
			});
		});
	},
	
	show_item:function(){
		wx.ready(function(){
			wx.showMenuItems({
				 menuList: [  
				     "menuItem:editTag",
				     //"menuItem:addContact"
				 ]	//只能显示部分“传播类”和保护类
			});
		});
	},
	
	hide_all:function(){
		wx.ready(function(){
			wx.hideAllNonBaseMenuItem();
		});
	},
	
	show_all:function(){
		wx.ready(function(){
			wx.showAllNonBaseMenuItem();
		});
	},
}

var scanqrcode_obj = {
	scan_qrcode:function(){
		wx.scanQRCode({
		    needResult: 0, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
		    scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
		    success: function (res) {
		    	var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
		    }
		});
	}
}

var voice_obj={
	start_record:function(){
		wx.startRecord();
	},
	
	stop_record:function(){
		wx.stopRecord({
		    success: function (res) {
		        var localId = res.localId;
		        $("input[name=localid]").val(localId);
		    }
		});
	},
	
	
}

