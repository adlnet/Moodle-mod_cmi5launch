"use strict";

define([], function () {
  return {
    init: function init() {
      var tokenForm = document.getElementById('settingformtoken');
      var setupForm = document.getElementById('setupform');
      var tokenBtn = document.getElementById('totokenbtn');
      var setupBtn = document.getElementById('tosetupbtn');
      if (tokenBtn && tokenForm) {
        tokenBtn.addEventListener('click', function () {
          return tokenForm.submit();
        });
      }
      if (setupBtn && setupForm) {
        setupBtn.addEventListener('click', function () {
          return setupForm.submit();
        });
      }
    }
  };
});
