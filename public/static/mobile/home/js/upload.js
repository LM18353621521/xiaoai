
$(function(){		
	
		//以下为9.10新增上传图片功能
		// $(".upload_box").height($(".upload_box").width());
		// var width = $('.upload_box').width();
		// $('.upload_box').height(width);
		// $('.upload_box input').height(width);
		// $('.upload_box input').width(width);
		// $('.upload_box .img img').css({'max-width':width,'max-height':width})
		
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
		if($('#ImgPath6').val()!=''){
			$('#upload_box6').removeClass('no_img');
		}
		if($('#ImgPath7').val()!=''){
			$('#upload_box7').removeClass('no_img');
		}
		if($('#ImgPath8').val()!=''){
			$('#upload_box8').removeClass('no_img');
		}
		if($('#ImgPath9').val()!=''){
			$('#upload_box9').removeClass('no_img');
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
	                
	                $('#ImgPath1').val( result.base64);
	                //跳出下一个上传
					$('#upload_box1').next(".upload_box").show();
					$('#upload_box1').removeClass('no_img')
					$(".upload_num").text("1");
					$("#ImgDetailDiv1").removeClass().addClass("a1");
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
	                $('#ImgPath2').val( result.base64);
	                
	                //跳出下一个上传
					$('#upload_box2').next(".upload_box").show();
					$('#upload_box2').removeClass('no_img')
					$(".upload_num").text("2");
					$("#ImgDetailDiv2").removeClass().addClass("a1");
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
	                $('#ImgPath3').val( result.base64);
	                
	                //跳出下一个上传
					$('#upload_box3').next(".upload_box").show();
					$('#upload_box3').removeClass('no_img')
					$(".upload_num").text("3");
					$("#ImgDetailDiv3").removeClass().addClass("a1");
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
	                $('#ImgDetailDiv4').attr('src',  result.base64);
	                $('#ImgPath4').val( result.base64);
	                
	                //跳出下一个上传
					$('#upload_box4').next(".upload_box").show();
					$('#upload_box4').removeClass('no_img')
					$(".upload_num").text("4");
					$("#ImgDetailDiv4").removeClass().addClass("a1");
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
	                $('#ImgDetailDiv5').attr('src',  result.base64);
	                $('#ImgPath5').val( result.base64);
	                
	                //跳出下一个上传
					$('#upload_box5').next(".upload_box").show();
					$('#upload_box5').removeClass('no_img')
					$(".upload_num").text("5");
					$("#ImgDetailDiv5").removeClass().addClass("a1");
	            }
	        }); 
		});
		
		$("#upload_box6").click(function(){		
			$('#ImgUpload6').localResizeIMG({
				width: setWidth*2,
	            quality: 2,
	            //before: function (that, blob) {},
	            success: function (result) 
	            {
	                $('#ImgDetailDiv6').attr('src',  result.base64);
	                $('#ImgPath6').val( result.base64);
	                
	                //跳出下一个上传
					$('#upload_box6').next(".upload_box").show();
					$('#upload_box6').removeClass('no_img')
					$(".upload_num").text("6");
					$("#ImgDetailDiv6").removeClass().addClass("a1");
	            }
	        }); 
		});
		
		$("#upload_box7").click(function(){		
			$('#ImgUpload7').localResizeIMG({
				width: setWidth*2,
	            quality: 2,
	            //before: function (that, blob) {},
	            success: function (result) 
	            {
	                $('#ImgDetailDiv7').attr('src',  result.base64);
	                $('#ImgPath7').val( result.base64);
	                
	                //跳出下一个上传
					$('#upload_box7').next(".upload_box").show();
					$('#upload_box7').removeClass('no_img')
					$(".upload_num").text("7");
					$("#ImgDetailDiv7").removeClass().addClass("a1");
	            }
	        }); 
		});
		
		$("#upload_box8").click(function(){		
			$('#ImgUpload8').localResizeIMG({
				width: setWidth*2,
	            quality: 2,
	            //before: function (that, blob) {},
	            success: function (result) 
	            {
	                $('#ImgDetailDiv8').attr('src',  result.base64);
	                $('#ImgPath8').val( result.base64);
	                
	                //跳出下一个上传
					$('#upload_box8').next(".upload_box").show();
					$('#upload_box8').removeClass('no_img')
					$(".upload_num").text("8");
					$("#ImgDetailDiv8").removeClass().addClass("a1");
	            }
	        }); 
		});
		
		$("#upload_box9").click(function(){		
			$('#ImgUpload9').localResizeIMG({
				width: setWidth*2,
	            quality: 2,
	            //before: function (that, blob) {},
	            success: function (result) 
	            {
	                $('#ImgDetailDiv9').attr('src',  result.base64);
	                $('#ImgPath9').val( result.base64);
	                
	                //跳出下一个上传
					$('#upload_box9').next(".upload_box").show();
					$('#upload_box9').removeClass('no_img')
					$(".upload_num").text("9");
					$("#ImgDetailDiv9").removeClass().addClass("a1");
	            }
	        }); 
		});




			
	})