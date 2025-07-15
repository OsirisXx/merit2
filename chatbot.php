<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ally: AI Legal Assistant - Adoption Help Philippines</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            position: relative;
        }

        /* Floating Chat Button */
        .chat-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .chat-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }

        .chat-button .icon {
            color: white;
            font-size: 24px;
        }

        /* Chat Popup */
        .chat-popup {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            display: none;
            flex-direction: column;
            z-index: 999;
            overflow: hidden;
        }

        .chat-popup.active {
            display: flex;
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-header h3 {
            font-size: 16px;
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message {
            display: flex;
            margin-bottom: 10px;
        }

        .message.user {
            justify-content: flex-end;
        }

        .message.bot {
            justify-content: flex-start;
        }

        .message-content {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 15px;
            font-size: 13px;
            line-height: 1.4;
        }

        .message.user .message-content {
            background: #667eea;
            color: white;
        }

        .message.bot .message-content {
            background: #f1f3f5;
            color: #333;
            border: 1px solid #e9ecef;
        }

        .chat-input {
            padding: 15px;
            border-top: 1px solid #e9ecef;
        }

        .input-group {
            display: flex;
            gap: 8px;
        }

        .chat-input input {
            flex: 1;
            padding: 10px 14px;
            border: 2px solid #e9ecef;
            border-radius: 20px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.3s;
        }

        .chat-input input:focus {
            border-color: #667eea;
        }

        .chat-input button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.3s;
            min-width: 60px;
        }

        .chat-input button:hover {
            background: #5a6fd8;
        }

        .chat-input button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .typing-indicator {
            display: none;
            align-items: center;
            gap: 5px;
            color: #666;
            font-style: italic;
            padding: 10px 15px;
            font-size: 12px;
        }

        .typing-dots {
            display: flex;
            gap: 2px;
        }

        .typing-dots span {
            width: 4px;
            height: 4px;
            background: #666;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-6px);
            }
        }

        .welcome-message {
            text-align: center;
            color: #666;
            padding: 10px;
            font-style: italic;
            font-size: 12px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .chat-popup {
                width: calc(100vw - 20px);
                height: 70vh;
                right: 10px;
                bottom: 80px;
            }
            
            .chat-button {
                right: 15px;
                bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Chat Button -->
    <div class="chat-button" id="chatButton">
        <img src="images/adoptionbotimg.png" alt="Chat with Ally" style="width: 50px; height: auto; border-radius: 50%;">
    </div>

    <!-- Chat Popup -->
    <div class="chat-popup" id="chatPopup">
        <div class="chat-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="images/adoptionbotimg.png" alt="Ally AI" style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid white;">
                <div>
                    <h3 style="margin: 0; font-size: 16px;">Ally</h3>
                    <p style="margin: 0; font-size: 12px; opacity: 0.9;">AI Legal Assistant</p>
                </div>
            </div>
            <button class="close-btn" id="closeBtn">×</button>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="welcome-message">
                <p>👋 Hello! I'm Ally, your AI Legal Assistant. I'm here to help you with questions about adoption in the Philippines.</p>
                <p>You can ask me in English or Filipino. How can I assist you today?</p>
            </div>
        </div>
        
        <div class="typing-indicator" id="typingIndicator">
            <span>Ally is typing</span>
            <div class="typing-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        
        <div class="chat-input">
            <div class="input-group">
                <input type="text" id="messageInput" placeholder="Ask Ally about adoption in the Philippines..." maxlength="500">
                <button id="sendButton" onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>

    <script>
        const chatButton = document.getElementById('chatButton');
        const chatPopup = document.getElementById('chatPopup');
        const closeBtn = document.getElementById('closeBtn');
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const typingIndicator = document.getElementById('typingIndicator');

        // Toggle chat popup
        chatButton.addEventListener('click', function() {
            chatPopup.classList.toggle('active');
            if (chatPopup.classList.contains('active')) {
                messageInput.focus();
            }
        });

        // Close chat popup
        closeBtn.addEventListener('click', function() {
            chatPopup.classList.remove('active');
        });

        // Close popup when clicking outside
        document.addEventListener('click', function(e) {
            if (!chatPopup.contains(e.target) && !chatButton.contains(e.target)) {
                chatPopup.classList.remove('active');
            }
        });

        // Send message on Enter key
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Add user message to chat
            addMessage(message, 'user');
            messageInput.value = '';
            sendButton.disabled = true;
            
            // Show typing indicator
            typingIndicator.style.display = 'flex';
            chatMessages.scrollTop = chatMessages.scrollHeight;

            try {
                const response = await fetch('chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();
                
                // Hide typing indicator
                typingIndicator.style.display = 'none';
                
                if (data.success) {
                    addMessage(data.response, 'bot');
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                }
            } catch (error) {
                typingIndicator.style.display = 'none';
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            }

            sendButton.disabled = false;
            messageInput.focus();
        }

        function addMessage(content, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = content;
            
            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>
</html> 