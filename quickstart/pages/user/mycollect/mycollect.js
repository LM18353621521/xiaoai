// pages/home/myfollow/myfollow.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    can_cliak: 1,
    no_order: 0,
  },
  /**
   *删除
   */
  del: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var user = wx.getStorageSync('user');
    var list = that.data.list;
    var data = {
      vip_id: user.vip_id,
      goods_id: list[index].goods_id,
      type: 2,
    }
    app.operation("home/collect_do", data, function(data) {
      wx.showToast({
        title: '取消成功',
        icon: 'success',
        duration: 1500
      });
      list.splice(index, 1);
      that.setData({
        list: list,
      })
    });
  },

  /**
   * 去详情
   */
  detail_do: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    if (that.data.can_cliak == 0) {
      return false
    }
    that.setData({
      can_click: 0,
    })
    var list = that.data.list;
    var id = list[index].id;
    var goods_id = list[index].goods_id;
    app.operation('Vip/check_goods', {
      goods_id: goods_id
    }, function(res) {
      if (res.ret == 1) {
        that.setData({
          can_click: 1,
        })
        wx.navigateTo({
          url: '/pages/home/detail/detail?id=' + goods_id,
        })
      } else {
        wx.showModal({
          title: '提示',
          content: '商品已下架，是否删除该记录吗？',
          showCancel: true, //是否显示取消按钮
          cancelText: "否", //默认是“取消”
          cancelColor: 'skyblue', //取消文字的颜色
          confirmText: "是", //默认是“确定”
          success: function(res) {
            if (res.confirm) {
              app.operation('Vip/collect_del', {
                id: id
              }, function(res) {
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
        that.setData({
          can_click: 1,
        })
      }
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
    status: that.data.tabcurrent,
    keyword: '',
    sort: 'create_time',
    asc: 'desc',
    pagenum: 10,
  }
  app.getList('vip/mycollect', that, data, page, function(data) {
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