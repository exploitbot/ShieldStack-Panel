// AI Website Editor - Multi-session chat interface with website selection
(function() {
    'use strict';

    const config = window.aiEditorConfig || {};
    const endpoints = config.endpoints || {};

    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const chatForm = document.getElementById('chatForm');
    const sendButton = document.getElementById('sendButton');
    const newSessionBtn = document.getElementById('newSessionBtn');
    const newSessionInlineBtn = document.getElementById('newSessionInlineBtn');
    const refreshSessionsBtn = document.getElementById('refreshSessionsBtn');
    const clearSessionBtn = document.getElementById('clearSessionBtn');
    const sessionList = document.getElementById('sessionList');
    const openSessionsTabs = document.getElementById('openSessionsTabs');
    const sessionStatus = document.getElementById('sessionStatus');
    const activeSessionLabel = document.getElementById('activeSessionLabel');

    let isProcessing = false;
    let sessions = [];
    let openSessions = [];
    let activeSessionId = null;

    const selectedWebsiteId = config.selectedWebsiteId;
    const selectedWebsiteName = config.selectedWebsiteName || 'your website';

    // Early exit if website is missing
    if (!selectedWebsiteId) {
        disableInput('Please choose a website to start chatting.');
        return;
    }

    init();

    function init() {
        bindEvents();
        renderWelcomeMessage();
        fetchSessions().then(() => {
            ensureActiveSession();
        });
    }

    function bindEvents() {
        chatForm.addEventListener('submit', handleSubmit);
        chatInput.addEventListener('keydown', handleKeyDown);

        if (newSessionBtn) newSessionBtn.addEventListener('click', () => createSession(true));
        if (newSessionInlineBtn) newSessionInlineBtn.addEventListener('click', () => createSession(true));
        if (refreshSessionsBtn) refreshSessionsBtn.addEventListener('click', () => fetchSessions(true));
        if (clearSessionBtn) clearSessionBtn.addEventListener('click', clearActiveSession);

        sessionList.addEventListener('click', handleSessionListClick);
        openSessionsTabs.addEventListener('click', handleOpenTabsClick);
    }

    function getStorageKey() {
        return `ai_chat_session_id_${selectedWebsiteId}`;
    }

    function handleKeyDown(e) {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            handleSubmit(e);
        }
    }

    async function handleSubmit(e) {
        e.preventDefault();
        const message = chatInput.value.trim();

        if (!message || isProcessing) {
            return;
        }

        setProcessing(true);
        addMessage(message, 'user');
        chatInput.value = '';

        try {
            if (!activeSessionId) {
                await createSession(false);
            }
            await sendMessage(message);
            await fetchSessions();
        } catch (error) {
            addError(error.message || 'An error occurred. Please try again.');
        } finally {
            setProcessing(false);
        }
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

    function renderWelcomeMessage() {
        chatMessages.innerHTML = '';
        addMessage(`I'm ready to help edit ${selectedWebsiteName}. Open or start a chat session to begin.`, 'ai');
    }

    function disableInput(reason) {
        addError(reason);
        chatInput.disabled = true;
        sendButton.disabled = true;
    }

    async function fetchSessions(showToast = false) {
        if (!endpoints.sessions) return;
        try {
            const res = await fetch(`${endpoints.sessions}?website_id=${selectedWebsiteId}`);
            const data = await res.json();
            if (!data.success) {
                throw new Error(data.error || 'Unable to load sessions.');
            }
            sessions = data.sessions || [];
            renderSessionList();
            syncOpenSessions();
            if (showToast) {
                setStatus('Sessions refreshed.');
            }
        } catch (err) {
            addError(err.message);
        }
    }

    function ensureActiveSession() {
        const storedId = localStorage.getItem(getStorageKey());
        const storedExists = sessions.find((s) => s.session_id === storedId);

        if (storedExists) {
            openSession(storedId);
            return;
        }

        if (sessions.length > 0) {
            openSession(sessions[0].session_id);
        } else {
            createSession(true);
        }
    }

    function renderSessionList() {
        if (!sessionList) return;
        sessionList.innerHTML = '';

        if (!sessions.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted';
            empty.textContent = 'No sessions yet. Start a new one to begin.';
            sessionList.appendChild(empty);
            return;
        }

        sessions.forEach((session) => {
            const item = document.createElement('div');
            item.className = 'session-list-item' + (session.session_id === activeSessionId ? ' active' : '');
            item.dataset.sessionId = session.session_id;
            item.innerHTML = `
                <div class="session-list-header">
                    <div>
                        <div class="session-title">${escapeHtml(session.session_name || 'Untitled Chat')}</div>
                        <div class="session-meta">
                            <span>${formatTimestamp(session.last_message_at || session.created_at)}</span>
                            <span>${session.total_tokens_used} tokens</span>
                        </div>
                    </div>
                    <div class="session-actions">
                        <button class="btn btn-sm btn-ghost js-open-session" data-session="${session.session_id}">Open</button>
                        <button class="btn btn-sm btn-link js-clear-session" data-session="${session.session_id}">Clear</button>
                    </div>
                </div>
                <div class="session-preview">${escapeHtml(session.last_message_preview || 'No messages yet')}</div>
            `;
            sessionList.appendChild(item);
        });
    }

    function handleSessionListClick(e) {
        const sessionId = e.target.dataset.session;
        if (e.target.classList.contains('js-open-session')) {
            openSession(sessionId);
        }
        if (e.target.classList.contains('js-clear-session')) {
            clearSession(sessionId);
        }
    }

    function handleOpenTabsClick(e) {
        const sessionId = e.target.dataset.session;
        if (e.target.classList.contains('open-tab')) {
            openSession(sessionId);
        }
        if (e.target.classList.contains('close-tab')) {
            closeOpenSession(sessionId);
        }
    }

    function syncOpenSessions() {
        openSessions = openSessions
            .map((session) => sessions.find((s) => s.session_id === session.session_id) || session)
            .filter((session) => sessions.find((s) => s.session_id === session.session_id));

        renderOpenTabs();

        if (activeSessionId && !sessions.find((s) => s.session_id === activeSessionId)) {
            activeSessionId = null;
            openSessionFromListFallback();
        }
    }

    function openSessionFromListFallback() {
        if (sessions.length) {
            openSession(sessions[0].session_id);
        } else {
            renderWelcomeMessage();
        }
    }

    function renderOpenTabs() {
        if (!openSessionsTabs) return;
        openSessionsTabs.innerHTML = '';

        if (!openSessions.length) {
            const empty = document.createElement('div');
            empty.className = 'open-tab empty';
            empty.textContent = 'No open sessions. Select or create one.';
            openSessionsTabs.appendChild(empty);
            return;
        }

        openSessions.forEach((session) => {
            const tab = document.createElement('button');
            tab.className = 'open-tab' + (session.session_id === activeSessionId ? ' active' : '');
            tab.dataset.session = session.session_id;
            tab.innerHTML = `
                <span class="tab-title">${escapeHtml(session.session_name || 'Chat')}</span>
                <span class="close-tab" data-session="${session.session_id}">&times;</span>
            `;
            openSessionsTabs.appendChild(tab);
        });
    }

    async function openSession(sessionId) {
        if (!sessionId) return;

        const session = sessions.find((s) => s.session_id === sessionId);
        if (!session) {
            addError('Session not found.');
            return;
        }

        if (!openSessions.find((s) => s.session_id === sessionId)) {
            openSessions.push(session);
        }

        activeSessionId = sessionId;
        localStorage.setItem(getStorageKey(), sessionId);
        renderOpenTabs();
        setActiveSessionLabel(session.session_name);
        await loadSession(sessionId);
        renderSessionList();
    }

    function closeOpenSession(sessionId) {
        openSessions = openSessions.filter((s) => s.session_id !== sessionId);
        if (sessionId === activeSessionId) {
            activeSessionId = null;
            openSessionFromListFallback();
        }
        renderOpenTabs();
    }

    async function createSession(showGreeting = true) {
        if (!endpoints.sessions) return;
        try {
            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('website_id', selectedWebsiteId);

            const res = await fetch(endpoints.sessions, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (!data.success) {
                throw new Error(data.error || 'Unable to create session.');
            }

            const session = data.session;
            sessions.unshift(session);
            openSessions.push(session);
            activeSessionId = session.session_id;
            localStorage.setItem(getStorageKey(), session.session_id);
            renderSessionList();
            renderOpenTabs();
            if (showGreeting) {
                await loadSession(session.session_id);
            }
            setActiveSessionLabel(session.session_name);
            setStatus('New session created.');
        } catch (err) {
            addError(err.message);
        }
    }

    async function clearSession(sessionId) {
        if (!endpoints.sessions || !sessionId) return;
        const confirmClear = window.confirm('Clear this chat session? This removes the messages but keeps the session.');
        if (!confirmClear) return;

        try {
            const formData = new FormData();
            formData.append('action', 'clear');
            formData.append('session_id', sessionId);
            formData.append('website_id', selectedWebsiteId);

            const res = await fetch(endpoints.sessions, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (!data.success) {
                throw new Error(data.error || 'Unable to clear session.');
            }

            // Reset local view
            await fetchSessions();
            if (sessionId === activeSessionId) {
                await openSession(sessionId);
            }
            setStatus('Session cleared.');
        } catch (err) {
            addError(err.message);
        }
    }

    function clearActiveSession() {
        if (!activeSessionId) {
            addError('Open a session before clearing.');
            return;
        }
        clearSession(activeSessionId);
    }

    async function loadSession(sessionId) {
        if (!endpoints.getSession) return;
        chatMessages.innerHTML = '<p class="text-muted">Loading session...</p>';
        try {
            const res = await fetch(`${endpoints.getSession}?session_id=${sessionId}&website_id=${selectedWebsiteId}`);
            const data = await res.json();
            if (!data.success) {
                throw new Error(data.error || 'Unable to load session.');
            }

            chatMessages.innerHTML = '';
            const messages = data.messages || [];
            if (!messages.length) {
                renderWelcomeMessage();
            } else {
                messages.forEach(msg => {
                    if (msg.role !== 'system') {
                        addMessage(msg.content, msg.role, msg.timestamp);
                    }
                });
            }
        } catch (err) {
            addError(err.message);
        }
    }

    async function sendMessage(message) {
        const formData = new FormData();
        formData.append('message', message);
        formData.append('website_id', selectedWebsiteId);

        if (activeSessionId) {
            formData.append('session_id', activeSessionId);
            const activeSession = sessions.find((s) => s.session_id === activeSessionId);
            if (activeSession && activeSession.session_name) {
                formData.append('session_name', activeSession.session_name);
            }
        }

        const res = await fetch(endpoints.chat, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        hideTypingIndicator();

        if (!data.success) {
            throw new Error(data.error || 'An error occurred.');
        }

        if (data.session_id && data.session_id !== activeSessionId) {
            activeSessionId = data.session_id;
            localStorage.setItem(getStorageKey(), data.session_id);
        }

        if (data.session_name) {
            const open = sessions.find((s) => s.session_id === activeSessionId);
            if (open) {
                open.session_name = data.session_name;
            }
            setActiveSessionLabel(data.session_name);
        }

        if (data.response) {
            addMessage(data.response, 'ai');
        }

        if (data.warnings && data.warnings.length > 0) {
            data.warnings.forEach(warning => addWarning(warning));
        }
    }

    function addMessage(content, role = 'user', timestamp = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${role}-message`;

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';

        const formattedContent = formatContent(content);
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
        let formatted = escapeHtml(content);

        formatted = formatted.replace(/```(\w+)?\n?([\s\S]*?)```/g, function(match, lang, code) {
            return '<pre><code>' + code.trim() + '</code></pre>';
        });

        formatted = formatted.replace(/`([^`]+)`/g, '<code>$1</code>');
        formatted = formatted.replace(/\*\*([^\*]+)\*\*/g, '<strong>$1</strong>');
        formatted = formatted.replace(/\n/g, '<br>');

        return formatted;
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

    function addError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = '⚠️ Error: ' + message;
        chatMessages.appendChild(errorDiv);
        scrollToBottom();
        setStatus(message);
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

    function setStatus(message) {
        if (sessionStatus) {
            sessionStatus.textContent = message || '';
        }
    }

    function setActiveSessionLabel(name) {
        if (activeSessionLabel) {
            activeSessionLabel.textContent = name ? `Active session: ${name}` : '';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatTimestamp(ts) {
        if (!ts) return 'Just now';
        return new Date(ts).toLocaleString();
    }
})();
