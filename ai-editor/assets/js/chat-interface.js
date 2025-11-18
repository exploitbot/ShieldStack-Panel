// AI Website Editor - Chat Interface
(function() {
    'use strict';

    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const chatForm = document.getElementById('chatForm');
    const sendButton = document.getElementById('sendButton');

    let sessionId = null;
    let isProcessing = false;

    // Initialize
    init();

    function init() {
        // Load or create session
        loadSession();

        // Set up event listeners
        chatForm.addEventListener('submit', handleSubmit);
        chatInput.addEventListener('keydown', handleKeyDown);

        // Auto-scroll to bottom
        scrollToBottom();
    }

    function handleKeyDown(e) {
        // Send on Ctrl+Enter or Cmd+Enter
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            handleSubmit(e);
        }
    }

    function handleSubmit(e) {
        e.preventDefault();

        const message = chatInput.value.trim();

        if (!message || isProcessing) {
            return;
        }

        // Add user message to chat
        addMessage(message, 'user');

        // Clear input
        chatInput.value = '';

        // Disable input while processing
        setProcessing(true);

        // Send to API
        sendMessage(message);
    }

    function setProcessing(processing) {
        isProcessing = processing;
        sendButton.disabled = processing;
        chatInput.disabled = processing;

        if (processing) {
            sendButton.textContent = 'Processing...';
            showTypingIndicator();
        } else {
            sendButton.textContent = 'Send';
            hideTypingIndicator();
        }
    }

    function addMessage(content, role = 'user', timestamp = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${role}-message`;

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';

        // Handle markdown-like formatting
        let formattedContent = formatContent(content);
        contentDiv.innerHTML = formattedContent;

        messageDiv.appendChild(contentDiv);

        if (timestamp) {
            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            timeDiv.textContent = new Date(timestamp).toLocaleTimeString();
            messageDiv.appendChild(timeDiv);
        }

        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }

    function formatContent(content) {
        // Convert markdown-like syntax to HTML
        let formatted = content;

        // Escape HTML first
        formatted = escapeHtml(formatted);

        // Convert code blocks (```...```)
        formatted = formatted.replace(/```(\w+)?\n?([\s\S]*?)```/g, function(match, lang, code) {
            return '<pre><code>' + code.trim() + '</code></pre>';
        });

        // Convert inline code (`...`)
        formatted = formatted.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Convert file paths (/path/to/file)
        formatted = formatted.replace(/([\/\w\-\.]+\.\w+)/g, function(match) {
            if (match.startsWith('/')) {
                return '<span class="file-path">' + match + '</span>';
            }
            return match;
        });

        // Convert bold (**text**)
        formatted = formatted.replace(/\*\*([^\*]+)\*\*/g, '<strong>$1</strong>');

        // Convert line breaks
        formatted = formatted.replace(/\n/g, '<br>');

        return formatted;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showTypingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'message ai-message';
        indicator.id = 'typingIndicator';
        indicator.innerHTML = `
            <div class="message-content typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        chatMessages.appendChild(indicator);
        scrollToBottom();
    }

    function hideTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
    }

    function scrollToBottom() {
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    }

    function loadSession() {
        // Try to load existing session from localStorage
        sessionId = localStorage.getItem('ai_chat_session_id');

        if (sessionId) {
            // Load chat history
            fetch('api/get-session.php?session_id=' + sessionId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages) {
                        // Load messages into chat
                        data.messages.forEach(msg => {
                            if (msg.role !== 'system') {
                                addMessage(msg.content, msg.role, msg.timestamp);
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Failed to load session:', error);
                });
        }
    }

    function sendMessage(message) {
        const formData = new FormData();
        formData.append('message', message);
        if (sessionId) {
            formData.append('session_id', sessionId);
        }

        fetch('api/chat.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            setProcessing(false);

            if (data.success) {
                // Store session ID
                if (data.session_id && !sessionId) {
                    sessionId = data.session_id;
                    localStorage.setItem('ai_chat_session_id', sessionId);
                }

                // Add AI response
                if (data.response) {
                    addMessage(data.response, 'ai');
                }

                // Show warnings if any
                if (data.warnings && data.warnings.length > 0) {
                    data.warnings.forEach(warning => {
                        addWarning(warning);
                    });
                }

                // Update token usage if provided
                if (data.tokens_used) {
                    updateTokenUsage(data.tokens_used, data.tokens_remaining);
                }
            } else {
                // Show error
                addError(data.error || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            setProcessing(false);
            console.error('Request failed:', error);
            addError('Network error. Please check your connection and try again.');
        });
    }

    function addError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = '⚠️ Error: ' + message;
        chatMessages.appendChild(errorDiv);
        scrollToBottom();
    }

    function addWarning(message) {
        const warningDiv = document.createElement('div');
        warningDiv.className = 'message ai-message';
        warningDiv.innerHTML = `
            <div class="message-content">
                <p>⚠️ <strong>Warning:</strong> ${escapeHtml(message)}</p>
            </div>
        `;
        chatMessages.appendChild(warningDiv);
        scrollToBottom();
    }

    function updateTokenUsage(used, remaining) {
        // Update token display if it exists
        const tokenDisplay = document.querySelector('.tokens-display');
        if (tokenDisplay) {
            // This would update the UI token counter
            console.log('Tokens used:', used, 'Remaining:', remaining);
        }
    }

    // Expose functions for debugging
    window.aiChat = {
        addMessage,
        addError,
        loadSession
    };
})();
