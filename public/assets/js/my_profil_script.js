const EMAIL_VAL = document.getElementById('emailInput').value
let span = document.getElementById('iconEmail')
hide_email()

function hide_email() {
    let email = document.getElementById('emailInput')
    if (email !== null) {
        emailvalue = email.value
        split = emailvalue.split('@')
        name_length = split[0].length
        stars = ''
        for (let i = 0; i<name_length; i++) {
            stars += '*'
        }
        email.value = stars+'@'+split[1]
    }
    span.innerHTML = "<i class=\"far fa-eye\" onclick='show_email()'></i>"
}
function show_email(){
    let email = document.getElementById('emailInput')
    email.value = EMAIL_VAL;
    span.innerHTML = "<i class=\"far fa-eye-slash\" onclick='hide_email()'></i>"
}