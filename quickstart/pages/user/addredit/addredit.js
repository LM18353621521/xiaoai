//获取应用实例
var tcity = require("../../../utils/citys.js");
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
    is_default: 0,
    province_code: '',
    city_code: '',
    district_code: '',
    provinces: [],
    province: "",
    citys: [],
    city: "",
    countys: [],
    county: '',
    value: [0, 0, 0],
    values: [0, 0, 0],
    condition: false
  },
  bindChange: function (e) {
    //console.log(e);
    var val = e.detail.value
    var t = this.data.values;
    var cityData = this.data.cityData;

    if (val[0] != t[0]) {
      console.log('province no ');
      const citys = [];
      const countys = [];

      for (let i = 0; i < cityData[val[0]].sub.length; i++) {
        citys.push(cityData[val[0]].sub[i].name)
      }
      for (let i = 0; i < cityData[val[0]].sub[0].sub.length; i++) {
        countys.push(cityData[val[0]].sub[0].sub[i].name)
      }

      this.setData({
        province: this.data.provinces[val[0]],
        city: cityData[val[0]].sub[0].name,
        citys: citys,
        county: cityData[val[0]].sub[0].sub[0].name,
        countys: countys,
        values: val,
        value: [val[0], 0, 0]
      })
      return;
    }
    if (val[1] != t[1]) {
      console.log('city no');
      const countys = [];

      for (let i = 0; i < cityData[val[0]].sub[val[1]].sub.length; i++) {
        countys.push(cityData[val[0]].sub[val[1]].sub[i].name)
      }
      this.setData({
        city: this.data.citys[val[1]],
        county: cityData[val[0]].sub[val[1]].sub[0].name,
        countys: countys,
        values: val,
        value: [val[0], val[1], 0]
      })
      return;
    }
    if (val[2] != t[2]) {
      console.log('county no');
      this.setData({
        county: this.data.countys[val[2]],
        values: val
      })
      return;
    }
  },
  open: function () {
    console.log(12);
    this.setData({
      condition: 1,
    })
  },
  sure: function () {
    var value = this.data.value;
    var values = this.data.values;
    console.log(value);
    console.log(values);
    var cityData = this.data.cityData;

    var province_code = cityData[values[0]].code;
    var city_code = cityData[values[0]].sub[values[1]].code;
    var district_code = cityData[values[0]].sub[values[1]].sub[values[2]].code;
    this.setData({
      condition: !this.data.condition,
      province_code: province_code,
      city_code: city_code,
      district_code: district_code,
    })
  },
  close: function () {
    this.setData({
      condition: !this.data.condition
    })
  },
  /**
   * 保存地址
   */
  address_save: function(e) {
    var that = this;
    console.log(e);
    var id = that.data.address_id;
    var cityData = this.data.cityData;
    var values = this.data.values;
    var province_code = cityData[values[0]].code;
    var city_code = cityData[values[0]].sub[values[1]].code;
    var district_code = cityData[values[0]].sub[values[1]].sub[values[2]].code;

    var data = {
      id: that.data.address_id,
      linkman: e.detail.value.linkman,
      linktel: e.detail.value.linktel,
      province_code: province_code,
      city_code: city_code,
      district_code: district_code,
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
        var pages = getCurrentPages();
        var Page = pages[pages.length - 1]; //当前页
        var prevPage = pages[pages.length - 2]; //上一个页面
        console.log(prevPage);
        var addressList = prevPage.data.addressList //取上页data里的数据也可以修改
        console.log(addressList);
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
      console.log(data);
      var is_default=0;
      if (data.data.addressInfo){
        is_default = data.data.addressInfo.is_default;
      }
      that.setData({
        is_default: is_default,
      });
      initCity(that);
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

function initCity(that) {
  tcity.init(that); 
  var cityData = that.data.cityData;
  const provinces = [];
  const citys = [];
  const countys = [];
  var values = [0, 0, 0];
  var value = [0, 0, 0];
  var addressInfo = that.data.addressInfo;
  if(addressInfo){
    var province_code=addressInfo.province_code;
    var city_code = addressInfo.city_code;
    var district_code = addressInfo.district_code;
  }else{
    var province_code = 0;
    var city_code = 0;
    var district_code = 0;
  }
  for (let i = 0; i < cityData.length; i++) {
    provinces.push(cityData[i].name);
    if (province_code == cityData[i].code) {
      values[0] = i;
      value[0] = i;
    }
  }
  console.log('省份完成');
  for (let i = 0; i < cityData[values[0]].sub.length; i++) {
    citys.push(cityData[values[0]].sub[i].name);
    if (city_code == cityData[values[0]].sub[i].code) {
      values[1] = i;
      value[1] = i;
    }
  }
  console.log('city完成');
  for (let i = 0; i < cityData[values[0]].sub[values[1]].sub.length; i++) {
    countys.push(cityData[values[0]].sub[values[1]].sub[i].name);
    if (district_code == cityData[values[0]].sub[values[1]].sub[i].code) {
      values[2] = i;
      value[2] = i;
    }
  }
  console.log(values);
  console.log(provinces);
  console.log(citys);
  console.log(countys);
  that.setData({
    'provinces': provinces,
    'citys': citys,
    'countys': countys,
    'province': cityData[values[0]].name,
    'city': cityData[values[0]].sub[values[1]].name,
    'county': cityData[values[0]].sub[values[1]].sub[values[2]].name,
    value: value,
    values: values,
  })
  console.log('初始化完成');
}