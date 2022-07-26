let privateKey = document.getElementById('private-key');

privateKey.value = localStorage.getItem('privateKey') || '';


function rememberToken() {
    localStorage.setItem('privateKey', privateKey.value);
}
