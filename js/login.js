/** Operate selected DOMs
 *
 * @param {string} selector DOM selector
 * @param {object} callback Callbck function
 * @param {string} method Selecte method
 */
const wwaotml_dom = (selector, callback = () => { }, method = 'query') => {
    let dom_list = [];
    if (method === 'id') {
        let dom = document.getElementById(selector);
        if (dom) {
            callback(dom);
        }
        return;
    } else if (method === 'class') {
        dom_list = document.getElementsByClassName(selector);
    } else if (method === 'tag') {
        dom_list = document.getElementsByTagName(selector);
    } else {
        dom_list = document.querySelectorAll(selector);
    }
    for (let dom of dom_list) {
        callback(dom);
    }
    return;
}

const findList = [
    '#loginform',
    '#lostpasswordform',
    '#registerform',
    '.admin-email-confirm-form',
    '#resetpassform',
]

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementsByClassName('wwa-otml-form').length > 0) {
        return;
    }
    const nav = document.getElementById('nav');
    if (!nav) {
        let target = null;
        for (let selector of findList) {
            if (document.querySelector(selector)) {
                target = selector;
                break;
            }
        }
        if (target) {
            const p = document.createElement('p');
            p.id = 'nav';
            p.innerHTML = `<a href="${wwaotml_php_vars.request_url}" title="${wwaotml_php_vars.one_time}">${wwaotml_php_vars.one_time}</a>`;
            target.after(p);
        }
    } else {
        if (nav.textContent.trim().endsWith(wwaotml_php_vars.separator.trim())) {
            nav.innerHTML += `<a href="${wwaotml_php_vars.request_url}" title="${wwaotml_php_vars.one_time}">${wwaotml_php_vars.one_time}</a>`;
        } else {
            nav.innerHTML += ` ${wwaotml_php_vars.separator} <a href="${wwaotml_php_vars.request_url}" title="${wwaotml_php_vars.one_time}">${wwaotml_php_vars.one_time}</a>`;
        }
    }
})