const username = document.getElementById('registration_form_username');
const password = document.getElementById('registration_form_plainPassword');
const email = document.getElementById('registration_form_email');
const confirmPassword = document.getElementById('passwordConfirm');
const btnConfirm =  document.getElementById("btn_send");

username.addEventListener('keyup', verifyUsername);
password.addEventListener('keyup', verifyPassword);
email.addEventListener('change', verifyEmail);
confirmPassword.addEventListener('keyup', verifyConfirmPassword);


function verifyUsername(){
    let str = username.value.toLowerCase();
    let span = document.createElement('span');
    let labelUsername = document.getElementById('username');
    let urlUsernameVerify = document.getElementById('usernameUrl').value.replace('test',str);

    if(str.includes(' ') || str.trim().length === 0){
        labelUsername.firstElementChild.remove()
        btnConfirm.disabled = true;
        return;
    }
    if(str.length > 3 ){
        async function fetchAsync(){
            span.innerHTML = "<i class=\"fas fa-spinner spin\"></i>"
            await fetch(urlUsernameVerify)
                .then(response => (response.json()))
                .then(function (res){
                    if(!res){
                        span.innerHTML = "<i class='bx bx-check-circle' ></i>";
                        btnConfirm.disabled = false;
                    }else{
                        span.innerHTML = "<i class='bx bx-no-entry'></i>";
                        btnConfirm.disabled = true;
                    }});
            username.addEventListener('change', function (){
                if(labelUsername.childElementCount > 1){
                    labelUsername.firstElementChild.remove();
                }})
        }

        fetchAsync().then()
        btnConfirm.disabled = false;
    }else{
        span.innerHTML = "<i class='bx bx-no-entry'>4 Caractères minimum</i>"
        btnConfirm.disabled = true;
    }
    labelUsername.appendChild(span);
    deleteChild(labelUsername);
}
function verifyPassword(){
    let str = password.value
    let span = document.createElement('span');
    let labelMdp = document.getElementById('password');

    if (str.length < 6){
        span.innerHTML += "<i class='bx bx-no-entry'> 6 Caractères minimum</i>";
        btnConfirm.disabled = true
    }else{
        span.innerHTML += "<i class='bx bx-check-circle' ></i>";
        btnConfirm.disabled = false;
    }
    labelMdp.appendChild(span);
    deleteChild(labelMdp);
}
function verifyEmail(){
    let str = email.value
    let span = document.createElement('span');
    let labelEmail = document.getElementById('labelEmail');
    let urlEmailVerify = document.getElementById('emailUrl').value.replace('test',str);

    if(!str.includes('@') || str === '' || str.trim().length === 0){
       deleteChild(labelEmail)
       btnConfirm.disabled = true;
       span.innerHTML = "<i class='bx bx-no-entry'>E-mail invalide</i>";
       labelEmail.appendChild(span);
       deleteChild(labelEmail)
       return;
    }
    async function fetchAsync(){
        span.innerHTML = "<i class=\"fas fa-spinner spin\"></i>"
        await fetch(urlEmailVerify)
            .then(response => (response.json()))
            .then(function (res){
                if(!res){
                    span.innerHTML = "<i class='bx bx-check-circle' ></i>";
                }else{
                    span.innerHTML = "<i class='bx bx-no-entry'></i>";
                }});
    }
    fetchAsync().then()
    labelEmail.appendChild(span);
    deleteChild(labelEmail)
}
function verifyConfirmPassword(){
    let str = confirmPassword.value
    let span = document.createElement('span');
    let labelMdp = document.getElementById('confirmPassword');
    deleteChild(labelMdp)
    if(str.length  === 0){
        labelMdp.firstElementChild.remove();
    }
    if(password.value !== str){
        span.innerHTML += "<i class='bx bx-no-entry' > Les mots de passes de correspondent pas</i>";
        btnConfirm.disabled = true;
    }if(password.value === str){
        span.innerHTML += "<i class='bx bx-check-circle' ></i>";
        btnConfirm.disabled = false;
    }
    labelMdp.appendChild(span);
    deleteChild(labelMdp)
}
function deleteChild(label){
    if(label.childElementCount > 1){
        label.firstElementChild.remove();
    }
}