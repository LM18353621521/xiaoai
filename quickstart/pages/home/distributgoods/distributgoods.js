// pages/home/distributionlist/distributionlist.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    can_click: 1,
    showfuceng: 1,
    f2: 0,
    f3: 0,
    more_tips: "正在加载中...",
    page: 1,
    keyword: "",
    sort: 'is_recommend',
    asc: 'desc',
    list: [],
    goodsInfo: [],
  },
  /**
   * 我要推荐
   */
  share_do:function(e){
    console.log(e)
    var index = e.currentTarget.dataset.index;
    var list =this.data.list;
    this.setData({
      goodsInfo:list[index],
    })
    this.showf3();
  },
  /**
   *保存图片
   */
  save_poster:function(e){
    console.log(e);
    var that = this;
    if (that.data.can_click == 0) {
      return false;
    }
    that.save_img_loc(that.data.poster_img);
  },
  /**
   * 分享到朋友圈
   */
  shre_friend_circle: function(e) {
    var that = this;
    wx.getSetting({
      success(res) {
        if (!res.authSetting['scope.writePhotosAlbum']) {
          wx.authorize({
            scope: 'scope.writePhotosAlbum',
            success() {
              console.log('授权成功');
              that.create_poster();
            }
          })
        } else {
          that.create_poster();
        }
      }
    })
  },
  /**
   * 生成分享图片
   */
  create_poster:function(){
    wx.showLoading({
      title: '正在生成海报...',
    })
    var that=this;
    var goodsInfo = this.data.goodsInfo;
    var data={
      goods_id:goodsInfo.id,
    }
    app.getData('Home/create_poster', that, data, function (data) {
      console.log(data);
      that.showf2();
      wx.hideLoading();
      that.setData({
        can_click: 1,
      });
    })
  },

  /**
   * 保存图片到相册
   */
  save_img_loc: function(img_path) {
    var that = this;
    var imgSrc = img_path;
    wx.downloadFile({
      url: imgSrc,
      success: function(res) {
        console.log(res);
        //图片保存到本地
        wx.saveImageToPhotosAlbum({
          filePath: res.tempFilePath,
          success: function(data) {
            wx.showToast({
              title: '保存成功',
              icon: 'success',
              duration: 2000
            })
            that.showf2();
          },
          fail: function(err) {
            console.log(err);
            if (err.errMsg === "saveImageToPhotosAlbum:fail auth deny") {
              console.log("当初用户拒绝，再次发起授权")
              wx.openSetting({
                success(settingdata) {
                  console.log(settingdata)
                  if (settingdata.authSetting['scope.writePhotosAlbum']) {
                    console.log('获取权限成功，给出再次点击图片保存到相册的提示。')
                  } else {
                    console.log('获取权限失败，给出不给权限就无法正常使用的提示')
                  }
                }
              })
            }
          },
          complete(res) {
            console.log(res);
          }
        })
      }
    })
  },

  showf2: function(e) {
    var f2 = this.data.f2;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f2: 1,
      f3: 0,
      showfuceng: 0
    })
  },
  hidef3: function(e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 0,
      showfuceng: 1,
      can_click: 1,
    })
  },
  showf3: function(e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 1,
      showfuceng: 0,
    })
  },
  hidef2: function(e) {
    var f2 = this.data.f2;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f2: 0,
      showfuceng: 1,
      can_click: 1,
    })
  },

  hidef3: function(e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 0,
      showfuceng: 1,
      can_click: 1,
    })
  },
  showf3: function(e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 1,
      showfuceng: 0
    })
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
    var that = this;
    app.getData('Home/distributgoods', this, { loading: 1}, function (data) {
      console.log(data)
    });
    getList(this, 1);
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
    getList(this, 0);
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function(res) {
    console.log(res);
    var user = wx.getStorageSync('user');
    console.log(user);
    var goodsInfo = this.data.goodsInfo;
    if (res.from === 'button') {
      // 来自页面内转发按钮
      console.log(res.target)
    }
    return {
      title: goodsInfo.name,
      path: '/pages/home/detail/detail?id=' + goodsInfo.id + "&share_id=" + user.wx_vip_id,
      imageUrl: goodsInfo.coverimg,
    }
  }
})

var getList = function(that, page) {
  var more_tips = '正在加载中...';
  if (that.data.can_click == 0) return false;
  that.setData({
    can_click: 0,
    more_tips: more_tips,
  })
  var data = {
    loading: 0,
    category_id: 0,
    keyword: that.data.keyword,
    sort: that.data.sort,
    asc: that.data.asc,
    pagenum: 10,
  }
  // console.log(data)
  app.getList('Home/shareGoodsList', that, data, page, function(data) {
    console.log(data)
    if (page == 1 && data.data.length == 0) {
      more_tips = '暂无数据~';
    } else if (data.data.length < 10) {
      more_tips = '人家是有底线的';
    } else {
      more_tips = '加载更多';
    }
    that.setData({
      can_click: 1,
      more_tips: more_tips,
    })
  })
}