document.getElementById('btn_modal_send').classList.add('disabled');

function verify(){
    let url = document.getElementById('input_background_url').value
    let modalSend = document.getElementById('btn_modal_send');
    console.log('testetet')
if(url.startsWith('https://docs.google.com/document')){
    modalSend.classList.remove('disabled');
}else{
    modalSend.classList.add('disabled');
}}