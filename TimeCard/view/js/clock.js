timerId = null;

const clock = () => {
    let now = new Date();
    let clock = document.getElementById('now');
    let year = now.getFullYear();
    let month = now.getMonth() + 1;
    let date = ('00' + now.getDate()).slice( -2 );
    let hour = ('00' + now.getHours()).slice( -2 );
    let minute = ('00' + now.getMinutes()).slice( -2 );
    let second = ('00' + now.getSeconds()).slice( -2 );
    clock.innerHTML = year + '年' + month + '月' + date + '日' + ' ' + hour + ':' + minute + ':' + second;
}

const startTimer = () => {
    timerId = setInterval('clock()',1000);
}
    
const stopTimer = () => {
    clearInterval(timerId);
}