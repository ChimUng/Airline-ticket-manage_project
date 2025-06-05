<!-- Chat Fixed Box -->
<div class="chat-container">
  <div class="chat-header">🤖 Trợ lý AI</div>
  <div class="chat-messages" id="chatMessages">
    <div class="message bot">Xin chào! Mình có thể giúp gì cho bạn?</div>
  </div>
  <div class="chat-input">
    <input type="text" id="chatInput" placeholder="Nhập tin nhắn..." />
    <button onclick="sendMessage()">Gửi</button>
  </div>
</div>

<style>
  .chat-container {
    position: fixed;
    bottom: 0;
    right: 20px;
    width: 300px;
    height: 500px;
    background: #f9f9f9;
    border: 1px solid #ccc;
    border-radius: 20px 20px 0 0;
    display: flex;
    flex-direction: column;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    font-family: Arial, sans-serif;
    z-index: 1000;
  }

  .chat-header {
    background: #4f46e5;
    color: #fff;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
  }

  .chat-messages {
    flex: 1;
    padding: 10px;
    overflow-y: auto;
    font-size: 14px;
    background: #ffffff;
  }

  .chat-input {
    display: flex;
    border-top: 1px solid #ccc;
    padding: 10px;
    background: #fff;
  }

  .chat-input input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    margin-right: 8px;
  }

  .chat-input button {
    background-color: #4f46e5;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
  }

  .message {
    margin-bottom: 8px;
    padding: 6px 10px;
    border-radius: 12px;
    max-width: 80%;
    word-wrap: break-word;
  }

  .message.bot {
    background-color: #e0e7ff;
    align-self: flex-start;
  }

  .message.user {
    background-color: #d1fae5;
    align-self: flex-end;
    text-align: right;
  }
</style>

<script>
  function sendMessage() {
    const input = document.getElementById("chatInput");
    const message = input.value.trim();
    const chatBox = document.getElementById("chatMessages");

    if (message !== "") {
      // Tin nhắn người dùng
      const userMsg = document.createElement("div");
      userMsg.className = "message user";
      userMsg.textContent = message;
      chatBox.appendChild(userMsg);

      // Giả lập phản hồi từ AI
      // Gửi yêu cầu đến server Python
      fetch("http://localhost:8080/chat", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify({ message }),
})
.then((res) => res.json())
.then((data) => {
  const botMsg = document.createElement("div");
  botMsg.className = "message bot";
  botMsg.textContent = data.reply;
  chatBox.appendChild(botMsg);
  chatBox.scrollTop = chatBox.scrollHeight;
})
.catch((error) => {
  console.error("Error:", error);
  const errorMsg = document.createElement("div");
  errorMsg.className = "message error";
  errorMsg.textContent = "Có lỗi khi gửi yêu cầu tới server!";
  chatBox.appendChild(errorMsg);
});

      input.value = "";
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  }
</script>
