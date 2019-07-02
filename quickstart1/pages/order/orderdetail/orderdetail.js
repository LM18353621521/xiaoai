// pages/home/ordersdetail/ordersdetail.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    can_click: 1,
    countDownDay: 0,
    countDownHour: 0,
    countDownMinute: 0,
    countDownSecond: 0,
    goodslist: [{
        img: '../../../images/product-list.jpg',
        name: 'VR飞机杯真实体验真人3D环VR飞机杯真实体验真人3D环VR飞机杯真实体验真人3D环',
        color: '39吗给色',
        size: '大码',
        price: 25,
        num: 5,
      },
      {
        img: '../../../images/product-list.jpg',
        name: 'VR飞机杯真实体验真人3D环VR飞机杯真实体验真人3D环VR飞机杯真实体验真人3D环',
        color: '39吗给色',
        size: '大码',
        price: 25,
        num: 5,
      },
    ],
    id: 35,
    index: 0,
    tabcurrent: 0,
  },


  /**
   * 订单支付
   */
  pay_do: function(data) {
    var that = this;
    console.log(data)
    if (that.data.can_cliak == 0) {
      return false
    }
    that.setData({
      can_click: 0,
    })

    var index = that.data.index;
    var tabcurrent = that.data.tabcurrent;
    var user = wx.getStorageSync('user');
    var id = that.data.id;
    app.operation("Order/order_pay", {
      order_id: id
    }, function(data) {
      var payargs = data.data;
      var order = data.order;
      console.log(data);
      wx.requestPayment({
        timeStamp: payargs.timeStamp,
        nonceStr: payargs.nonceStr,
        package: payargs.package,
        signType: payargs.signType,
        paySign: payargs.paySign,
        success: function(res) {
          var pages = getCurrentPages();
          var Page = pages[pages.length - 1]; //当前页
          var prevPage = pages[pages.length - 2]; //上一个页面
          var list = prevPage.data.list //取上页data里的数据也可以修改
          if (tabcurrent == 0) {
            list[index].status = 1;
          } else {
            list.splice(index, 1);
          }
          that.setData({
            can_click: 1,
            order: order,
          })
          wx.showToast({
            title: '订单支付成功！',
            duration: 2500,
            success: function(e) {}
          });
        },
        fail: function(res) {
          that.setData({
            can_click: 1,
          })
          wx.showToast({
            title: "！支付失败，请稍后重试",
            icon: "none",
            duration: 2000,
            success: function(e) {}
          });
        }
      })
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
    var index = that.data.index;
    var order_id = that.data.id;
    wx.navigateTo({
      url: '/pages/order/discuss/discuss?id=' + order_id + '&index=' + index +'&source=2',
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
    var index = that.data.index;
    var order_id = that.data.id;
    wx.navigateTo({
      url: '/pages/order/orderdetail/orderdetail?id=' + order_id + '&index=' + index,
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
          var index = that.data.index;
          var tabcurrent = that.data.tabcurrent;
          var order_id = that.data.id;
          var data = {
            order_id: order_id,
          }
          app.operation('Order/order_cancel', data, function(res) {
            if (res.ret == 1) {
              var pages = getCurrentPages();
              var Page = pages[pages.length - 1]; //当前页
              var prevPage = pages[pages.length - 2]; //上一个页面
              var list = prevPage.data.list //取上页data里的数据也可以修改
              prevPage.setData({
                list: list
              });
              if (tabcurrent == 0) {
                list[index].status = -1;
              } else {
                list.splice(index, 1);
              }
              wx.showToast({
                title: res.msg,
              })
              that.setData({
                order: res.data,
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
          var order = that.data.order;
          var index = that.data.index;
          var order_id = that.data.id;
          var data = {
            order_id: order_id,
          }
          app.operation('Order/order_del', data, function(res) {
            if (res.ret == 1) {
              var pages = getCurrentPages();
              var Page = pages[pages.length - 1]; //当前页
              var prevPage = pages[pages.length - 2]; //上一个页面
              var list = prevPage.data.list //取上页data里的数据也可以修改
              list.splice(index, 1);
              prevPage.setData({
                list: list
              });
              wx.showToast({
                title: res.msg,
              })
              wx.navigateBack({
                delta: 1,
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
          var index = that.data.index;
          var tabcurrent = that.data.tabcurrent;
          var order_id = that.data.id;
          var data = {
            order_id: order_id,
          }
          app.operation('Order/order_confirm', data, function(res) {
            if (res.ret == 1) {
              var pages = getCurrentPages();
              var Page = pages[pages.length - 1]; //当前页
              var prevPage = pages[pages.length - 2]; //上一个页面
              var list = prevPage.data.list //取上页data里的数据也可以修改
              if (tabcurrent == 0) {
                list[index].status = 3;
              } else {
                list.splice(index, 1);
              }
              prevPage.setData({
                list: list
              });
              wx.showToast({
                title: res.msg,
              })
              that.setData({
                order: res.data,
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
  refund_do: function (e) {
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
      success: function (res) {
        if (res.confirm) {
          that.setData({
            can_click: 0,
          })
          var index = that.data.index;
          var tabcurrent = that.data.tabcurrent;
          var order_id = that.data.id;
          var data = {
            order_id: order_id,
          }
          app.operation('Order/order_refund', data, function (res) {
            if (res.ret == 1) {
              var pages = getCurrentPages();
              var Page = pages[pages.length - 1]; //当前页
              var prevPage = pages[pages.length - 2]; //上一个页面
              var list = prevPage.data.list //取上页data里的数据也可以修改
              if (tabcurrent == 0) {
                list[index].status = 3;
              } else {
                list.splice(index, 1);
              }
              prevPage.setData({
                list: list
              });
              wx.showToast({
                title: res.msg,
              })
              that.setData({
                order: res.data,
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
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    wx.hideShareMenu({});
    console.log(options);
    this.setData(options);


  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {
    var that = this;
    var data = {
      loading: 1,
      order_id: this.data.id,
    };
    app.getData('Order/orderdetail', this, data, function(data) {
      console.log(data)
      var order = data.data.order;
      if (order.status == 0) {
        count_down(that, order.cancel_subtime);
      }
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    this.setData({
      can_click:1,
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

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {}
})
var count_down = function(that, totalSecond) {
  var totalSecond = totalSecond;
  var interval = setInterval(function() {
    var second = totalSecond; // 相差的总秒数
    // 天数位  
    var day = Math.floor(second / 3600 / 24);
    var dayStr = day.toString();
    if (dayStr.length == 1) dayStr = '0' + dayStr;
    // 小时位  
    var hr = Math.floor((second - day * 3600 * 24) / 3600);
    var hrStr = hr.toString();
    if (hrStr.length == 1) hrStr = '0' + hrStr;
    // 分钟位  
    var min = Math.floor((second - day * 3600 * 24 - hr * 3600) / 60);
    var minStr = min.toString();
    if (minStr.length == 1) minStr = '0' + minStr;
    // 秒位  
    var sec = second - day * 3600 * 24 - hr * 3600 - min * 60;
    var secStr = sec.toString();
    if (secStr.length == 1) secStr = '0' + secStr;
    this.setData({
      countDownDay: dayStr,
      countDownHour: hrStr,
      countDownMinute: minStr,
      countDownSecond: secStr,
    });
    totalSecond--;
    if (totalSecond < 0) {
      clearInterval(interval);
      wx.showToast({
        title: '订单已过期',
      });
      that.setData({
        countDownDay: '00',
        countDownHour: '00',
        countDownMinute: '00',
        countDownSecond: '00',
      });
    }
  }.bind(that), 1000);
}