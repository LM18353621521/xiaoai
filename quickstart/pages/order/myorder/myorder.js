var app = getApp();
Page({
  data: {
    can_cliak: 1,
    no_order: 0,
    tabcurrent: 0,
    titarr: [{
        tit: "全部"
      },
      {
        tit: "待付款"
      },
      {
        tit: "待发货"
      },
      {
        tit: "已发货"
      },
      {
        tit: "已完成"
      },
      {
        tit: "售后"
      },
    ],
    list: [],
    f4: 0,
    selected: false,
    selected1: true,
    bind_hide: true,
    //切换账号
    mobile: "",
    code: "",
    //验证码
    code_mobile: "",
    code_code: "",
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
        getList(that, 1);
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
    if (that.data.can_click == 0) {
      return false
    }
    var mobile = that.data.mobile;
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
      type: 1
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

  /**
   * 去详情
   */
  detail_do: function(e) {
    var that = this;
    if (that.data.can_cliak == 0) {
      return false
    }
    that.setData({
      can_click: 0,
    })
    var index = e.currentTarget.dataset.index;
    var tabcurrent = that.data.tabcurrent;
    var list = that.data.list;
    var order_id = list[index].id;
    app.globalData.ol_index = index;
    wx.navigateTo({
      url: '/pages/order/orderdetail/orderdetail?id=' + order_id + '&index=' + index + '&tabcurrent=' + tabcurrent,
    })
  },
  /**
   * 去评价
   */
  comment_do: function(e) {
    var that = this;
    if (that.data.can_cliak == 0) {
      return false
    }
    that.setData({
      can_click: 0,
    })
    var index = e.currentTarget.dataset.index;
    var list = that.data.list;
    var order_id = list[index].id;
    app.globalData.ol_index=index;
    wx.navigateTo({
      url: '/pages/order/discuss/discuss?id=' + order_id + '&index=' + index+'&source=1',
    })
  },
  /**
   * 查看物流
   */
  express_do: function(e) {
    var that = this;
    if (that.data.can_cliak == 0) {
      return false
    }
    that.setData({
      can_click: 0,
    })
    var index = e.currentTarget.dataset.index;
    var list = that.data.list;
    var order_id = list[index].id;
    wx.navigateTo({
      url: '/pages/order/wuliu/wuliu?id=' + order_id + '&index=' + index,
    })
  },
  /**
   * 取消订单
   */
  cancel_do: function(e) {
    var that = this;
    if (that.data.can_cliak == 0) {
      return false
    }
    wx.showModal({
      title: '提示',
      content: '确定取消该订单吗？',
      showCancel: true, //是否显示取消按钮
      cancelText: "否", //默认是“取消”
      cancelColor: 'skyblue', //取消文字的颜色
      confirmText: "是", //默认是“确定”
      success: function(res) {
        if (res.confirm) {
          that.setData({
            can_click: 0,
          })
          var index = e.currentTarget.dataset.index;
          var tabcurrent = that.data.tabcurrent;
          var list = that.data.list;
          var order_id = list[index].id;
          var data = {
            order_id: order_id,
          }
          app.operation('Order/order_cancel', data, function(res) {
            if (res.ret == 1) {
              if (tabcurrent == 0) {
                list[index].status = -1;
              } else {
                list.splice(index, 1);
              }
              wx.showToast({
                title: res.msg,
              })
              that.setData({
                list: list,
                can_click: 1,
              })
            } else {
              app.alert(res.msg);
              that.setData({
                can_click: 1,
              })
            }
          })
        }
      }
    })

  },
  /**
   * 删除订单
   */
  del_do: function(e) {
    var that = this;
    if (that.data.can_cliak == 0) {
      return false
    }
    wx.showModal({
      title: '提示',
      content: '确定删除该订单吗？',
      showCancel: true, //是否显示取消按钮
      cancelText: "否", //默认是“取消”
      cancelColor: 'skyblue', //取消文字的颜色
      confirmText: "是", //默认是“确定”
      success: function(res) {
        if (res.confirm) {
          that.setData({
            can_click: 0,
          })
          var index = e.currentTarget.dataset.index;
          var tabcurrent = that.data.tabcurrent;
          var list = that.data.list;
          var order_id = list[index].id;
          var data = {
            order_id: order_id,
          }
          app.operation('Order/order_del', data, function(res) {
            if (res.ret == 1) {
              list.splice(index, 1);
              wx.showToast({
                title: res.msg,
              })
              that.setData({
                list: list,
                can_click: 1,
              })
            } else {
              app.alert(res.msg);
              that.setData({
                can_click: 1,
              })
            }
          })
        }
      }
    })

  },

  /**
   * 确认收货
   */
  confirm_do: function(e) {
    var that = this;
    if (that.data.can_cliak == 0) {
      return false
    }
    wx.showModal({
      title: '提示',
      content: '确定已经收货了吗？',
      showCancel: true, //是否显示取消按钮
      cancelText: "否", //默认是“取消”
      cancelColor: 'skyblue', //取消文字的颜色
      confirmText: "是", //默认是“确定”
      success: function(res) {
        if (res.confirm) {
          that.setData({
            can_click: 0,
          })
          var index = e.currentTarget.dataset.index;
          var tabcurrent = that.data.tabcurrent;
          var list = that.data.list;
          var order_id = list[index].id;
          var data = {
            order_id: order_id,
          }
          app.operation('Order/order_confirm', data, function(res) {
            if (res.ret == 1) {
              if (tabcurrent == 0) {
                list[index].status = 3;
              } else {
                list.splice(index, 1);
              }
              wx.showToast({
                title: res.msg,
              })
              that.setData({
                list: list,
                can_click: 1,
              })
            } else {
              app.alert(res.msg);
              that.setData({
                can_click: 1,
              })
            }
          })
        }
      }
    })
  },

  /**
   * 申请退款
   */
  refund_do: function(e) {
    var that = this;
    if (that.data.can_cliak == 0) {
      return false
    }
    wx.showModal({
      title: '提示',
      content: '确定申请退款吗？',
      showCancel: true, //是否显示取消按钮
      cancelText: "否", //默认是“取消”
      cancelColor: 'skyblue', //取消文字的颜色
      confirmText: "是", //默认是“确定”
      success: function(res) {
        if (res.confirm) {
          that.setData({
            can_click: 0,
          })
          var index = e.currentTarget.dataset.index;
          var tabcurrent = that.data.tabcurrent;
          var list = that.data.list;
          var order_id = list[index].id;
          var data = {
            order_id: order_id,
          }
          app.operation('Order/order_refund', data, function(res) {
            if (res.ret == 1) {
              if (tabcurrent == 0) {
                list[index].status = 3;
              } else {
                list.splice(index, 1);
              }
              wx.showToast({
                title: res.msg,
              })
              that.setData({
                list: list,
                can_click: 1,
              })
            } else {
              app.alert(res.msg);
              that.setData({
                can_click: 1,
              })
            }
          })
        }
      }
    })
  },

  /**
   * 切换状态
   */
  singelbtn: function(e) {
    var index = e.currentTarget.dataset.index;
    this.setData({
      tabcurrent: index
    });
    getList(this, 1);
  },
  onLoad: function(options) {
    wx.hideShareMenu({});
    var tabcurrent = options.type;
    this.setData({
      tabcurrent: tabcurrent,
    })

  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {
    var that = this;
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
    })
    getList(this, 1);
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    this.setData({
      can_click: 1,
    })
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
    status: that.data.tabcurrent,
    keyword: '',
    sort: 'create_time',
    asc: 'desc',
    pagenum: 10,
  }
  app.getList('Order/myorder', that, data, page, function(data) {
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

function countdown(that) {
  that.setData({
    selected: true,
    selected1: false,
  });
  var second = that.data.second;
  if (second == 0) {
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