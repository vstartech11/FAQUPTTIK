<template>
    <div class="chatbot-floating">
        <div class="chatbot-container">
            <div class="chatbot-header">
                <h3>Chatbot</h3>
                <button @click="toggleChatbot" class="close-btn">✕</button>
            </div>
            <div v-if="isOpen" class="chatbot-body" ref="chatBody">
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
                    <!-- Display execution time if available -->
                    <div v-if="message.executionTime" class="execution-time">
                        <span
                            >Waktu Eksekusi:
                            {{ message.executionTime }} detik</span
                        >
                    </div>
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

                // Check if execution time is present in the response
                if (data.execution_time) {
                    this.messages.push({
                        text: data.reply,
                        sender: "bot",
                        executionTime: data.execution_time, // Add execution time to message
                    });
                } else {
                    this.messages.push({
                        text: data.reply,
                        sender: "bot",
                    });
                }

                // Automatically scroll to the bottom when a new message is added
                this.scrollToBottom();
            } catch (error) {
                console.error(error);
                this.messages.push({
                    text: "Pesan gagal dikirim ke server.",
                    sender: "bot",
                });
                this.scrollToBottom();
            }
        },
        scrollToBottom() {
            // Scroll to the bottom of the chat container
            this.$nextTick(() => {
                const chatBody = this.$refs.chatBody;
                chatBody.scrollTop = chatBody.scrollHeight;
            });
        },
    },
};
</script>

<style scoped>
/* Chatbot Container Styling */
.chatbot-floating {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.chatbot-container {
    width: 350px;
    max-height: 500px;
    border-radius: 12px;
    background-color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #ddd;
}

/* Header Styling */
.chatbot-header {
    background-color: #007bff;
    color: white;
    padding: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px 12px 0 0;
}

.chatbot-header .close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

/* Chat Bubble Styling */
.chatbot-body {
    flex-grow: 1;
    padding: 12px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.chat-bubble {
    max-width: 80%;
    padding: 12px;
    border-radius: 18px;
    line-height: 1.5;
    display: inline-block;
    margin-bottom: 12px;
}

.chat-bubble.bot {
    align-self: flex-start;
    background-color: #f1f1f1;
    color: #333;
    border-radius: 18px 18px 0 18px;
}

.chat-bubble.user {
    align-self: flex-end;
    background-color: #007bff;
    color: white;
    border-radius: 18px 18px 18px 0;
}

/* Footer Styling */
.chatbot-footer {
    display: flex;
    padding: 12px;
    border-top: 1px solid #ddd;
    background-color: #f8f9fa;
}

.chatbot-footer input {
    flex-grow: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.chatbot-footer button {
    padding: 10px 14px;
    margin-left: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.chatbot-footer button:hover {
    background-color: #0056b3;
}

/* Toggle Button Styling */
.chatbot-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #007bff;
    color: white;
    font-size: 30px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.chatbot-toggle:hover {
    background-color: #0056b3;
}

/* Execution Time Styling */
.execution-time {
    font-size: 0.9rem;
    color: #888;
    margin-top: 6px;
}
</style>
