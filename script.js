document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const messagesContainer = document.getElementById('messages-container');
    const typingIndicator = document.getElementById('typing-indicator');

    if (!chatForm || !chatInput || !messagesContainer) {
        console.error("Critical elements missing");
        return;
    }

    // Auto-resize textarea
    chatInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        if (this.value === '') this.style.height = '56px';
    });

    // Handle Enter key (Shift+Enter for newline)
    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            submitMessage();
        }
    });

    // Handle Submit
    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        submitMessage();
    });

    // Load history
    fetchHistory();

    function scrollToBottom() {
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    async function fetchHistory() {
        try {
            const res = await fetch('fetch_chat.php');
            if (!res.ok) throw new Error("HTTP " + res.status);

            const data = await res.json();
            if (data.status === 'success') {
                messagesContainer.innerHTML = '';
                // Append typing indicator again since we clear innerHTML
                messagesContainer.appendChild(typingIndicator);

                if (data.chats && data.chats.length > 0) {
                    data.chats.forEach(chat => {
                        appendMessage('user', chat.message, false); // false = don't animate
                        appendMessage('ai', chat.response, false);
                    });
                } else {
                    // Start fresh
                    appendMessage('ai', "Hello! I am your AI assistant. How can I help you today?", true);
                }
                scrollToBottom();
            } else {
                console.error('History Error:', data.message);
                // Don't show error to user, just start blank
            }
        } catch (error) {
            console.error('Error fetching history:', error);
            appendMessage('ai', "Could not load history. " + error.message, false);
        }
    }

    async function submitMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        // Clear input
        chatInput.value = '';
        chatInput.style.height = '56px';

        // Show user message
        appendMessage('user', message, true);
        scrollToBottom();

        // Show typing indicator
        showTyping(true);

        try {
            // Call AI API
            const formData = new FormData();
            formData.append('message', message);

            const response = await fetch('ai_api.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                // If the error response is JSON, we still need to parse it to check the code
                // Try to read it, if fails, throw basic error
            }

            const data = await response.json();
            showTyping(false);

            if (data.status === 'success') {
                // Type out AI response
                await typeMessage(data.reply, message);
            } else if (data.code === 'MISSING_API_KEY' || data.code === 'INVALID_API_KEY') {
                // Prompt user to enter key
                appendMessage('ai', "Authentication Error: " + data.message, true);
                if (typeof openSettings === 'function') {
                    setTimeout(openSettings, 1500); // Open settings after showing error
                }
            } else {
                appendMessage('ai', 'API Error: ' + (data.message || 'Unknown error'), true);
            }

        } catch (error) {
            showTyping(false);
            appendMessage('ai', 'Error: ' + error.message, true);
            console.error("Submission error:", error);
        }
    }

    function appendMessage(role, text, animate) {
        if (!text) return;
        const wrapper = document.createElement('div');
        wrapper.className = `message-wrapper ${role}`;
        if (animate) wrapper.classList.add('fade-in');

        const content = document.createElement('div');
        content.className = 'message-content';

        const avatar = document.createElement('div');
        avatar.className = 'avatar';
        avatar.innerHTML = role === 'user' ? '👤' : '🤖';

        const textDiv = document.createElement('div');
        textDiv.className = 'text';
        textDiv.innerText = text;

        content.appendChild(avatar);
        content.appendChild(textDiv);
        wrapper.appendChild(content);

        // Insert before typing indicator
        messagesContainer.insertBefore(wrapper, typingIndicator);
    }

    async function typeMessage(text, originalUserMessage) {
        const wrapper = document.createElement('div');
        wrapper.className = 'message-wrapper ai';

        const content = document.createElement('div');
        content.className = 'message-content';

        const avatar = document.createElement('div');
        avatar.className = 'avatar';
        avatar.innerHTML = '🤖';

        const textDiv = document.createElement('div');
        textDiv.className = 'text';

        content.appendChild(avatar);
        content.appendChild(textDiv);
        wrapper.appendChild(content);
        messagesContainer.insertBefore(wrapper, typingIndicator);

        let i = 0;
        const speed = 15; // Faster typing

        return new Promise((resolve) => {
            function type() {
                if (i < text.length) {
                    textDiv.textContent += text.charAt(i);
                    i++;
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    setTimeout(type, speed);
                } else {
                    // Save to DB after typing is done
                    saveChat(originalUserMessage, text);
                    resolve();
                }
            }
            type();
        });
    }

    function showTyping(show) {
        if (typingIndicator) {
            typingIndicator.style.display = show ? 'flex' : 'none';
            if (show) {
                // Move logic to ensure it's at bottom
                messagesContainer.appendChild(typingIndicator);
            }
            scrollToBottom();
        }
    }

    async function saveChat(msg, response) {
        try {
            const formData = new FormData();
            formData.append('message', msg);
            formData.append('response', response);

            const res = await fetch('save_chat.php', {
                method: 'POST',
                body: formData
            });
            // We don't strictly need to await the result or handle it if it fails silently.
            // But let's log.
            const data = await res.json();
            if (data.status !== 'success') {
                console.warn("Failed to save chat:", data.message);
            }
        } catch (e) {
            console.error("Save chat error:", e);
        }
    }
});
