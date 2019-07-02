// pages/vip/distribution/distribution.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    can_click: 1,
    auth_hide: 0,
    show: 0,
    fuhide: 0,
    fuhidea: 0,
    status: 0,
    money: '',
  },

  /**
   * 生成分享图片
   */
  create_poster: function () {
    wx.showLoading({
      title: '正在生成海报...',
    })
    var that = this;
    var goodsInfo = this.data.goodsInfo;
    var data = {
      goods_id: goodsInfo.id,
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
   *保存图片
   */
  save_poster: function (e) {
    console.log(e);
    var that = this;
    if (that.data.can_click == 0) {
      return false;
    }
    wx.showLoading({
      title: '正在生成海报...',
    })
    var that = this;
    app.getData('Vip/create_user_poster', that, {}, function (data) {
      console.log(data);
      var poster_img = data.data.poster_img;
      setTimeout(function(){
        that.save_img_loc(poster_img);
      },500);
    })
    // that.save_img_loc(that.data.poster_img);
  },
  /**
 * 保存图片到相册
 */
  save_img_loc: function (img_path) {
    var that = this;
    var imgSrc = img_path;
    wx.downloadFile({
      url: imgSrc,
      success: function (res) {
        console.log(res);
        //图片保存到本地
        wx.saveImageToPhotosAlbum({
          filePath: res.tempFilePath,
          success: function (data) {
            wx.showToast({
              title: '保存成功',
              icon: 'success',
              duration: 2000
            })
            that.showf2();
          },
          fail: function (err) {
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

  /**
   * 提现
   */
  withdraw_do: function(e) {
    var that = this;
    if (that.data.can_cliak == 0) {
      return false
    }
    var vip = that.data.vip;
    var data = {
      money: that.data.money,
    }

    if (!data.money) {
      app.alert('！请输入提现金额');
      return false;
    }

    if (data.money > vip.income) {
      app.alert('您的可提现金额为:' + vip.income + "元");
      return false;
    }

    that.setData({
      can_click: 0,
    })
    app.operation('Vip/withdraw', data, function(res) {
      console.log(data);
      if (res.ret == 1) {
        wx.showToast({
          title: res.msg,
        })
        vip.income -= data.money;
        that.setData({
          vip: vip,
          money: '',
          fuhide: 0,
          can_click: 1,
        })
      } else {
        app.alert(res.msg);
        that.setData({
          can_click: 0,
        })
      }
    });
  },
  /**
   * 输入提箱金额
   */
  set_money: function(e) {
    console.log(e.detail.value);
    this.setData({
      money: e.detail.value,
    })
  },

  swichNav: function(e) {
    var that = this;
    var status = e.target.dataset.current;
    this.setData({
      status: status,
    });
    getList(this, 1);
  },
  show: function(e) {
    this.setData({
      fuhide: 1,
    })
  },
  close: function(e) {
    this.setData({
      fuhide: 0,
    })
  },
  showa: function(e) {
    this.setData({
      fuhidea: 1,
    })
  },
  closea: function(e) {
    this.setData({
      fuhidea: 0,
    })
  },


  /**
   * 授权
   */
  authUserInfo: function(e) {

    var that = this;
    console.log(e)
    wx.getUserInfo({
      lang: 'zh_CN',
      success: function(res) {
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
        }, function(data) {
          console.log(data)
          app.globalData.userInfo = res.userInfo
          typeof cb == "function" && cb(app.globalData.userInfo)
          wx.setStorageSync('userInfo', res.userInfo);
        });
        that.setData({
          auth_hide: 0,
        })
      },
      fail: function(e) {
        console.log(e);
      }
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
    //判断是否授权
    wx.getUserInfo({
      success: function(res) {
        that.setData({
          auth_hide: 0,
        })
      },
      fail: function(res) {
        that.setData({
          auth_hide: 1,
        })
      }
    });

    app.getData('Vip/distribution', this, {
      loading: 1
    }, function(data) {
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
  onShareAppMessage: function() {

  }
})

var getList = function(that, page) {
  if (that.data.can_click == 0) return false;
  var more_tips = '正在加载中...';
  var no_order = 0;
  that.setData({
    no_order: 0,
    can_click: 0,
    more_tips: more_tips,
  })
  var data = {
    loading: 0,
    status: that.data.status,
    keyword: '',
    sort: 'create_time',
    asc: 'desc',
    pagenum: 10,
  }
  console.log(data)
  app.getList('Vip/distribution_log', that, data, page, function(data) {
    if (page == 1 && data.data.length == 0) {
      no_order = 1;
      more_tips = '暂无数据~';
    } else if (data.data.length < 10) {
      more_tips = '没有更多了';
    } else {
      more_tips = '上拉加载更多';
    }
    that.setData({
      no_order: no_order,
      can_click: 1,
      more_tips: more_tips,
    })
  })
}
