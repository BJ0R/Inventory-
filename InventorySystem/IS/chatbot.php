
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System Chatbot</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Chatbot Container */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            font-family: 'Arial', sans-serif;
        }
        
        /* Chatbot Button */
        .chatbot-button {
            width: 60px;
            height: 60px;
            background-color: #8f0002;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .chatbot-button:hover {
            background-color: #520000;
            transform: scale(1.1);
        }
        
        .chatbot-button i {
            font-size: 24px;
        }
        
        /* Chatbot Window */
        .chatbot-window {
            width: 350px;
            height: 500px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: none;
            flex-direction: column;
        }
        
        .chatbot-window.active {
            display: flex;
        }
        
        /* Chatbot Header */
        .chatbot-header {
            background-color: #8f0002;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chatbot-header h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .chatbot-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }
        
        /* Chatbot Messages */
        .chatbot-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background-color: #f9f5db;
        }
        
        .message {
            margin-bottom: 15px;
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 18px;
            line-height: 1.4;
            position: relative;
            word-wrap: break-word;
        }
        
        .bot-message {
            background-color: white;
            color: #333;
            border-top-left-radius: 0;
            align-self: flex-start;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .user-message {
            background-color: #520000;
            color: white;
            border-top-right-radius: 0;
            align-self: flex-end;
            margin-left: auto;
        }
        
        /* Chatbot Input */
        .chatbot-input {
            display: flex;
            padding: 10px;
            background-color: white;
            border-top: 1px solid #eee;
        }
        
        .chatbot-input input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
            font-size: 14px;
        }
        
        .chatbot-input button {
            background-color: #8f0002;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-left: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .chatbot-input button:hover {
            background-color: #520000;
        }
        
        /* Typing indicator */
        .typing-indicator {
            display: flex;
            padding: 10px 15px;
            background-color: white;
            border-radius: 18px;
            border-top-left-radius: 0;
            align-self: flex-start;
            margin-bottom: 15px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background-color: #8f0002;
            border-radius: 50%;
            margin: 0 2px;
            animation: typingAnimation 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(1) {
            animation-delay: 0s;
        }
        
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typingAnimation {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-5px);
            }
        }
        
        /* Timestamp */
        .message-timestamp {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
            text-align: right;
        }
        
        /* Responsive Design */
        @media (max-width: 480px) {
            .chatbot-container {
                bottom: 10px;
                right: 10px;
            }
            
            .chatbot-window {
                width: calc(100vw - 20px);
                height: 70vh;
                right: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Chatbot Container -->
    <div class="chatbot-container">
        <div class="chatbot-button" id="chatbotButton">
            <i class="fas fa-robot"></i>
        </div>
        <div class="chatbot-window" id="chatbotWindow">
            <div class="chatbot-header">
                <h3>Inventory Assistant</h3>
                <button class="chatbot-close" id="chatbotClose">&times;</button>
            </div>
            <div class="chatbot-messages" id="chatbotMessages">
                <!-- Messages will appear here -->
            </div>
            <div class="chatbot-input">
                <input type="text" id="chatbotInput" placeholder="Type your message here...">
                <button id="chatbotSend"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const chatbotButton = $('#chatbotButton');
            const chatbotWindow = $('#chatbotWindow');
            const chatbotClose = $('#chatbotClose');
            const chatbotMessages = $('#chatbotMessages');
            const chatbotInput = $('#chatbotInput');
            const chatbotSend = $('#chatbotSend');
            
            // Toggle chatbot window
            chatbotButton.click(function() {
                chatbotWindow.toggleClass('active');
                if (chatbotWindow.hasClass('active')) {
                    chatbotMessages.scrollTop(chatbotMessages[0].scrollHeight);
                    chatbotInput.focus();
                }
            });
            
            chatbotClose.click(function() {
                chatbotWindow.removeClass('active');
            });
            
            // Send message function
            function sendMessage() {
                const message = chatbotInput.val().trim();
                if (message === '') return;
                
                // Add user message to chat
                addMessage(message, 'user');
                chatbotInput.val('');
                
                // Show typing indicator
                const typingIndicator = $(`
                    <div class="typing-indicator">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                `);
                chatbotMessages.append(typingIndicator);
                chatbotMessages.scrollTop(chatbotMessages[0].scrollHeight);
                
                // Send message to backend
                $.ajax({
                    url: 'http://localhost:5000/chat',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ message: message }),
                    success: function(response) {
                        // Remove typing indicator
                        typingIndicator.remove();
                        
                        // Add bot response
                        addMessage(response.response, 'bot');
                        
                        // Handle redirect if needed
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    },
                    error: function(xhr, status, error) {
                        typingIndicator.remove();
                        addMessage("Sorry, I'm having trouble connecting to the server. Please try again later.", 'bot');
                    }
                });
            }
            
            // Add message to chat
            function addMessage(text, sender) {
                const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const messageElement = $(`
                    <div class="message ${sender}-message">
                        ${text}
                        <div class="message-timestamp">${timestamp}</div>
                    </div>
                `);
                chatbotMessages.append(messageElement);
                chatbotMessages.scrollTop(chatbotMessages[0].scrollHeight);
            }
            
            // Send message on button click
            chatbotSend.click(sendMessage);
            
            // Send message on Enter key
            chatbotInput.keypress(function(e) {
                if (e.which === 13) {
                    sendMessage();
                }
            });
            
            // Initial greeting
            setTimeout(() => {
                addMessage("Hello! I'm your Inventory System Assistant. How can I help you today?", 'bot');
            }, 500);
        });
    </script>
</body>
</html>