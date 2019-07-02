var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    can_click:1,
    more_tips: '正在加载中...',
    auth_hide:0,
    carousel:[],
    list:[],
    has_coupon:[],
    //   swiper
    listsArr: [],
    couponArr: [],
    hidecai: 1,
    happy_open:0,
  },
  /**
   *商品 搜索
   */
  search_do:function(e){
      console.log(e);
      var keyword =e.detail.value;
    if (!keyword){
      wx.showToast({
        title: '请输入关键词',
        icon:'success',
        // image:"/images/edit.png",
        duration:1500
      });
      return false;
    }
      wx.navigateTo({
        url: '/pages/home/goodsList/goodsList?keyword='+keyword,
      })
  },


  /**
   * 授权
   */
  authUserInfo: function (e) {
    var that = this;
    console.log(e)
    wx.getUserInfo({
      lang: 'zh_CN',
      success: function (res) {
        var user = wx.getStorageSync('user');
        var share_id = wx.getStorageSync('share_id');
        app.operation('Applet/checkopenid', {
          openid: user.openid,
          headimgurl: res.userInfo.avatarUrl,
          city: res.userInfo.city,
          country: res.userInfo.country,
          province: res.userInfo.province,
          sex: res.userInfo.gender,
          nickname: res.userInfo.nickName,
          share_id: share_id,
        }, function (data) {
          console.log(data)
          app.globalData.userInfo = res.userInfo
          typeof cb == "function" && cb(app.globalData.userInfo)
          wx.setStorageSync('userInfo', res.userInfo);
        });
        that.setData({
          auth_hide:0,
        })
      },
      fail: function (e) {
        console.log(e);
      }
    })
  },
  close: function (e) {
    this.setData({
      hide: 0,
      hidecai: 0,
    })
  },
  showcai: function (e) {
    this.setData({
      hidecai: 1,
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {    
    console.log(options);
    if (typeof (options.share_id) == "undefined"){
      wx.setStorageSync("share_id", options.share_id);
    }   
    if (options.scene) {
      let scene = decodeURIComponent(options.scene);
      //&是我们定义的参数链接方式      
      let userId = options.scene.split("&")[0];
      if (options.scene) {
        let scene = decodeURIComponent(options.scene);
        //&是我们定义的参数链接方式
        let userId = scene;
        wx.setStorageSync("share_id", userId);
      }
    } 
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    var that =this;
    getList(this,1);
    app.getData('Home/index', this, { loading: 1 }, function (data) {
      console.log(data)
    })

    //判断是否授权
    wx.getUserInfo({
      success: function (res) {
        that.setData({
          auth_hide: 0,
        })
      },
      fail: function (res) {
        that.setData({
          auth_hide: 1,
        })
      }
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    
  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    getList(this,0);
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function (res) {
    var user = wx.getStorageSync('user');
    console.log(user);
    if (res.from === 'button') {
      // 来自页面内转发按钮
      console.log(res.target)
    }
    return {
      title:"晓爱商城",
      path: '/pages/home/index/index?share_id=' + user.wx_vip_id,
    }
  }
  
})

var getList = function (that, page) {
  if (that.data.can_click == 0) return false;
  var more_tips = '正在加载中...';  
  that.setData({
    can_click: 0,
    more_tips: more_tips,
  })
  var data = {
    loading: 0,
    category_id: 0,
    keyword: '',
    sort: 'sales',
    asc: 'desc',
    pagenum: 10,
  }
  console.log(data)
  app.getList('Home/getNewGoods', that, data, page, function (data) {
    if (page == 1 && data.data.length == 0) {
      more_tips = '暂无数据~';
    } else if (data.data.length < 10) {
      more_tips = '没有更多了';
    } else {
      more_tips = '上拉加载更多';
    }
    that.setData({
      can_click: 1,
      more_tips: more_tips,
    })
  })
}