<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Semantic memory demo</title>
    <style>
        :root {
            --bg: #f6f6f8; --panel: #ffffff; --text: #1f2328; --muted: #6b7280; --line: #e6e7eb;
            --accent: #635bdf; --accent-soft: #eeedfd; --hover: #f2f2f6; --danger: #d93025;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #141418; --panel: #1c1c22; --text: #e8eaed; --muted: #9aa0a6; --line: #2c2c34;
                --accent: #8f88ff; --accent-soft: #2b2a42; --hover: #24242c; --danger: #f28b82;
            }
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body { margin: 0; font: 14px/1.55 Inter, system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); }
        button, select, textarea { font: inherit; color: inherit; }
        button { cursor: pointer; }

        .app { display: grid; grid-template-columns: 260px minmax(0, 1fr) 300px; height: 100vh; }
        .sidebar, .memory { background: var(--panel); display: flex; flex-direction: column; min-height: 0; }
        .sidebar { border-right: 1px solid var(--line); }
        .memory { border-left: 1px solid var(--line); }

        .brand { padding: 16px 16px 12px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .brand .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--accent); }
        .account { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin: 0 12px 12px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; }
        .account .who { min-width: 0; }
        .account .name { display: block; font-weight: 600; }
        .account .email { display: block; font-size: 12px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .logout { display: block; padding: 0; border: 0; background: none; }
        .logout button { border: 0; background: none; color: var(--muted); font-size: 12px; padding: 0; }
        .logout button:hover { color: var(--danger); }
        .new-chat { margin: 0 12px 12px; padding: 8px 12px; border: 1px solid var(--line); border-radius: 8px; background: transparent; text-align: left; }
        .new-chat:hover { background: var(--hover); }
        .section-title { padding: 8px 16px 4px; font-size: 11px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: var(--muted); }
        #conversations { list-style: none; margin: 0; padding: 0 8px 12px; overflow-y: auto; flex: 1; }
        #conversations button { width: 100%; text-align: left; padding: 8px 10px; border: 0; border-radius: 8px; background: transparent; display: block; }
        #conversations button:hover { background: var(--hover); }
        #conversations button.active { background: var(--accent-soft); }
        #conversations .title { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        #conversations .time { display: block; font-size: 12px; color: var(--muted); }
        .sidebar-footer { padding: 12px 16px; border-top: 1px solid var(--line); font-size: 13px; }
        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .chat { display: flex; flex-direction: column; min-height: 0; }
        .chat header { padding: 14px 24px; border-bottom: 1px solid var(--line); background: var(--panel); }
        .chat header h1 { margin: 0; font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        #messages { flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 16px; }
        .msg { max-width: 760px; width: fit-content; padding: 10px 14px; border-radius: 14px; overflow-wrap: anywhere; }
        .msg.user { align-self: flex-end; background: var(--accent); color: #fff; white-space: pre-wrap; border-bottom-right-radius: 4px; }
        .msg.assistant { align-self: flex-start; background: var(--panel); border: 1px solid var(--line); border-bottom-left-radius: 4px; }
        .msg.assistant pre { background: var(--bg); padding: 10px 12px; border-radius: 8px; overflow-x: auto; }
        .msg.assistant code { font-size: 13px; }
        .msg.assistant > :first-child { margin-top: 0; } .msg.assistant > :last-child { margin-bottom: 0; }
        .msg.error { align-self: center; color: var(--danger); font-size: 13px; }
        .typing { color: var(--muted); }
        .empty-chat { margin: auto; text-align: center; max-width: 440px; color: var(--muted); }
        .empty-chat h2 { color: var(--text); font-size: 18px; margin: 0 0 6px; }
        .suggestions { display: flex; flex-direction: column; gap: 8px; margin-top: 16px; }
        .suggestions button { padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; background: var(--panel); text-align: left; }
        .suggestions button:hover { border-color: var(--accent); }

        #form { display: flex; gap: 8px; padding: 16px 24px 20px; border-top: 1px solid var(--line); background: var(--panel); }
        textarea { flex: 1; resize: none; height: 46px; padding: 12px; border: 1px solid var(--line); border-radius: 10px; background: var(--bg); }
        textarea:focus { outline: 2px solid var(--accent-soft); border-color: var(--accent); }
        .send { border: 0; background: var(--accent); color: #fff; border-radius: 10px; padding: 0 20px; font-weight: 600; }
        .send:disabled { opacity: .5; cursor: wait; }

        .memory header { display: flex; justify-content: space-between; align-items: center; padding: 16px; }
        .memory h2 { margin: 0; font-size: 14px; }
        .memory .hint { padding: 0 16px 8px; font-size: 12px; color: var(--muted); }
        .clear { border: 1px solid var(--line); background: transparent; border-radius: 6px; padding: 3px 10px; font-size: 12px; }
        .clear:hover { border-color: var(--danger); color: var(--danger); }
        #memories { list-style: none; margin: 0; padding: 0 12px 16px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 8px; }
        #memories li { padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; }
        #memories .meta { display: block; margin-top: 4px; font-size: 12px; color: var(--muted); }
        #memories li.superseded { opacity: .6; }
        #memories li.superseded .content { text-decoration: line-through; }
        .empty { color: var(--muted); font-size: 13px; padding: 4px 4px; border: 0 !important; }

        @media (max-width: 1000px) {
            .app { grid-template-columns: 220px minmax(0, 1fr); }
            .memory { display: none; }
        }
        @media (max-width: 680px) {
            .app { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand"><span class="dot"></span> Memory demo</div>
        <div class="account">
            <div class="who">
                <span class="name">{{ auth()->user()->name }}</span>
                <span class="email">{{ auth()->user()->email }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="logout">
                @csrf
                <button type="submit">Log out</button>
            </form>
        </div>
        <button type="button" class="new-chat" id="new">+ New chat</button>
        <div class="section-title">Conversations</div>
        <ul id="conversations"></ul>
        <div class="sidebar-footer"><a href="/trail" target="_blank">Open Trail →</a></div>
    </aside>

    <main class="chat">
        <header><h1 id="title">New chat</h1></header>
        <div id="messages"></div>
        <form id="form">
            <textarea id="input" placeholder="Message… (Shift+Enter for a new line)" required></textarea>
            <button class="send" id="send">Send</button>
        </form>
    </main>

    <aside class="memory">
        <header>
            <h2>What it remembers</h2>
            <button type="button" class="clear" id="clear">Clear</button>
        </header>
        <div class="hint">Facts about {{ auth()->user()->name }}, updated every 3 seconds.</div>
        <ul id="memories"><li class="empty">No memories yet.</li></ul>
    </aside>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
<script>
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
    };
    const $ = (id) => document.getElementById(id);
    const suggestions = [
        'Give me a short script that prints the current git branch, and the command to run it.',
        'Please remember that I deploy with Laravel Forge and write tests with Pest.',
        'I switched to a Mac last week. How do I set up a Python virtual environment now?',
    ];
    let conversationId = null;

    function timeAgo(date) {
        const seconds = Math.round((Date.now() - new Date(date)) / 1000);
        if (seconds < 60) return 'just now';
        if (seconds < 3600) return `${Math.round(seconds / 60)} min ago`;
        if (seconds < 86400) return `${Math.round(seconds / 3600)} h ago`;
        return new Date(date).toLocaleDateString();
    }

    function addMessage(role, text) {
        $('messages').querySelector('.empty-chat')?.remove();
        const div = document.createElement('div');
        div.className = `msg ${role}`;
        if (role === 'assistant') div.innerHTML = DOMPurify.sanitize(marked.parse(text));
        else div.textContent = text;
        $('messages').append(div);
        $('messages').scrollTop = $('messages').scrollHeight;
        return div;
    }

    function showEmptyChat() {
        const empty = document.createElement('div');
        empty.className = 'empty-chat';
        empty.innerHTML = '<h2>Start a conversation</h2><div>The assistant remembers what it learns about you across conversations.</div><div class="suggestions"></div>';
        for (const text of suggestions) {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = text;
            button.onclick = () => { $('input').value = text; $('input').focus(); };
            empty.querySelector('.suggestions').append(button);
        }
        $('messages').replaceChildren(empty);
    }

    async function loadConversations() {
        const response = await fetch('/conversations', { headers });
        if (!response.ok) return;
        const list = $('conversations');
        list.replaceChildren();
        const conversations = await response.json();
        if (conversations.length === 0) {
            list.innerHTML = '<li class="empty">No conversations yet.</li>';
        }
        for (const conversation of conversations) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = conversation.id === conversationId ? 'active' : '';
            button.innerHTML = '<span class="title"></span><span class="time"></span>';
            button.querySelector('.title').textContent = conversation.title;
            button.querySelector('.time').textContent = timeAgo(conversation.updated_at);
            button.onclick = () => openConversation(conversation);
            const li = document.createElement('li');
            li.append(button);
            list.append(li);
        }
    }

    async function openConversation(conversation) {
        conversationId = conversation.id;
        $('title').textContent = conversation.title;
        $('messages').replaceChildren();
        loadConversations();
        const response = await fetch(`/conversations/${conversation.id}`, { headers });
        if (!response.ok) return;
        for (const message of await response.json()) addMessage(message.role, message.content);
    }

    function newChat() {
        conversationId = null;
        $('title').textContent = 'New chat';
        showEmptyChat();
        loadConversations();
        $('input').focus();
    }

    async function send(event) {
        event.preventDefault();
        const message = $('input').value.trim();
        if (!message || $('send').disabled) return;

        addMessage('user', message);
        $('input').value = '';
        $('send').disabled = true;
        const pending = addMessage('assistant', '');
        pending.innerHTML = '<span class="typing">Thinking…</span>';

        try {
            const response = await fetch('/chat', {
                method: 'POST', headers,
                body: JSON.stringify({ message, conversation_id: conversationId }),
            });
            if (!response.ok) throw new Error(`Request failed (${response.status})`);
            const data = await response.json();
            pending.innerHTML = DOMPurify.sanitize(marked.parse(data.reply));
            if (!conversationId) {
                conversationId = data.conversation_id;
            }
            await loadConversations();
            $('title').textContent = document.querySelector('#conversations .active .title')?.textContent ?? 'Chat';
        } catch (error) {
            pending.remove();
            addMessage('error', error.message);
        } finally {
            $('send').disabled = false;
            $('input').focus();
        }
    }

    async function loadMemories() {
        const response = await fetch('/memories', { headers });
        if (!response.ok) return;
        const memories = await response.json();
        const list = $('memories');
        list.replaceChildren();
        if (memories.length === 0) {
            list.innerHTML = '<li class="empty">No memories yet.</li>';
            return;
        }
        for (const memory of memories) {
            const li = document.createElement('li');
            if (memory.superseded_at) li.className = 'superseded';
            const content = document.createElement('span');
            content.className = 'content';
            content.textContent = `[${memory.id}] ${memory.content}`;
            const meta = document.createElement('span');
            meta.className = 'meta';
            const replaced = memory.superseded_at
                ? ` · outdated${memory.superseded_by ? `, replaced by [${memory.superseded_by}]` : ''}`
                : '';
            meta.textContent = `${memory.source ?? 'no source'} · ${timeAgo(memory.created_at ?? memory.updated_at)}${replaced}`;
            li.append(content, meta);
            list.append(li);
        }
    }

    $('form').addEventListener('submit', send);
    $('input').addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) send(e); });
    $('new').addEventListener('click', newChat);
    $('clear').addEventListener('click', async () => {
        if (!confirm('Delete all memories for this user?')) return;
        await fetch('/memories', { method: 'DELETE', headers });
        loadMemories();
    });

    newChat();
    loadMemories();
    setInterval(loadMemories, 3000);
</script>
</body>
</html>
