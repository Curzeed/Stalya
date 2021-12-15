window.onload = timer(900)

function timer(time) {
    if (time == 0) {
        let form = document.getElementById('qcm_form')
        let input = document.createElement('input')
        input.name = 'timeout'
        input.type = 'hidden'
        form.appendChild(input)
        form.submit()
    }
    let container = document.getElementById('timer')
    converted = convertMS(time)
    container.innerHTML = converted
    time--
    setTimeout(() => {
        timer(time)
    }, 1000)
}

function convertMS(value) {
    let sec = parseInt(value, 10); // convert value to number if it's string
    let hours   = Math.floor(sec / 3600); // get hours
    let minutes = Math.floor((sec - (hours * 3600)) / 60); // get minutes
    let seconds = sec - (hours * 3600) - (minutes * 60); //  get seconds
    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return minutes+':'+seconds; // Return is MM : SS
}