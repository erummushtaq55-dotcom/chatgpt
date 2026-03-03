<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="chat-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2 class="logo-text">AI Assistant</h2>
            <div style="margin-top: auto;">
                <div style="color: #a0a0a0; margin-bottom: 10px;">
                    Logged in as <br>
                    <strong style="color: white;">
                        <?php echo htmlspecialchars($_SESSION['name']); ?>
                    </strong>
                </div>
                <button onclick="window.location.href='logout.php'" class="logout-btn">Logout</button>
            </div>
        </aside>

        <!-- Main Chat Area -->
        <main class="chat-main">
            <header class="chat-header">
                <div class="logo-text" style="font-size: 1.2rem;">New Chat</div>
                <div>
                    <button onclick="openSettings()" class="settings-btn">⚙️ Settings</button>
                    <!-- Mobile Logout visible only on small screens -->
                    <button onclick="window.location.href='logout.php'" class="logout-btn"
                        style="display: none;">Logout</button>
                </div>
            </header>

            <div id="messages-container" class="messages-container">
                <!-- Messages will be injected here by JS -->

                <div id="typing-indicator" class="typing-indicator">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
            </div>

            <div class="input-area">
                <div class="input-container">
                    <form id="chat-form">
                        <textarea id="chat-input" class="chat-input" placeholder="Send a message..."
                            rows="1"></textarea>
                        <button type="submit" class="send-btn">
                            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                stroke-linecap="round" stroke-linejoin="round" height="20" width="20"
                                xmlns="http://www.w3.org/2000/svg">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Settings Modal -->
    <div id="settings-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeSettings()">&times;</span>
            <h2 style="margin-bottom: 20px; color: var(--primary-color);">API Settings</h2>
            <p style="margin-bottom: 15px; color: #ccc;">Enter your OpenAI API Key to enable intelligence. Keys are
                saved securely in your session.</p>
            <input type="password" id="api-key-input" class="form-input" placeholder="sk-..."
                style="margin-bottom: 20px;">
            <button onclick="saveApiKey()" class="btn-neon">Save Key</button>
            <p id="save-status" style="margin-top: 10px; height: 20px; color: #0f0;"></p>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Modal Logic
        const modal = document.getElementById('settings-modal');
        function openSettings() {
            modal.style.display = 'flex';
        }
        function closeSettings() {
            modal.style.display = 'none';
        }
        window.onclick = function (event) {
            if (event.target == modal) {
                closeSettings();
            }
        }

        async function saveApiKey() {
            const key = document.getElementById('api-key-input').value.trim();
            const status = document.getElementById('save-status');

            if (!key) {
                status.style.color = 'red';
                status.innerText = 'Please enter a key.';
                return;
            }

            const formData = new FormData();
            formData.append('api_key', key);

            try {
                const res = await fetch('save_key.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.status === 'success') {
                    status.style.color = '#0ff0fc';
                    status.innerText = 'Key Saved! You can now chat.';
                    setTimeout(closeSettings, 1500);
                } else {
                    status.style.color = 'red';
                    status.innerText = 'Failed to save.';
                }
            } catch (e) {
                console.error(e);
                status.innerText = 'Error connecting.';
            }
        }
    </script>
</body>

</html>
