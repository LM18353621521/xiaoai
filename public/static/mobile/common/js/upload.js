$(function(){		
		//以下为9.10新增上传图片功能
		$(".upload_box").height($(".upload_box").width());
		var width = $('.upload_box').width();
		$('.upload_box').height(width);
		$('.upload_box input').height(width);
		$('.upload_box input').width(width);
		$('.upload_box .img img').css({'max-width':width,'max-height':width})
		
		var id =$('input[name=id]').val();
		if(id){
			$('.upload_box').show();
		}
		var setWidth = $(window).width();
		//如果修改图片存在 则删除class no_img
		if($('#ImgPath1').val()!=''){
			$('#upload_box1').removeClass('no_img');
		}
		if($('#ImgPath2').val()!=''){
			$('#upload_box2').removeClass('no_img');
		}
		if($('#ImgPath3').val()!=''){
			$('#upload_box3').removeClass('no_img');
		}
		if($('#ImgPath4').val()!=''){
			$('#upload_box4').removeClass('no_img');
		}
		if($('#ImgPath5').val()!=''){
			$('#upload_box5').removeClass('no_img');
		}
		
		
		//上传图片
		$("#upload_box1").click(function(){
			$('#ImgUpload1').localResizeIMG({
				width: setWidth*2,
	            quality: 2,
	            //before: function (that, blob) {},
	            success: function (result) 
	            {
	                $('#ImgDetailDiv1').attr('src', result.base64);
	                
	                $('#ImgPath1').val(result.clearBase64);
	                //跳出下一个上传
					$('#upload_box1').next(".upload_box").show();
					$('#upload_box1').removeClass('no_img')
					$(".upload_num").text("1");
	            }
	        });

		});
		$("#upload_box2").click(function(){	
			$('#ImgUpload2').localResizeIMG({
				
	            width: setWidth*2,
	            quality: 2,
	            //before: function (that, blob) {},
	            success: function (result) 
	            {
	                $('#ImgDetailDiv2').attr('src', result.base64);
	                $('#ImgPath2').val(result.clearBase64);
	                
	                //跳出下一个上传
					$('#upload_box2').next(".upload_box").show();
					$('#upload_box2').removeClass('no_img')
					$(".upload_num").text("2");
	            }
	        });
			
		});
		
		$("#upload_box3").click(function(){		
			$('#ImgUpload3').localResizeIMG({
				width: setWidth*2,
	            quality: 2,
	            //before: function (that, blob) {},
	            success: function (result) 
	            {
	                $('#ImgDetailDiv3').attr('src', result.base64);
	                $('#ImgPath3').val(result.clearBase64);
	                
	                //跳出下一个上传
					$('#upload_box3').next(".upload_box").show();
					$('#upload_box3').removeClass('no_img')
					$(".upload_num").text("3");
	            }
	        });
			
		});
		
		$("#upload_box4").click(function(){		
			$('#ImgUpload4').localResizeIMG({
				width: setWidth*2,
	            quality: 2,
	            //before: function (that, blob) {},
	            success: function (result) 
	            {
	                $('#ImgDetailDiv4').attr('src', result.base64);
	                $('#ImgPath4').val(result.clearBase64);
	                
	                //跳出下一个上传
					$('#upload_box4').next(".upload_box").show();
					$('#upload_box4').removeClass('no_img')
					$(".upload_num").text("4");
	            }
	        });
			
		});

		$("#upload_box5").click(function(){		
			$('#ImgUpload5').localResizeIMG({
				width: setWidth*2,
	            quality: 2,
	            //before: function (that, blob) {},
	            success: function (result) 
	            {
	                $('#ImgDetailDiv5').attr('src', result.base64);
	                $('#ImgPath5').val(result.clearBase64);
	                
	                //跳出下一个上传
					$('#upload_box5').next(".upload_box").show();
					$('#upload_box5').removeClass('no_img')
					$(".upload_num").text("5");
	            }
	        });
			
		})
			
	})