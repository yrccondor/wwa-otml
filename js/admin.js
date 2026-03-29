// Toggle mail template editor
jQuery('.wwa-otml-open-editor').click((e) => {
    e.preventDefault();
    jQuery('.wwa-otml-open-editor').hide();
    jQuery('.wwa-otml-close-editor').show();
    jQuery('#wwa-otml-mail-template-editor').attr('style', '');
});
jQuery('.wwa-otml-close-editor').click((e) => {
    e.preventDefault();
    jQuery('.wwa-otml-close-editor').hide();
    jQuery('.wwa-otml-open-editor').show();
    jQuery('#wwa-otml-mail-template-editor').attr('style', 'height:0');
});

const selectText = (node) => {
    if (document.body.createTextRange) {
        const range = document.body.createTextRange();
        range.moveToElementText(node);
        range.select();
    } else if (window.getSelection) {
        const selection = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(node);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}

jQuery(() => {
    jQuery('.wwa-otml-details li code').click((e) => {
        selectText(e.target);
    });
});