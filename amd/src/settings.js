define([], function() {
    return {
        init: function() {
            const tokenForm = document.getElementById('settingformtoken');
            const setupForm = document.getElementById('setupform');

            const tokenBtn = document.getElementById('totokenbtn');
            const setupBtn = document.getElementById('tosetupbtn');

            if (tokenBtn && tokenForm) {
                tokenBtn.addEventListener('click', () => tokenForm.submit());
            }

            if (setupBtn && setupForm) {
                setupBtn.addEventListener('click', () => setupForm.submit());
            }
        }
    };
});
