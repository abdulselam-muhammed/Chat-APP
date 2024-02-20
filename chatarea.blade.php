
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat App</title>
</head>
<body>
    <style>
        .message-outgoing {
            text-align: right;
            background-color: #448ae6;
            margin: 5px;
            padding: 10px;
            border-radius: 10px;
        }
        .message-incoming {
            text-align: left;
            background-color: #667cb8;
            margin: 5px;
            padding: 10px;
            border-radius: 10px;
        }
    </style>
<div id="messages_container"></div>

<label for="">Message :</label>
<form action="" onsubmit ="return send()">
    <input type="text" name="message" id="message">
    <button type="submit">send</button>
</form>

<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>   
   var auth_user = `{{auth()->id()}}`;
    var pusher = new Pusher('your-key', {
        authEndpoint: '/broadcasting/auth', // if Not Auto
        cluster: 'eu',
        useTLS: true 
    });

    var channel = pusher.subscribe('private-chat');
    channel.bind('message', function(data) {
        var userId = data.userId;
        var recever_userId = data.recever_userId;
        var message = data.message;
        var chanel = data.chanel;
        var messagesContainer = document.getElementById('messages_container');
        var messageElement = document.createElement('div');
        
        if(chanel == userId+'_'+recever_userId || chanel == recever_userId+'_'+userId){
            if(userId == auth_user){
                messageElement.classList.add('message-outgoing');
            }else{
                messageElement.classList.add('message-incoming');
            }
            messageElement.textContent = message;
            messagesContainer.appendChild(messageElement);
        }
    });


    function send() {
        var messageinput = document.getElementById('message').value;
        var recever_userId = `{{$recever_user->id}}`;
        var recever_userEmail = `{{$recever_user->email}}`;
        const form_data  = new FormData();

        form_data.append('message', messageinput);
        form_data.append('recever_userId', recever_userId);
        form_data.append('recever_userEmail', recever_userEmail);
        
        $.ajax({
            type: 'POST',
            cache: false,
            url: '/message',
            contentType: false,
            processData: false,
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (result) {
                document.getElementById('message').value = '';
            },
            error: function(e, x, p) {
                console.log(x);
            }
        });
        return false;
    }


    function loadPreviousMessages() {
        var messageinput = document.getElementById('message').value;
        var recever_userId = `{{$recever_user->id}}`;
        var recever_userEmail = `{{$recever_user->email}}`;
        const form_data  = new FormData();
        
        form_data.append('message', messageinput);
        form_data.append('recever_userId', recever_userId);
        form_data.append('recever_userEmail', recever_userEmail);
        $.ajax({
            method: 'POST',
            cache: false,
            url: '/get-messages', 
            contentType: false,
            processData: false,
            data:form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                var userId = data.userId;
                var recever_userId = data.recever_userId;
                var chanel = data.chanel;
                console.log(userId);
                console.log(auth_user);
                var messagesContainer = document.getElementById('messages_container');
                data.messages.forEach(function(message) {
                if(chanel == userId+'_'+recever_userId || chanel == recever_userId+'_'+userId){
                    var messageElement = document.createElement('div');
                    if(message.sender_user == auth_user){
                        messageElement.classList.add('message-outgoing');
                    }else{
                        messageElement.classList.add('message-incoming');
                    }
                    messageElement.textContent = message.message;
                    messagesContainer.appendChild(messageElement);
                }
                });
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function(){
        loadPreviousMessages();
    });
</script>

</body>
</html>
