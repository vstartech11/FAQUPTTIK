<template>
    <div class="chatbot-floating">
        <div class="chatbot-container">
            <div class="chatbot-header">
                <h3>Chatbot</h3>
                <button @click="toggleChatbot" class="close-btn">✕</button>
            </div>
            <div v-if="isOpen" class="chatbot-body">
                <!-- Bubble Chat -->
                <div
                    v-for="(message, index) in messages"
                    :key="index"
                    class="chat-bubble"
                    :class="{
                        user: message.sender === 'user',
                        bot: message.sender === 'bot',
                    }"
                >
                    <span>{{ message.text }}</span>
                </div>
            </div>
            <div v-if="isOpen" class="chatbot-footer">
                <input
                    type="text"
                    v-model="userInput"
                    @keyup.enter="sendMessage"
                    placeholder="Tulis pesan Anda..."
                />
                <button @click="sendMessage">Kirim</button>
            </div>
        </div>
        <button v-if="!isOpen" class="chatbot-toggle" @click="toggleChatbot">
            💬
        </button>
    </div>
</template>

<script>
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");
export default {
    data() {
        return {
            userInput: "",
            messages: [],
            isOpen: false,
        };
    },
    methods: {
        toggleChatbot() {
            this.isOpen = !this.isOpen;
            if (this.isOpen && this.messages.length === 0) {
                this.addGreeting();
            }
        },
        addGreeting() {
            this.messages.push({
                text: "Halo! Saya Chatbot. Silakan ajukan pertanyaan Anda.",
                sender: "bot",
            });
        },
        async sendMessage() {
            if (this.userInput.trim() === "") return;

            this.messages.push({ text: this.userInput, sender: "user" });
            const userMessage = this.userInput;
            this.userInput = "";

            try {
                const response = await fetch("/chatbot", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ message: userMessage }),
                });
                const data = await response.json();
                this.messages.push({ text: data.reply, sender: "bot" });
            } catch (error) {
                console.error(error);
                this.messages.push({
                    text: "Maaf, terjadi kesalahan. Coba lagi nanti.",
                    sender: "bot",
                });
            }
        },
    },
};
</script>

<style scoped>
.chatbot-floating {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.chatbot-container {
    width: 350px;
    max-height: 500px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chatbot-header {
    background-color: #007bff;
    color: white;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbot-header .close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

.chatbot-body {
    flex-grow: 1;
    padding: 10px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.chat-bubble {
    margin-bottom: 10px;
    max-width: 80%;
    padding: 10px;
    border-radius: 15px;
    line-height: 1.5;
    display: inline-block;
}

.chat-bubble.bot {
    align-self: flex-start;
    background-color: #f8d7da;
    color: #721c24;
}

.chat-bubble.user {
    align-self: flex-end;
    background-color: #d1e7dd;
    color: #0f5132;
}

.chatbot-footer {
    display: flex;
    padding: 10px;
    border-top: 1px solid #ddd;
}

.chatbot-footer input {
    flex-grow: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.chatbot-footer button {
    padding: 8px 12px;
    margin-left: 8px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.chatbot-footer button:hover {
    background-color: #0056b3;
}

.chatbot-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #007bff;
    color: white;
    font-size: 24px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.chatbot-toggle:hover {
    background-color: #0056b3;
}
</style>
