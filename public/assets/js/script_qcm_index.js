var time_remaining = document.getElementById('remaining')
var date = Date.parse(time_remaining.value)


calculateTime()

function calculateTime() {
    var now = new Date()
    if (date - Date.parse(now) == 0) {
        window.location.reload()
    }
    time = convertHMS(date - Date.parse(now))

    timer = document.getElementById('timer')
    timer.innerHTML = time
    setTimeout(calculateTime, 1000)
}

function convertHMS(value) {
    let sec = parseInt(value, 10); // convert value to number if it's string
    sec = sec/1000
    let hours   = Math.floor(sec / 3600); // get hours
    let minutes = Math.floor((sec - (hours * 3600)) / 60); // get minutes
    let seconds = sec - (hours * 3600) - (minutes * 60); //  get seconds
    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours+':'+minutes+':'+seconds; // Return is HH : MM : SS
}

