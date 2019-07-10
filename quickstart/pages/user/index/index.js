// pages/user/vip/vip.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    can_click: 1,
    auth_hide: 0,
    current: 0,
    f3: 0,
    f4: 0,
    f5: 0,
    f6: 0,
    second: 60,
    selected: false,
    selected1: true,
    vip: [],
    order_status: [],

    bind_hide: true,
    //切换账号
    mobile: "",
    code: "",

    //验证码
    code_mobile: "",
    code_code: "",
  },
  /**
   * 修改手机号第一步
   */
  change_mobile_one: function(e) {
    var that=this;
    var code = that.data.code;
    var code_mobile = that.data.code_mobile;
    var code_code = that.data.code_code;
    if (code == "") {
      app.alert("请输入您收到的验证码");
      return false;
    }
    if (code != code_code) {
      app.alert("验证码输入不正确");
      return false;
    }
    this.setData({
      code_mobile: "",
      code_code: "",
      code: '',
      second: 0,
      selected: false,
      selected1: true,
      f5: 0,
      f6: 1,
    })
  },
  /**
   * 修改手机号码
   */
  change_mobile_two: function(e) {
    var that = this;
    if (that.data.can_click == 0) {
      return false
    }
    var mobile = that.data.mobile;
    var code = that.data.code;
    var code_mobile = that.data.code_mobile;
    var code_code = that.data.code_code;
    if (mobile == "" || mobile.length < 11) {
      app.alert("请输入11位手机号码");
      return false;
    }
    if (code == "") {
      app.alert("请输入您收到的验证码");
      return false;
    }
    if (mobile != code_mobile) {
      app.alert("手机号码输入不正确");
      return false;
    }
    if (code != code_code) {
      app.alert("验证码输入不正确");
      return false;
    }
    app.operation('Vip/change_mobile', {
      loading: 0,
      mobile: mobile
    }, function(data) {
      if (data.ret == 1) {
        wx.showToast({
          title: data.msg,
          icon: 'success',
          duration: 2000
        })
        that.setData({
          second: 60,
          selected: false,
          selected1: true,
          mobile: '',
          code: '',
          code_mobile: "",
          code_code: "",
          f6: 0,
          showfuceng: 0,
          can_click: 1,
          bind_hide: true,
        })
        var obj = data.data;
        obj.expires_in = Date.parse(new Date()) / 1000 + data.data.expires_in - 200,
          wx.setStorageSync('user', obj); //存储openid
        var user = wx.getStorageSync('user');
        app.getData('Vip/index', that, {
          loading: 0
        }, function(data) {})
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
    });
  },

  /**
   * 绑定手机
   */
  bind_mobile_do: function(e) {
    var that = this;
    if (that.data.can_click == 0) {
      return false
    }
    var mobile = that.data.mobile;
    var code = that.data.code;
    var code_mobile = that.data.code_mobile;
    var code_code = that.data.code_code;
    if (mobile == "" || mobile.length < 11) {
      app.alert("请输入11位手机号码");
      return false;
    }
    if (code == "") {
      app.alert("请输入您收到的验证码");
      return false;
    }
    if (mobile != code_mobile) {
      app.alert("手机号码输入不正确");
      return false;
    }
    if (code != code_code) {
      app.alert("验证码输入不正确");
      return false;
    }
    app.operation('Vip/bind_mobile', {
      loading: 0,
      mobile: mobile
    }, function(data) {
      if (data.ret == 1) {
        wx.showToast({
          title: data.msg,
          icon: 'success',
          duration: 2000
        })
        that.setData({
          selected: false,
          selected1: true,
          mobile: '',
          code: '',
          code_mobile: "",
          code_code: "",
          f4: 0,
          showfuceng: 0,
          can_click: 1,
          bind_hide: true,
        })
        var obj = data.data;
        obj.expires_in = Date.parse(new Date()) / 1000 + data.data.expires_in - 200,
          wx.setStorageSync('user', obj); //存储openid
        var user = wx.getStorageSync('user');
        app.getData('Vip/index', that, {
          loading: 0
        }, function(data) {})
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
    });

  },

  /**
   *发送验证码
   */
  getcode: function(e) {
    var that = this;
    var type = e.currentTarget.dataset.type;
    if (that.data.can_click == 0) {
      return false
    }
    if (type == 1) {
      var mobile = that.data.mobile;
    } else if (type == 5) {
      var mobile = that.data.vip.mobile;
    } else if (type == 6) {
      var mobile = that.data.mobile;
    }
    if (mobile == "" || mobile.length < 11) {
      app.alert("请输入11位手机号码");
      return false;
    }
    that.setData({
      can_click: 0,
    })
    app.operation('Api/sendSmsCode', {
      loading: 0,
      mobile: mobile,
      type: type,
    }, function(data) {
      if (data.ret == 1) {
        wx.showToast({
          title: data.msg,
          icon: 'none',
          duration: 2000
        })
        that.setData({
          code_mobile: data.data.code_mobile,
          code_code: data.data.code_code,
          second: 60,
          can_click: 1,
          selected: true,
          selected1: false,
        })
        countdown(that);
      } else {
        app.alert(data.msg);
        that.setData({
          code_mobile: '',
          code_code: '',
          can_click: 1,
        })
      }
    });
  },
  getphone: function(e) {
    this.setData({
      selected: true,
      selected1: false,
    });
    countdown(this);
  },

  /**
   * 输入手机号
   */
  input_mobile: function(e) {
    var mobile = e.detail.value;
    this.setData({
      mobile: mobile,
    });
  },
  /**
   * 输入验证码
   */
  input_code: function(e) {
    var code = e.detail.value;
    this.setData({
      code: code,
    });
  },
  /**
   * 登录
   */
  login_do: function(e) {
    var that = this;
    var user = wx.getStorageSync('user');
    var data = user;
    // var data = {
    //   login_type: that.data.current,
    //   mobile: that.data.mobile,
    // };
    data.login_type = that.data.current;
    data.mobile = that.data.mobile;
    if (data.login_type == 1) {
      if (data.mobile == "") {
        app.alert('！请输入手机号码');
        return false;
      }
    }

    app.operation('Home/change_login', data, function(data) {
      if (data.ret == 1) {
        wx.showToast({
          title: data.msg,
        })
        var obj = data.data;
        obj.expires_in = Date.parse(new Date()) / 1000 + data.data.expires_in - 200,
          wx.setStorageSync('user', obj); //存储openid
        var user = wx.getStorageSync('user');
        app.getData('Vip/index', that, {
          loading: 1
        }, function(data) {})
      } else {
        app.alert(data.msg);
      }
    });
  },
  showf4: function() {
    this.setData({
      f4: 1,
      showfuceng: 1
    })
  },
  hidef4: function(e) {
    this.setData({
      f4: 0,
      showfuceng: 1
    })
  },
  showf5: function() {
    this.setData({
      f5: 1,
      showfuceng: 1
    })
  },
  hidef5: function(e) {
    this.setData({
      f5: 0,
      showfuceng: 0
    })
  },
  showf6: function() {
    this.setData({
      f6: 1,
      showfuceng: 1
    })
  },
  hidef6: function(e) {
    this.setData({
      f6: 0,
      showfuceng: 0
    })
  },

  hidef3: function(e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 0,
      showfuceng: 1
    })
  },
  press: function(e) {
    var cur = e.currentTarget.dataset.current;
    this.setData({
      current: cur,
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
   * 页面跳转
   */
  navigateTo: function(e) {
    var url = e.currentTarget.dataset.url;
    if (this.data.can_click == 0) {
      return false
    };
    this.setData({
      can_click: 0,
    });
    wx.navigateTo({
      url: url,
    })
  },

  /**
   * 授权
   */
  authUserInfo: function(e) {
    var that = this;
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
          app.globalData.userInfo = res.userInfo
          typeof cb == "function" && cb(app.globalData.userInfo)
          wx.setStorageSync('userInfo', res.userInfo);
        });
        that.setData({
          auth_hide: 0,
        })
      },
      fail: function(e) {}
    })
  },



  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    wx.hideShareMenu({})

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
    app.getData('Vip/index', this, {
      loading: 1
    }, function(data) {})
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    var that = this;
    this.setData({
      can_click: 1,
    });
    app.getData('Order/check_bind_mobile', this, {
      loading: 0
    }, function(data) {
      var bind_hide = true;
      if (!data.data.vip.mobile) {
        bind_hide = false;
      }
      that.setData({
        bind_hide: bind_hide,
      })
    });
    app.getData('Vip/index', this, {
      loading: 1
    }, function(data) {})
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

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})

function countdown(that) {
  var second = that.data.second;
  if (second <= 0) {
    that.setData({
      selected: false,
      selected1: true,
      second: 60,
    });
    return;
  }
  var time = setTimeout(function() {
    that.setData({
      second: second - 1
    });
    countdown(that);
  }, 1000)
}