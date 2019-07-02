// pages/user/applyaddr/applyaddr.js
var app = getApp();
Page({
  data: {
    can_click: 1,
    address_id: 0,
    addressInfo: [],
    second: 60,
    selected: false,
    selected1: true,
    // region: ['广东省', '广州市', '海珠区'],
    customItem: '全部',
    multiArray: [
      ['无脊柱动物', '脊柱1动物'],
      ['扁性动物', '线形动物', '环节动物', '软体动物', '节肢动物'],
      ['猪肉绦虫', '吸血虫']
    ],
    region: [
      [],
      [],
      []
    ],
    sureIndex: [0, 0, 0],
    selectIndex: [0, 0, 0],
    province: [],
    is_default: 0,
    province_code: '',
    city_code: '',
    district_code: '',
  },

  /**
   * 保存地址
   */
  address_save: function(e) {
    var that = this;
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
        var pages = getCurrentPages();
        var Page = pages[pages.length - 1]; //当前页
        var prevPage = pages[pages.length - 2]; //上一个页面
        var addressList = prevPage.data.addressList //取上页data里的数据也可以修改
        addressList = data.addressList
        wx.showToast({
          title: data.msg,
          duration: 1000,
          success: function(res) {
            prevPage.setData({
              addressList: addressList
            }); //设置数据
            wx.navigateBack({
              delta: 1,
            });
          }
        })
        return false;
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
    this.setData({
      province_code: region[0][sureIndex[0]]['code'],
      city_code: region[1][sureIndex[1]]['code'],
      district_code: region[2][sureIndex[2]]['code'],
    });
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
    var region = this.data.region;
    var column = e.detail.column;
    var value = e.detail.value;
    var sureIndex = this.data.sureIndex;
    switch (column) {
      case 0:
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
        break;
    }
  },
  bindRegionChange: function(e) {
    this.setData({
      region: e.detail.value
    })
  },
  getphone: function(e) {
    this.setData({
      selected: true,
      selected1: false,
    });
    countdown(this);
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    wx.hideShareMenu({
    })
    this.setData({
      address_id: options.address_id,
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {
    var that = this;
    var data = {
      address_id: that.data.address_id,
    }
    var region = this.data.region;
    app.getData('Address/addressInfo', that, data, function(data) {
      region[0] = data.data.province;
      region[1] = data.data.city;
      region[2] = data.data.district;
      var is_default=0;
      if (data.data.addressInfo){
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