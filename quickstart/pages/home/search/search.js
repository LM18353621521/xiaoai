// pages/home/search/search.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    inputVal: '',
    inputvalue: 0,
    current: 0,
    searchres: 0,
    search: 1,
    keyword: "",
    keywordList: [],
    con: [{
        src: '../../../images/my-goods.jpg',
        tit: 'sm刑具玩成人情侣系列工具',
        money: '899.0',
      },
      {
        src: '../../../images/my-goods.jpg',
        tit: 'sm刑具玩成人情侣系列工具',
        money: '899.0',
      },
      {
        src: '../../../images/my-goods.jpg',
        tit: 'sm刑具玩成人情侣系列工具',
        money: '899.0',
      },
    ],
  },

  /**
   *商品 搜索
   */
  search_do: function(e) {
    var keyword = this.data.keyword;
    if (!keyword) {
      wx.showToast({
        title: '请输入关键词',
        icon: 'success',
        // image:"/images/edit.png",
        duration: 1500
      });
      return false;
    }
    wx.navigateTo({
      url: '/pages/home/goodsList/goodsList?keyword=' + keyword,
    })
  },
  /**
   * 点击关键词
   */
  keyword_btn: function(e) {
    var keyword = e.currentTarget.dataset.keyword;
    wx.navigateTo({
      url: '/pages/home/goodsList/goodsList?keyword=' + keyword,
    })
  },


  /**
   * 输入关键词
   */
  input_keyword: function(e) {
    var value = e.detail.value;

    var inputvalue = this.data.inputvalue;
    if (value == "") {
      inputvalue = 0;
    } else {
      inputvalue = 1;
    }
    this.setData({
      inputvalue: inputvalue,
      keyword: value
    })
  },
  clearInput: function(e) {
    this.setData({
      inputVal: '999'
    })
  },
  search: function() {
    var search = this.data.search;
    this.setData({
      search: 0,
    })
  },
  /**
   * 清空历史
   */
  clearHistory: function() {
    var that=this;
    app.operation('Home/clear_keyword', {}, function (res) {
      if (res.ret == 1) {
        wx.showToast({
          title: res.msg,
        })
        that.setData({
          can_click: 1,
          keywordList: [],
        });
      } else {
        app.alert(res.msg);
        that.setData({
          can_click: 0,
        })
      }
    });
  },
  press: function(e) {
    var cur = e.currentTarget.dataset.current;
    this.setData({
      current: cur,
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
  onReady: function() {},

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    app.getData('Home/get_search', this, {
      loading: 0
    }, function(data) {
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