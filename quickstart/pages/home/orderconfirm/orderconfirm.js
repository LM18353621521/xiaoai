var app = getApp();
Page({
  data: {
    can_click: 1,
    startlocal: ['快递', '北京', '吉安', '厦门'],
    start: 0,
    showfuceng: 1,
    tab: 0,
    index: 0,
    radioa: 0,
    radiob: 0,
    f1: 0,
    f2: 0,
    f3: 0,
    f4: 0,
    f5: 0,
    f6: 0,
    radio: "../../../images/no.png",
    multiIndex: [0, 0, 0],
    sure: 1,
    //添加地址
    region: [
      [],
      [],
      []
    ],
    address_id: 0,
    addressInfo: [],
    sureIndex: [0, 0, 0],
    selectIndex: [0, 0, 0],
    province: [],
    is_default: 0,
    province_code: '',
    city_code: '',
    district_code: '',

    //购买信息
    sel_address: [],
    cartList: [],
    action: '',
    goods_id: 0,
    item_id: 0,
    buy_num: 0,
    cart_ids: 0,
    express_fee: 0, //运费
    pay_money: '0.00', //实付

    //优惠券
    coupon_sel_name: "",
    coupon_id: 0,
    coupon_money: '0.00',

    //支付方式
    pay_index: 0,
    pay_name: '微信支付',
    pay_type: "wxpay",
    payList: [{
      pay_type: "wxpay",
        icon: "../../../images/wechat.png",
        name: "微信支付"
      },
      {
        pay_type: "income",
        icon: "",
        name: "佣金支付"
      }
    ]
  },
  /**
   * 提交订单
   */
  order_submit: function(e) {
    console.log(e);
    var that = this;
    var data = {
      action: that.data.action,
      goods_id: that.data.goods_id,
      item_id: that.data.item_id,
      buy_num: that.data.buy_num,
      cart_ids: that.data.cart_ids,
      address_id: that.data.sel_address.id,
      coupon_id: that.data.coupon_id,
      pay_type: that.data.pay_type,
      remark: e.detail.value.remark,
    }
    console.log(data);
    if (!data.address_id) {
      app.alert("请选择收货地址");
      return false;
    }
    if (!data.pay_type) {
      app.alert("请选择支付方式");
      return false;
    }
    app.operation('Order/orderadd', data, function(data) {
      console.log(data);
      var order = data.data;
      if (data.ret == 1) {
        if (order.pay_type == "income") {
          wx.showToast({
            title: data.msg,
            success: function(e) {
              setTimeout(function() {
                wx.redirectTo({
                  url: '/pages/order/myorder/myorder',
                })
              }, 2000)
            }
          })

        } else {
          that.order_pay(order);
        }
      } else {
        app.alert(data.msg);
        return false;
      }
    });
  },
  /**
   * 订单支付
   */
  order_pay: function(data) {
    console.log(data)
    var that = this;
    var user = wx.getStorageSync('user');
    var id = data.id;
    app.operation("Order/order_pay", {
      order_id: id
    }, function(data) {
      var payargs = data.data;
      console.log(data);
      wx.requestPayment({
        timeStamp: payargs.timeStamp,
        nonceStr: payargs.nonceStr,
        package: payargs.package,
        signType: payargs.signType,
        paySign: payargs.paySign,
        success: function(res) {
          wx.showToast({
            title: '订单支付成功！',
            duration: 2000,
            success: function(e) {
              setTimeout(function(e) {
                wx.redirectTo({
                  url: '/pages/order/myorder/myorder?tabcurrent=0',
                })
              }, 2000)
            }
          });
        },
        fail: function(res) {
          wx.showToast({
            title: "！支付失败，请稍后重试",
            icon: "none",
            duration: 2500,
            success: function(e) {
              setTimeout(function(e) {
                wx.redirectTo({
                  url: '/pages/order/myorder/myorder?tabcurrent=0',
                })
              }, 2500)
            }
          });
        }
      })
    })
  },
  /**
   * 选择收货地址
   */
  sel_address: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var addressList = this.data.addressList;
    var address_id = addressList[index].id;
    console.log(addressList[index])
    that.setData({
      f5: 0,
      sel_address: addressList[index],
    })
    var data = {
      id: address_id,
    }
    app.operation('Home/count_repress_fee', data, function(data) {
      console.log(data);
      if (data.ret == 1) {
        that.setData({
          express_fee: data.data,
        })
        count_pay_money(that);
      } else {
        app.alert(data.msg);
        return false;
      }
    })
  },


  /**
   * 保存地址
   */
  address_save: function(e) {
    var that = this;
    console.log(e);
    var id = that.data.address_id;
    var data = {
      id: that.data.address_id,
      linkman: e.detail.value.linkman,
      linktel: e.detail.value.linktel,
      province_code: that.data.province_code,
      city_code: that.data.city_code,
      district_code: that.data.district_code,
      address: e.detail.value.address,
      is_default: that.data.is_default,
    }
    console.log(data);
    if (data.linkman == "") {
      app.alert('请输入收货人');
      return false;
    }
    if (data.linktel == "") {
      app.alert('请输入手机号码');
      return false;
    }
    if (data.province_code == 0 || data.city_code == 0 || data.district_code == 0) {
      app.alert('请选择收货地址');
      return false;
    }
    if (data.address == "") {
      app.alert('请输入详细地址');
      return false;
    }
    app.operation('Address/addEditAddress', data, function(data) {
      if (data.status == 1) {
        wx.showToast({
          title: data.msg,
          duration: 1000,
          success: function(res) {}
        })
        that.setData({
          f4: 0,
          f5: 1,
          addressList: data.addressList,
        })
      } else {
        app.alert(data.msg);
        return false;
      }
    })


  },

  /**
   * 确认地址
   */
  bindRegionSure: function(e) {
    var sureIndex = e.detail.value;
    var region = this.data.region;
    console.log(region);
    console.log(sureIndex);
    this.setData({
      province_code: region[0][sureIndex[0]]['code'],
      city_code: region[1][sureIndex[1]]['code'],
      district_code: region[2][sureIndex[2]]['code'],
    });
    console.log(region[0][sureIndex[0]]['code']);
  },

  /**
   * 设置默认
   */
  set_default: function(e) {
    var is_default = this.data.is_default;
    if (is_default == 0) {
      this.setData({
        is_default: 1
      })
    } else {
      this.setData({
        is_default: 0
      })
    }
  },

  bindMultiPickerColumnChange: function(e) {
    var that = this;
    console.log('修改的列为', e.detail.column, '，值为', e.detail.value);
    var region = this.data.region;
    var column = e.detail.column;
    var value = e.detail.value;
    console.log(region);
    var sureIndex = this.data.sureIndex;
    switch (column) {
      case 0:
        console.log(region[column][value]);
        var data = {
          parent_id: region[0][value]['id'],
        };
        app.getData('Address/getNextRegion', that, data, function(data) {
          region[1] = data.data.list;
          region[2] = data.data.list_next;
          sureIndex[1] = 0;
          sureIndex[2] = 0;
          that.setData({
            region: region,
            sureIndex: sureIndex
          })
        });
        break;
      case 1:
        var data = {
          parent_id: region[1][value]['id'],
        };
        app.getData('Address/getNextRegion', that, data, function(data) {
          region[2] = data.data.list;
          sureIndex[2] = 0;
          that.setData({
            region: region,
            sureIndex: sureIndex
          })
        });
        console.log(this.data.sureIndex)
        break;
    }
  },


  bindMultiPickerChange: function(e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      multiIndex: e.detail.value
    })
  },

  startplace: function(e) {
    this.setData({
      start: e.detail.value
    })
  },
  /**
   * 选择支付方式
   */
  sel_pay_type: function(e) {
    var index = e.currentTarget.dataset.index;
    var payList = this.data.payList;
    this.setData({
      pay_index: index,
      pay_type: payList[index].pay_type,
      pay_name: payList[index].name,
    })
  },
  /**
   * 选择优惠券
   */
  coupon_sel: function(e) {
    var radiob = this.data.radiob;
    var index = e.currentTarget.dataset.index;
    var couponList = this.data.couponList;
    var coupon_sel_name = this.data.couponList[index].name;
    var coupon_sel_id = this.data.couponList[index].id;
    var coupon_money = this.data.couponList[index].money;
    this.setData({
      radiob: index,
      coupon_sel_name: coupon_sel_name,
      coupon_id: coupon_sel_id,
      coupon_money: coupon_money,
    });
    count_pay_money(this);
  },
  showf1: function(e) {
    var f1 = this.data.f1;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f1: 1,
      showfuceng: 0
    })
  },
  hidef1: function(e) {
    var f1 = this.data.f1;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f1: 0,
      showfuceng: 1
    })
  },
  showf2: function(e) {
    var f2 = this.data.f2;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f2: 1,
      showfuceng: 0
    })
  },
  hidef2: function(e) {
    var f2 = this.data.f2;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f2: 0,
      showfuceng: 1
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
  hidef3: function(e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 0,
      showfuceng: 1
    })
  },
  hidef31: function(e) {
    var f3 = this.data.f3;
    var f4 = this.data.f4;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 0,
      f4: 1,
      showfuceng: 0
    })
  },
  showf4: function(e) {
    console.log(e)
    var showfuceng = this.data.showfuceng;
    this.setData({
      f4: 1,
      f5: 0,
      showfuceng: 0
    })
  },
  hidef4: function(e) {
    var f4 = this.data.f4;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f4: 0,
      showfuceng: 1
    })
  },
  hidef41: function(e) {
    var f5 = this.data.f5;
    var f4 = this.data.f4;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f5: 1,
      f4: 0,
      showfuceng: 0
    })
  },
  showf5: function(e) {
    var f5 = this.data.f5;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f5: 1,
      showfuceng: 0
    })
  },
  hidef5: function(e) {
    var f5 = this.data.f5;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f5: 0,
      showfuceng: 1
    })
  },
  showf6: function(e) {
    var f6 = this.data.f6;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f6: 1,
      showfuceng: 0
    })
  },
  hidef6: function(e) {
    var f6 = this.data.f6;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f6: 0,
      showfuceng: 1
    })
  },
  singlebtn: function(e) {
    var tab = this.data.tab;
    var tabicon = e.currentTarget.dataset.tab;
    this.setData({
      tab: tabicon
    })
  },
  bindPickerChange: function(e) {
    this.setData({
      index: e.detail.value
    })
  },
  seleall: function(e) {
    var radio = this.data.radio;
    if (radio == "../../../images/no.png") {
      this.setData({
        radio: "../../../images/yes.png"
      })
    } else {
      this.setData({
        radio: "../../../images/no.png"
      })
    }
  },

  onLoad: function(options) {
    console.log(options);
    // options.action = 'buy_cart';
    // options.goods_id = 18;
    // options.item_id = 381;
    // options.buy_num = 2;
    this.setData(options);
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {
    var that = this;
    var data = {
      action: that.data.action,
      goods_id: that.data.goods_id,
      item_id: that.data.item_id,
      buy_num: that.data.buy_num,
      cart_ids: that.data.cart_ids,
    }
    app.getData('Home/orderconfirm', that, data, function(data) {
      console.log(data);
      var total_count = data.data.total_count;
      console.log(total_count);
      that.setData({
        express_fee: total_count.express_fee,
      })
      count_pay_money(that);
    });
    var data1 = {
      address_id: that.data.address_id,
    }
    var region = this.data.region;
    app.getData('Address/addressInfo', that, data1, function(data) {
      console.log(data);
      region[0] = data.data.province;
      region[1] = data.data.city;
      region[2] = data.data.district;
      var is_default = 0;
      if (data.data.addressInfo) {
        is_default = data.data.addressInfo.is_default;
      }
      that.setData({
        region: region,
        province_code: region[0][0]['code'],
        city_code: region[1][0]['code'],
        district_code: region[2][0]['code'],
        is_default: is_default,
      })
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    var that = this;
    that.setData({
      can_click: 1,
    });
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

var count_pay_money = function(that) {
  var goods_price = parseFloat(that.data.total_count.goods_price);
  var express_fee = parseFloat(that.data.express_fee);
  var coupon_money = parseFloat(that.data.coupon_money);
  var pay_money = goods_price + express_fee - coupon_money;
  that.setData({
    pay_money: pay_money.toFixed(2),
  })
}