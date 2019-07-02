// pages/exchangeinfo/exchangeinfo.js
var app = getApp();
Page({
  data: {
    showfuceng: 1,
    f1: 0,
    f2: 0,
    f3: 0,
    startlocal: ['红色/m码', '北京', '吉安', '厦门'],
    start: 0,
    collect: 0,
    current: 0,
    imgUrls: [
      '../../../images/swiper.png',
      '../../../images/swiper.png',
      '../../../images/swiper.png',
    ],
    hidefu: 0,  //浮层显示隐藏
    tabcurrent: 0,  //单选
    tabcurrenta: 0,  //单选
    money: 150,  //商品价格
    goodsname: '红色',  //商品名称
    color: '红色',  //选择的颜色
    size:'大号',
    goods: '../../../images/product-list.jpg',  //商品图片
    couponList:[
      {
        types:'满减券',
        money:20,
        allmoney:199,
        date:'2019.3.2-2.19.5.6',
        status:'已领取',
      },
      {
        types: '满减券',
        money: 20,
        allmoney: 199,
        date: '2019.3.2-2.19.5.6',
        status: '立即领券',
      },
      {
        types: '满减券',
        money: 20,
        allmoney: 199,
        date: '2019.3.2-2.19.5.6',
        status: '已领取',
      },
    ],
    sizeArr:['大号','中号','小号'],
    listsArr: [
      {
        img: '../../../images/product.jpg',
        color: '红色',
        goodsname: '红1色',
        price: 150,
      },
      {
        img: '../../../images/swiper.jpg',
        color: '红2色',
        goodsname: '红4色',
        price: 1520,
      },
      {
        img: '../../../images/product-list.jpg',
        color: '红3色',
        goodsname: '红色',
        price: 150,
      },
      {
        img: '../../../images/swiper.jpg',
        color: '红4色',
        goodsname: '红3色',
        price: 1530,
      }
    ],
    num: 1,  //加减数量
    nodes: [{
      name: 'div',
      attrs: {
        class: 'div_class',
        style: ''
      },
      children: [{
        type: 'text',
        text: 'Hello&nbsp;World!'
      }]
    }],
    discuss: [
      {
        starlist: ['../../../images/star-active.png', '../../../images/star-active.png', '../../../images/star-active.png', '../../../images/star-active.png', '../../../images/star-active.png'],
        date: "2017-05-06",
        name: "牛津鞋",
        detail: '红色红色红色红色红色红色红色红色红色',
        imglist: ['../../../images/swiper.png', '../../../images/swiper.png', '../../../images/swiper.png'],
      },
      {
        starlist: ['../../../images/star-active.png', '../../../images/star-active.png', '../../../images/star-active.png', '../../../images/star-active.png', '../../../images/star-active.png'],
        date: "2017-05-06",
        name: "牛津鞋",
        detail: '红色红色红色红色红色红色红色红色红色',
        imglist: ['../../../images/swiper.png', '../../../images/swiper.png', '../../../images/swiper.png'],
      },
    ],

    goods_id:18,


  },
  getcoupon: function (e) {
    var index = e.currentTarget.dataset.index;
    if (this.data.couponList[index].status=="立即领券"){
      this.data.couponList[index].status == '已领取';
    }
    this.setData({
      couponList:  this.data.couponList
    })
  },
  showf1: function (e) {
    var f1 = this.data.f1;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f1: 1,
      showfuceng: 0
    })
  },
  hidef1: function (e) {
    var f1 = this.data.f1;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f1: 0,
      showfuceng: 1
    })
  },
  showf2: function (e) {
    var f2 = this.data.f2;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f2: 1,
      f3: 0,
      showfuceng: 0
    })
  },
  hidef3: function (e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 0,
      showfuceng: 1
    })
  },
  showf3: function (e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 1,
      showfuceng: 0
    })
  },
  hidef2: function (e) {
    var f2 = this.data.f2;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f2: 0,
      showfuceng: 1
    })
  },
  startplace: function (e) {
    this.setData({
      start: e.detail.value
    })
  },
  press: function (e) {
    var cur = e.currentTarget.dataset.current;
    this.setData({
      current: cur,
    })
  },
  singelbtn: function (e) {
    var index = e.currentTarget.dataset.index;
    var listArr = this.data.listsArr;
    this.setData({
      tabcurrent: index,
      money: listArr[index].price,
      goods: listArr[index].img,
      money: listArr[index].price,
      goodsname: listArr[index].goodsname,
      color: listArr[index].color,
    })
  },
  singesizebtn: function (e) {
    var index = e.currentTarget.dataset.index;
    var sizeArr = this.data.sizeArr;
    var size = sizeArr[index];
    this.setData({
      tabcurrenta: index,
      size:size
    })
  },
  collectbtn: function (e) {
    var collect = this.data.collect;
    if (collect == 0) {
      this.setData({
        collect: 1,
      })
    } else {
      this.setData({
        collect: 0,
      })
    }
  },
  showfu: function (e) {
    var hidefu = this.data.hidefu;
    this.setData({
      hidefu: 1
    })
  },
  hidefu: function (e) {
    var hidefu = this.data.hidefu;
    this.setData({
      hidefu: 0
    })
  },
  add: function (e) {
    var num = this.data.num;
    this.setData({
      num: num + 1
    })
  },
  reduce: function (e) {
    var num = this.data.num;
    if (num == 1) {
      this.setData({
        num: 1
      })
    } else {
      this.setData({
        num: num - 1
      })
    }

  },
  previewImage: function (e) {
    var current = e.target.dataset.src;
    var index = e.target.dataset.index;
    var discuss = this.data.discuss;
    wx.previewImage({
      current: current,
      urls: this.data.discuss[index].imglist
    })
  },
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    var that = this;
    var data = {
      goods_id: that.data.goods_id,
    }
    var region = this.data.region;
    app.getData('Home/detail', that, data, function (data) {
      console.log(data);
      that.setData({
      })
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})