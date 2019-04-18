var interval;
//消息框获取焦点
$('#send-input').focus(function() {
    interval = setInterval(function() {
        scrollToEnd();
    }, 500)
})

//消息框失去焦点
$('#send-input').blur(function() {
    clearInterval(interval);
})

//滚动到底部
function scrollToEnd() {
    document.body.scrollTop = document.body.scrollHeight;
}

message_scrollTop();

var ws = new WebSocket("ws://129.204.147.27:10000");
ws.onopen = function() {
    var msg_obj = { "action_type": "login", "uid": uid, "username": username, "photo": photo };
    var msg = JSON.stringify(msg_obj);
    ws.send(msg);
};
ws.onmessage = function(e) {
    var msg = JSON.parse(e.data);
    console.log(msg);
    // 新用户登录
    if (msg.action_type == 'new_user_login') {
        var html = '<div class="remind-box"><span>' + msg.username + '</span>进入了聊天室</div>';
        $(".message-list-box").append(html);
		message_scrollTop();
    }
    // 新消息
    if (msg.action_type == 'new_msg') {
        var my_html = '<div class="message message-right"><div class="img-box"></div><div class="message-text my-message">' + msg.content + '</div><div class="right-arrow-box"><div class="right-arrow"></div></div><div class="img-box"><img src="' + msg.photo + '" alt=""></div></div>';
        var others_html = '<div class="message message-left"><div class="img-box"><img src="' + msg.photo + '" alt=""></div><div class="left-arrow-box"><div class="left-arrow"></div></div><div class="message-text">' + msg.content + '</div><div class="img-box"></div></div>';
        if (msg.my_msg == 1) {
            $(".message-list-box").append(my_html);
        } else {
            $(".message-list-box").append(others_html);
        }
		message_scrollTop();
    }
    // 当前在线人数
    if (msg.action_type == 'online_user_count') {
        var html = msg.online_user_count + '人在线';
        $("#online_user_count").html(html);
    }
    // 用户断开链接
    if (msg.action_type == 'user_on_close') {
        var html = '<div class="remind-box"><span>' + msg.username + '</span>离开了聊天室</div>';
        $(".message-list-box").append(html);
        message_scrollTop();
    }
};

$(document).keyup(function(event){
  if(event.keyCode ==13){
    send();
  }
});

function send() {
    var content = $('#send-input').val();
    $('#send-input').val('');
    var msg_obj = { "action_type": "send_msg", "content": content };
    var msg = JSON.stringify(msg_obj);
    ws.send(msg);
}