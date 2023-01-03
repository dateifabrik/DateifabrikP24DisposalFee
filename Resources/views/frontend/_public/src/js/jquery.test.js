$(document).ready(function(){
    console.log('works - from plugin resource');

    $('select[name="applyLicenseFee"]').change(function(){
        console.log('applyLicenseFee Option ' + $('select[name="applyLicenseFee"]').val() + 'geklickt');
        $('.mymodal').css({'z-index':'10', 'position':'absolute', 'top':'0', 'right':'0', 'bottom':'0', 'left':'0', 'background-color':'rgba(0,0,0,.66)'});
    });

});

