calculateTime()

function calculateTime() {
    const nextDate = new Date(document.getElementById('remaining').value);
    const now = new Date();
    const timer = document.getElementById('timer')

    let diff = nextDate > now ?
        nextDate.getTime() - now.getTime() :
        now.getTime() - nextDate.getTime();

    const days = Math.floor(diff / 1000 / 60 / 60 / 24);
    diff -= days * 1000 * 60 * 60 * 24;
    const hours = Math.floor(diff / 1000 / 60 / 60);
    diff -= hours * 1000 * 60 * 60;
    const minutes = Math.floor(diff / 1000 / 60);
    diff -= minutes * 1000 * 60;
    const seconds = Math.floor(diff / 1000);

    if(diff === 0)
        window.location.reload();

    timer.innerHTML = `${leadingZero(days)}:${leadingZero(hours)}:${leadingZero(minutes)}:${leadingZero(seconds)}`;
    setTimeout(calculateTime, 1000)
}