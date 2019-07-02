App({
  HOST: 'http://www.gzxi.com/',
  HOST: 'https://www.gzxi.cn/',
    globalData: {
      vip_id: 0,
      ol_index:0,
      cartList: [],
    },

  onLaunch: function(options) {
    if (typeof(options.query.share_id) != "undefined") {
      wx.setStorageSync("share_id", options.query.share_id);
    }
    var app = this;
    var user = wx.getStorageSync('user') || {};
    var userInfo = wx.getStorageSync('userInfo') || {};
    if ((!user.openid || Date.parse(new Date()) / 1000 > (user.expires_in - 600))&&user.login_vip_type!=3) {
      wx.login({
        success: function(res) {
          if (res.code) {
            var share_id = wx.getStorageSync('share_id');
            app.operation('Applet/getopenid', {
              code: res.code,
              share_id: share_id,
            }, function(data) {
              console.log(data);
              var obj=data.data;
              obj.expires_in=Date.parse(new Date()) / 1000 + data.data.expires_in - 200,
              //   openid: data.data.openid,
              //   vip_id: data.data.vip_id,
              //   wx_vip_id: data.data.wx_vip_id,
              //   s
              //   expires_in: Date.parse(new Date()) / 1000 + data.data.expires_in - 200,
              // };
              wx.setStorageSync('user', obj); //存储openid
              // app.getUserInfo();
            });
          } else {
            console.log('获取用户登录态失败！' + res.errMsg)
          }
        }
      });
    }
  },

  onShow: function(options) {
    var user = wx.getStorageSync('user');
  },
  onHide: function() {},

  onError: function(msg) {},

  //获取用户信息
  getUserInfo: function(cb) {
    var app = this;
    console.log(123);
    if (this.globalData.userInfo) {
      typeof cb == "function" && cb(this.globalData.userInfo)
    } else {
      //调用登录接口
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
        },
        fail: function(e) {
          console.log(e);
        }
      })
    }
  },

  // get请求方法
  fetchGet: function(url, data, callback) {
    wx.request({
      method: 'GET',
      url: url,
      data: data,
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function(res) {
        callback(null, res.data)
      },
      fail: function(e) {
        callback(e)
      }
    });
  },

  // post请求方法
  fetchPost: function(url, data, callback) {
    wx.request({
      method: 'POST',
      url: url,
      data: data,
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      dataType: 'json',
      success: function(res) {
        callback(null, res.data)
      },
      fail: function(e) {
        callback(e)
      }
    })
  },

  alert: function(msg, callback) {
    wx.showModal({
      content: msg,
      showCancel: false,
      success: function(res) {
        if (callback) {
          callback(res.data)
        }
      }
    });
  },

  fileupload: function(files, data, callback) {
    // console.log(data);
    var app = this;
    if (files.length > 0) {
      var file = files.shift();
      if (file.indexOf("/uploads") > -1) { //编辑时不改变链接
        var index = file.indexOf("/uploads");
        file = file.substring(index);
        data.push(file);
        app.fileupload(files, data, callback);
        return false;
      }
      wx.uploadFile({
        url: app.HOST + 'applet/Applet/saveimg', //仅为示例，非真实的接口地址
        filePath: file,
        name: 'photos',
        header: {
          'content-type': 'multipart/form-data'
        },

        complete: function(res) {
          var rdata = JSON.parse(res.data);
          if (rdata.ret == 1) {
            data.push(rdata.data);
            app.fileupload(files, data, callback);
          }
        },
      })
    } else {
      callback(data);
    }
  },




  //操作处理
  operation: function(url, data, callback) {
    console.log(url);
    var app = this;
    if (data.loading == 1) {
      wx.showLoading({
        title: '加载中',
        mask: true
      })
    }
    url = app.HOST + 'applet.php/' + url;
    var user = wx.getStorageSync('user');
    console.log(user);
    if (user.hasOwnProperty('openid')) {
      data.vip_id = user.vip_id;
      data.vip_ids = user.vip_ids;
    }
    app.fetchPost(url, data, (err, res) => {
      wx.hideLoading();
      if (callback) {
        callback(res)
      }
    });
  },

  //获取数据
  getData: function(url, that, data, callback) {
    var app = this;
    if (data.loading == 1) {
      wx.showLoading({
        title: '加载中',
        mask: true
      })
    }
    url = app.HOST + 'applet.php/' + url;
    var user = wx.getStorageSync('user');
    console.log(user);
    if (user.hasOwnProperty('openid')) {
      data.vip_id = user.vip_id;
      data.vip_ids = user.vip_ids;
    }
    app.fetchPost(url, data, (err, res) => {
      var data = res.data;
      that.setData(data);
      if (callback) {
        callback(res)
      }
      setTimeout(function() {
        wx.hideLoading();
      }, 300)

    });
  },

  //获取列表
  getList: function(url, that, data, page, callback) {
    var app = this;
    var more_tips = '正在加载中...'
    if (data.loading == 1) {
      wx.showLoading({
        title: '加载中',
        mask: true
      })
    }
    url = app.HOST + 'applet.php/' + url;
    if (page != 1) {
      page = that.data.page + 1;
    } else {
      page = 1;
      that.setData({
        more_tips: more_tips,
        list: [],
      });
    }
    var user = wx.getStorageSync('user');
    if (user.hasOwnProperty('openid')) {
      data.vip_id = user.vip_id;
      data.vip_ids = user.vip_ids;
    }
    data.page = page;
    app.fetchPost(url, data, (err, res) => {
      if (callback) {
        callback(res)
      }
      res = res.data;
      if (res.length) {
        var list = that.data.list;
        for (var i = 0; i < res.length; i++) {
          list.push(res[i])
        }
        that.setData({
          list: list,
          page: page
        });
      }
      setTimeout(function() {
        wx.hideLoading();
      }, 300)
    });
  },

  //表单提交
  formSubmit: function(url, that, data, callback, callback1) {
    var app = this;
    if (that.data.can_click == 0) {
      return false;
    }
    that.setData({
      can_click: 0
    });
    wx.showLoading({
      title: '处理中',
      mask: true
    })
    url = app.HOST + 'applet.php/' + url;
    var user = wx.getStorageSync('user');
    if (user.hasOwnProperty('openid')) {
      data.vip_id = user.vip_id;
      data.vip_ids = user.vip_ids;
    }
    app.fetchPost(url, data, (err, res) => {
      wx.hideLoading();
      if (res.ret == 1) {
        if (callback) {
          callback(res)
        }
      } else {
        app.alert(res.msg, function() {
          that.setData({
            can_click: 1,
          });
          if (callback1) {
            callback1(res)
          }
        });
        return false;
      }

    });
  },

  firstLoad: function(callback) {
    var app = this;
    var timer = setTimeout(function() {
      loadCheck(app, callback);
    }, 500);
  },

});

function loadCheck(app, callback) {
  var user = wx.getStorageSync('user');
  if (user.hasOwnProperty('openid')) {
    callback();
  } else {
    app.firstLoad();
  }
};

function check_click(that) {
  if (that.data.can_click == 0) {
    return false;
  } else {
    that.setData({
      can_click: 0,
    })
    return true;
  }
}