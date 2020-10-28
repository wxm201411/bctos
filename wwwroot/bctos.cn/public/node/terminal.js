var resizeInterval;
var wSocket = new WebSocket("ws:{:SSH_IP}:2100");
Terminal.applyAddon(attach);  // Apply the `attach` addon
Terminal.applyAddon(fit);  //Apply the `fit` addon
var term = new Terminal({
    cols: 80,
    rows: 24,
    disableStdin: true
});
term.open(document.getElementById('terminal'));


function ConnectServer() {
    var dataSend = {"auth": {}};
    wSocket.send(JSON.stringify(dataSend));
    term.fit();
    //term.focus();
}

wSocket.onopen = function (event) {
    console.log("Socket Open");
    ConnectServer()
    term.attach(wSocket, false, false);
    window.setInterval(function () {
        wSocket.send(JSON.stringify({"refresh": ""}));
    }, 700);
};

wSocket.onerror = function (event) {
    term.detach(wSocket);
    alert("Connection Closed");
}

function sendData(data) {
    //var dataSend = {"data":{"data":data}};
    var dataSend = {"line": data};
    wSocket.send(JSON.stringify(dataSend));
    //Xtermjs with attach dont print zero, so i force. Need to fix it :(
    if (data == "0") {
        term.write(data);
    }
}

term.on('data', function (data) {
    sendData(data)
})

function exec(data) {
    sendData(data)
}

//Execute resize with a timeout
window.onresize = function () {
    clearTimeout(resizeInterval);
    resizeInterval = setTimeout(resize, 400);
}

// Recalculates the terminal Columns / Rows and sends new size to SSH server + xtermjs
function resize() {
    if (term) {
        term.fit()
    }
}