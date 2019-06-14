// pages/goodsList/goodsList.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    can_click: 1,
    more_tips: "正在加载中...",
    page: 1,
    sort: 'price',
    asc: 'desc',
    tabcurrent: 0,
    titarr: [
      [{
          tit: "价格",
          sort: 'price',
        },
        {
          tit: "销量",
          sort: 'sales',
        },
        {
          tit: "最新",
          sort: 'id',
        },
        {
          tit: "评价",
          sort: 'comment_score',
        },
      ]
    ],
    category_id: 0,
    keyword: "",

  },
  /**
 *商品 搜索
 */
  search_do: function (e) {
    console.log(e);
    if (this.data.can_click == 0) return false;
    var keyword = e.detail.value;
    this.setData({
      keyword: keyword,
    });
    getList(this, 1);
  },
  singelbtn: function(e) {
    var index = e.currentTarget.dataset.index;
    var sort = e.currentTarget.dataset.sort;
    if (this.data.can_click == 0) return false;
    this.setData({
      tabcurrent: index,
      sort: sort,
    });
    getList(this, 1);
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    console.log(options);
    this.setData(options);
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
  var more_tips = '正在加载中...';
  if(that.data.can_click==0)return false;
  that.setData({
    can_click:0,
    more_tips: more_tips,
  })
  var data = {
    loading: 0,
    category_id: that.data.category_id,
    keyword: that.data.keyword,
    sort: that.data.sort,
    asc: that.data.asc,
    pagenum: 10,
  }
  // console.log(data)
  app.getList('Home/goodsList', that, data, page, function(data) {
    console.log(data)
    if (page == 1 && data.data.length == 0) {
      more_tips = '暂无数据~';
    } else if (data.data.length < 10) {
      more_tips = '没有更多了';
    } else {
      more_tips = '加载更多';
    }
    that.setData({
      can_click:1,
      more_tips: more_tips,
    })
  })
}