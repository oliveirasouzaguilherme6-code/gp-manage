// Relógio

function atualizarRelogio(){

    const agora = new Date();

    let h = agora.getHours().toString().padStart(2,'0');
    let m = agora.getMinutes().toString().padStart(2,'0');
    let s = agora.getSeconds().toString().padStart(2,'0');

    const relogio = document.getElementById("relogio");

    if(relogio){

        relogio.innerHTML = `${h}:${m}:${s}`;

    }

}

setInterval(atualizarRelogio,1000);

atualizarRelogio();