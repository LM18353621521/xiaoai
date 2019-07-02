// pages/renwu/renwu.js
var app = getApp();
Page({
  data: {
    can_click: 1,
    edit: '编辑',
    hidedel: false,
    select_all: 0,
    jifen: 0,
    jishu: 0, //控制是不是都选中了
    cartList: [],
    total_count: [],
  },
  /**
   * 立即付款
   */
  cart_submit: function(e) {
    var that = this;
    if (this.data.can_click == 0) {
      return false
    }
    var cartList = this.data.cartList;
    var select_all = this.data.select_all == 1 ? 0 : 1;
    var cart_ids = [];
    for (var i = 0; i < cartList.length; i++) {
      if (cartList[i].selected == 1) {
        cart_ids.push(cartList[i].id);
      }
    }

    if (cart_ids.length == 0) {
      app.alert('！请选择购买商品');
      that.setData({
        can_click: 1,
      })
      return false;
    }
    that.setData({
      can_click: 0,
    })
    console.log(cart_ids);
    var user = wx.getStorageSync('user');
    var data = {
      vip_id: user.vip_id,
      goods_id: 0,
      item_id: 0,
      buy_num: 0,
    }
    wx.navigateTo({
      url: '/pages/home/orderconfirm/orderconfirm?action=buy_cart',
    });
  },

  /**
   * 购物车加+
   */
  add: function(e) {
    var that = this;
    if (this.data.can_click == 0) {
      return false
    }
    var cartList = this.data.cartList;
    var index = e.currentTarget.dataset.index;
    that.setData({
      can_click: 0,
    })
    var data = {
      id: cartList[index].id,
      number: 1,
      type: 1,
    };
    app.operation('Cart/ajax_cart_update', data, function(data) {
      console.log(data);
      var order = data.data;
      if (data.ret == 1) {
        cartList[index].number++;
        that.setData({
          cartList: cartList,
          can_click: 1,
        })
        countCartPrice(that, cartList);
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
    });
  },
  /**
   *购物车 减
   */
  reduce: function(e) {
    var that = this;
    if (this.data.can_click == 0) {
      return false
    }
    var cartList = this.data.cartList;
    var index = e.currentTarget.dataset.index;
    var number = cartList[index].number;
    if (number == 1) {
      return false
    }
    that.setData({
      can_click: 0,
    })
    var data = {
      id: cartList[index].id,
      number: 1,
      type: 2,
    };
    app.operation('Cart/ajax_cart_update', data, function(data) {
      console.log(data);
      var order = data.data;
      if (data.ret == 1) {
        cartList[index].number--;
        that.setData({
          cartList: cartList,
          can_click: 1,
        })
        countCartPrice(that, cartList);
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
    });
  },
  sele: function(e) {
    var index = e.currentTarget.dataset.index;
    var that = this;
    if (this.data.can_click == 0) {
      return false
    }
    that.setData({
      can_click: 0,
    })
    var cartList = this.data.cartList;
    var selected = cartList[index].selected == 1 ? 0 : 1;
    var cart_ids = [];
    cartList[index].selected = selected;
    cart_ids.push(cartList[index].id);
    var data = {
      selected: selected,
      cart_ids: cart_ids,
    }
    app.operation('Cart/cart_select', data, function(data) {
      if (data.ret == 1) {
        that.setData({
          cartList: cartList,
          can_click: 1,
        })
        countCartPrice(that, cartList);
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
    });
  },
  tapbtn: function(e) {
    var edit = this.data.edit;
    if (edit == "编辑") {
      this.setData({
        edit: "完成",
        hideall: false,
        hidedel: true,
      })
    } else {
      this.setData({
        edit: "编辑",
        hideall: true,
        hidedel: false,
      })
    }
  },
  seleall: function(e) {
    var that = this;
    if (this.data.can_click == 0) {
      return false
    }
    that.setData({
      can_click: 0,
    })
    var cartList = this.data.cartList;
    var select_all = this.data.select_all == 1 ? 0 : 1;
    var cart_ids = [];
    for (var i = 0; i < cartList.length; i++) {
      cartList[i].selected = select_all;
      cart_ids.push(cartList[i].id);
    }
    var data = {
      selected: select_all,
      cart_ids: cart_ids,
    }
    app.operation('Cart/cart_select', data, function(data) {
      if (data.ret == 1) {
        that.setData({
          cartList: cartList,
          can_click: 1,
        })
        countCartPrice(that, cartList);
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
    });
  },
  onLoad: function(options) {
    wx.hideShareMenu({})
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {},

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    var that = this;
    that.setData({
      can_click: 1,
    });
    var that = this;
    var data = {};
    app.getData('Cart/cart', that, data, function(data) {
      console.log(data);
      countCartPrice(that, data.data.cartList);
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
  onShareAppMessage: function() {

  }
})
var countCartPrice = function(that, cartList) {
  var total_count = {
    total_price: 0,
    total_num: 0,
  };
  var select_all = 1;
  for (var i = 0; i < cartList.length; i++) {
    if (cartList[i].selected == 1) {
      total_count.total_price += parseFloat(cartList[i].price) * parseFloat(cartList[i].number);
      total_count.total_num += parseFloat(cartList[i].number);
    } else {
      select_all = 0;
    }
  }
  total_count.total_price = total_count.total_price.toFixed(2);
  that.setData({
    total_count: total_count,
    select_all: select_all,
  })

}