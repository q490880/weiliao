var wsServer = null;
var ws = null;

function sendMessage(message){
    ws.send(JSON.stringify(message))
}

function analysisMessage(message){
    var message = JSON.parse(message)
    if(message.type == "system"){
        var content = $('#resultMessage').val();
        if(isNull(content)){
            content = curentTime() + "\n" + message.message;
        }else{
            content = content + "\n" + curentTime() + "\n" + message.message;
        }
        $('#resultMessage').val(content);
        $("#resultMessage").scrollTop(document.getElementById("resultMessage").scrollHeight);
    }
    if(message.action == "join_room"){
        var memberMessage = '<a href="javascript:" data="'+message.userId+'" class="online_member_list" onclick="selectSendObject(this)">'+message.userName+'</a>';
        $('#online_member').append(memberMessage);
    }else if(message.action == "exit_room"){
        $('#online_member a[data='+message.userId+']').remove();
    }
}

function connection(){
    var wsServer = "ws://www.weiliao.com:9501";
    ws = new WebSocket(wsServer);
    ws.onopen = function (evt) {
        console.log("connection success")
        sendMessage({
            "controller": "Test",
            "action": "intoRoom",
            "data": {
                "userId": getCookie("user_id"),
                "roomId": room_id,
                "token" : getCookie("token"),
                "name" : decodeURI(getCookie("name"))
            }
        });
    };
    ws.onmessage = function (evt) {
        analysisMessage(evt.data);
    };
    ws.onerror = function (evt) {
        console.log("connection error")
    };
    ws.onclose = function (evt) {
        console.log("connection close")
    };
}

connection();
$('#sendSubmit').click(function(){
    var sendContent = $('#sendMessage').val();
    if(isNull(sendContent)){
        layer.msg("要发送的内容不能为空!");
        return false;
    }
    $('#sendMessage').val('');
    var send_member_id = $('#sendObject').attr('member_id');
    if(send_member_id == 0){
        sendMessage({
            "controller": "Test",
            "action": "sendToRoom",
            "data": {
                "fromUserId": getCookie("user_id"),
                "roomId": room_id,
                "token" : getCookie("token"),
                "message" : sendContent
            }
        });
    }else{
        sendMessage({
            "controller": "Test",
            "action": "sendToUser",
            "data": {
                "fromUserId": getCookie("user_id"),
                "message": sendContent,
                "userId" : send_member_id,
                "token" : getCookie("token")
            }
        });
    }
});

