// pages/home/happy/happy.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    can_click: 1,
    tabcurrent: 'video',
    video_page: 1,
    image_page: 1,
    text_page: 1,
    video_more_tips: '正在加载中...',
    image_more_tips: '正在加载中...',
    text_more_tips: '正在加载中...',
    titarr: [{
        tit: "视频",
        type: "video",
      },
      {
        tit: "漫画",
        type: "image",
      },
      {
        tit: "段子",
        type: "text",
      },
    ],
    nodes: [{
      name: 'div',
      attrs: {
        class: 'div_class',
        style: 'line-height:40px; color: red;'
      },
      children: [{
        type: 'text',
        text: 'Hello&nbsp;World!'
      }]
    }],
    video_list: [],
    image_list: [],
    text_list: [],
  },
  singelbtn: function(e) {
    var index = e.currentTarget.dataset.index;
    var type = e.currentTarget.dataset.type;
    console.log(type);
    if (this.data.can_click == 0) return false;
    this.setData({
      tabcurrent: type,
    })
    // switch (type) {
    //   case "video":
    //     getVideoList(this);
    //     break;
    //   case "image":
    //     getImageList(this);
    //     break;
    //   case "text":
    //     getTextList(this);
    //     break;
    // }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {
    var that =this;
    getVideoList(this,function(){
      getImageList(that,function(){
        getTextList(that);
      });
    });
    
    
    
   
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    var type = this.data.tabcurrent;
    switch (type) {
      case "video":
        getVideoList(this,function(){});
        break;
      case "image":
        getImageList(this, function () { });
      case "text":
        getTextList(this);
        break;
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})

var getVideoList = function (that,callback) {  
  var more_tips = '正在加载中...';
  var video_list = that.data.video_list;
  var page = that.data.video_page;
  if (that.data.can_click == 0) return false;
  that.setData({
    can_click: 0,
    video_more_tips: more_tips,
  })
  var data = {
    loading: 0,
    type: 'video',
    keyword: '',
    sort: '',
    asc: '',
    page: page,
    pagenum: 10,
  }
  console.log(data)
  var url = app.HOST + 'applet/Home/happly'; //仅为示例，非真实的接口地址
  app.fetchPost(url, data, function(res, data) {
    console.log(data)
    var list = data.data;
    if (page == 1 && data.data.length == 0) {
      more_tips = '暂无数据~';
    } else if (data.data.length < 10) {
      more_tips = '没有更多了';
    } else {
      more_tips = '上拉加载更多';      
    }
    page++;
    if (list.length) {
      for (var i = 0; i < list.length; i++) {
        video_list.push(list[i])
      }
    }
    that.setData({
      can_click: 1,
      video_page:page,
      video_more_tips: more_tips,
      video_list: video_list,
    });
    callback();
  });
}
var getImageList = function (that,callback) {
  var more_tips = '正在加载中...';
  var image_list = that.data.image_list;
  var page = that.data.image_page;
  if (that.data.can_click == 0) return false;
  that.setData({
    can_click: 0,
    image_more_tips: more_tips,
  })
  var data = {
    loading: 0,
    type: 'image',
    keyword: '',
    sort: '',
    asc: '',
    page: page,
    pagenum: 10,
  }
  console.log(data)
  var url = app.HOST + 'applet/Home/happly'; //仅为示例，非真实的接口地址
  app.fetchPost(url, data, function (res, data) {
    console.log(data)
    var list = data.data;
    if (page == 1 && data.data.length == 0) {
      more_tips = '暂无数据~';
    } else if (data.data.length < 10) {
      more_tips = '没有更多了';
    } else {
      more_tips = '上拉加载更多';
    }
    page++;
    if (list.length) {
      for (var i = 0; i < list.length; i++) {
        image_list.push(list[i])
      }
    }
    that.setData({
      can_click: 1,
      image_page: page,
      image_more_tips: more_tips,
      image_list: image_list,
    });
    callback();
  });
}
var getTextList = function (that) {
  var more_tips = '正在加载中...';
  var text_list = that.data.text_list;
  var page = that.data.text_page;
  if (that.data.can_click == 0) return false;
  that.setData({
    can_click: 0,
    text_more_tips: more_tips,
  })
  var data = {
    loading: 0,
    type: 'text',
    keyword: '',
    sort: '',
    asc: '',
    page: page,
    pagenum: 10,
  }
  console.log(data)
  var url = app.HOST + 'applet/Home/happly'; //仅为示例，非真实的接口地址
  app.fetchPost(url, data, function (res, data) {
    console.log(data)
    var list = data.data;
    if (page == 1 && data.data.length == 0) {
      more_tips = '暂无数据~';
    } else if (data.data.length < 10) {
      more_tips = '没有更多了';
    } else {
      more_tips = '上拉加载更多';
    }
    page++;
    if (list.length) {
      for (var i = 0; i < list.length; i++) {
        text_list.push(list[i])
      }
    }
    that.setData({
      can_click: 1,
      text_page: page,
      text_more_tips: more_tips,
      text_list: text_list,
    })
  })
}