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
}