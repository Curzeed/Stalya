var myModal = document.getElementById('maonmodal')
var myInput = document.getElementById('btn_qcm_start')

myModal.addEventListener('shown.bs.modal', function () {
    myInput.focus()
})

