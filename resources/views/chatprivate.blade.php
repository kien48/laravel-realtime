@extends('layouts.app')

@section('css')
    <style>
        /* CSS cho danh sách người dùng */
        .user-list {
            background: #fff;
            border-right: 1px solid #ddd;
            padding: 10px;
            height: calc(100vh - 120px);
            overflow-y: auto;
        }

        .user-list a {
            display: flex;
            align-items: center;
            padding: 5px;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: background 0.3s ease;
            text-decoration: none;
            color: #333;
        }

        .user-list a:hover {
            background: #f5f5f5;
        }

        .user-list img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }

        /* CSS cho khung chat */
        .block-chat {
            height: calc(100vh - 200px);
            overflow-y: auto;
            list-style: none;
            padding: 10px;
            margin: 0;
            background: #e5ddd5;
            border-radius: 5px;
        }

        .block-chat li {
            display: flex;
            flex-direction: column;
            padding: 10px; /* Điều chỉnh padding cho khoảng cách tốt hơn */
            margin-bottom: 10px; /* Điều chỉnh margin cho khoảng cách tốt hơn */
            border-radius: 15px;
            max-width: 40%;
            word-wrap: break-word;
            position: relative;
        }

        .my-message {
            color: white;
            background-color: #0084ff;
            margin-left: auto;
        }

        .other-message {
            color: black;
            background-color: #f1f0f0;
        }

        /* CSS cho input chat */
        .chat-input {
            display: flex;
            align-items: center;
            padding: 5px;
            background: #f5f5f5;
            border-top: 1px solid #ddd;
        }

        .chat-input input {
            border: none;
            border-radius: 15px;
            flex: 1;
            padding: 5px 10px;
            margin-right: 5px;
            outline: none;
            background: #e5e5ea;
        }

        .status {
            background-color: green;
            height: 10px;
            width: 10px;
            border-radius: 50%;
        }

        .chat-input button {
            background-color: #0084ff;
            color: white;
            border: none;
            border-radius: 15px;
            padding: 5px 10px;
            cursor: pointer;
        }

        .chat-input button:hover {
            background-color: #006bbf;
        }

        /* Thời gian tin nhắn */
        .message-time {
            font-size: 0.8rem; /* Điều chỉnh kích thước font của thời gian */
            color: #ef4444; /* Điều chỉnh màu sắc của thời gian */
            position: absolute; /* Vị trí tương đối so với phần tử cha li */
            bottom: 5px; /* Vị trí ở dưới cùng */
            right: 10px; /* Điều chỉnh vị trí bên phải */
        }

        /* Kiểu scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            box-shadow: inset 0 0 5px grey;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #0084ff;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #b30000;
        }

    </style>

@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-2 user-list">
                    <a href="{{route('chatPrivate',$user->id)}}" id="link_{{$user->id}}">
                        <img src="{{$user->image}}" alt="" class="img-fluid">
                        <p>{{$user->name}}</p>
                    </a>
            </div>
            <div class="col-10">
                <ul class="block-chat" id="messages">
                    @foreach($messages as $message)
                        <li class="{{ $message->user_id === Auth::id() ? 'my-message' : 'other-message' }}">
                            <span>{{ $message->user->name }}:</span> @php
                                if(! \Str::contains($message->content, 'https://')) {
                                    echo $message->content;
                                }else{
                                    echo '<img src="'.$message->content.'" alt="" class="img-fluid" style="width: 200px">';
                                }
                            @endphp
                            <div class="message-time">{{ $message->created_at->format('H:i:s') }}</div>
                        </li>
                    @endforeach
                </ul>
                <form action="" class="chat-input">
                    <input type="text" class="form-control" id="inputChat" placeholder="Nhập tin nhắn">
                    <button type="button" id="btnSend">Gửi</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module">
        // Sử dụng Echo để kết nối với kênh 'chat'
        Echo.join('chat')
            // Xử lý khi có người dùng đang online
            .here((users) => {
                users.forEach((user) => {
                    let el = document.getElementById('link_' + user.id);
                    let elementStatus = document.createElement('div');
                    elementStatus.classList.add('status');
                    if (el) {
                        el.appendChild(elementStatus);
                    }
                })
                console.log(users, 'đang online');
            })
            // Xử lý khi có người dùng tham gia
            .joining((user) => {
                console.log(user, 'đang tham gia');
                let el = document.getElementById('link_' + user.id);
                let elementStatus = document.createElement('div');
                elementStatus.classList.add('status');
                if (el) {
                    el.appendChild(elementStatus);
                }
            })
            // Xử lý khi có người dùng rời đi
            .leaving((user) => {
                console.log(user, 'rời đi');
                let el = document.getElementById('link_' + user.id);
                let elementStatus = el.querySelector('.status');
                if (elementStatus) {
                    el.removeChild(elementStatus);
                }
            })

            // Xử lý khi có lỗi xảy ra
            .error((error) => {
                console.error('Lỗi:', error);
            });

        // Xử lý khi nhấn nút 'Gửi'
        document.getElementById('btnSend').addEventListener('click', () => {
            axios.post('{{ route('sendPrivate',$user->id) }}', {
                msg: document.getElementById('inputChat').value,
                conversation_id : {{$conversation->id}}
            }).then((res) => {
                console.log(res.data.status);
                document.getElementById('inputChat').value = '';
            });
        });
    </script>

{{--    <script type="module">--}}
{{--        Echo.private('chat.private.{{Auth::user()->id}}.{{$user->id}}')--}}
{{--            .listen('ChatPrivate',e=>{--}}
{{--                let messages = document.querySelector('#messages');--}}
{{--                let elementChat = document.createElement('li');--}}
{{--                elementChat.textContent = `${e.userSend.name}: ${e.msg} | ${e.created_at}`;--}}
{{--                elementChat.classList.add('my-message');--}}
{{--                messages.appendChild(elementChat);--}}
{{--            })--}}

{{--        Echo.private('chat.private.{{$user->id}}.{{Auth::user()->id}}')--}}
{{--            .listen('ChatPrivate', e=>{--}}
{{--                let messages = document.querySelector('#messages');--}}
{{--                let elementChat = document.createElement('li');--}}
{{--                elementChat.textContent = `${e.userSend.name}: ${e.msg} | ${e.created_at}`;--}}
{{--                elementChat.classList.add('other-message');--}}
{{--                messages.appendChild(elementChat);--}}
{{--            })--}}
{{--    </script>--}}


    <script type="module">
        Echo.private('chat.private.{{ Auth::user()->id }}.{{ $user->id }}')
            .listen('ChatPrivate', event => {
                let messages = document.querySelector('#messages');
                let elementChat = document.createElement('li');
                let messageContent = `${event.msg}`;
                let messageTime = new Date(event.created_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                if (messageContent.startsWith('https://')) {
                    elementChat.innerHTML = `
                    <strong>${event.userSend.name}:</strong>
                    <img src="${messageContent}" alt="Image" class="img-fluid" style="width: 200px">
                    <div class="message-time">${messageTime}</div>
                `;
                } else {
                    elementChat.innerHTML = `
                    <strong>${event.userSend.name}:</strong>
                    <span>${messageContent}</span>
                    <div class="message-time">${messageTime}</div>
                `;
                }
                elementChat.classList.add('my-message');
                messages.appendChild(elementChat);
                messages.scrollTop = messages.scrollHeight;
            });

        Echo.private('chat.private.{{ $user->id }}.{{ Auth::user()->id }}')
            .listen('ChatPrivate', event => {
                let messages = document.querySelector('#messages');
                let elementChat = document.createElement('li');
                let messageContent = `${event.msg}`;
                let messageTime = new Date(event.created_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                if (messageContent.startsWith('https://')) {
                    elementChat.innerHTML = `
                    <strong>${event.userSend.name}:</strong>
                    <img src="${messageContent}" alt="Image" class="img-fluid" style="width: 200px">
                    <div class="message-time">${messageTime}</div>
                `;
                } else {
                    elementChat.innerHTML = `
                    <strong>${event.userSend.name}:</strong>
                    <span>${messageContent}</span>
                    <div class="message-time">${messageTime}</div>
                `;
                }
                elementChat.classList.add('other-message');
                messages.appendChild(elementChat);
                messages.scrollTop = messages.scrollHeight;

            });
    </script>

@endsection
