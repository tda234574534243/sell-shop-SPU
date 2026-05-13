<?php /* Chat bubble UI using Tailwind CSS */ ?>
<style>
    /* Expanded chat window styling */
    #chatWindow.chat-expanded{
        width: calc(100vw - 48px) !important;
        right: 24px !important;
        bottom: 24px !important;
        max-height: calc(100vh - 48px) !important;
        height: calc(100vh - 48px) !important;
        border-radius: 12px !important;
    }
    #chatWindow .chat-messages-full{
        height: calc(100% - 144px) !important;
    }
</style>

<div id="chatLauncher" class="fixed right-4 bottom-4 z-50 flex items-end gap-3 touch-none cursor-grab" style="touch-action:none;">
    <div>
        <button id="openChatBtn" class="bg-gradient-to-r from-indigo-600 to-indigo-500 text-white p-3 rounded-full shadow-md hover:scale-105 transform transition-transform focus:outline-none" title="Chat tư vấn" aria-label="Mở chat">
            <i class="fas fa-robot" aria-hidden="true"></i>
        </button>
    </div>
    <div>
        <a id="openZaloBtn" href="https://zalo.me/0917337576" target="_blank" rel="noopener noreferrer" title="Chat qua Zalo">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo" style="width:46px;height:46px;border-radius:8px;box-shadow:0 6px 12px rgba(0,0,0,0.12);display:block;">
        </a>
    </div>
</div>

<div id="chatWindow" class="hidden fixed right-4 bottom-20 w-80 max-w-[calc(100vw-36px)] max-h-[70vh] z-50 rounded-xl shadow-2xl overflow-hidden font-sans bg-slate-900 text-slate-100" aria-hidden="true">
    <div id="chatHeader" class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-indigo-700 to-indigo-600 text-white" style="touch-action:none; cursor:grab;">
        <img src="/public/image/avatars/assistant.png" onerror="this.style.display='none'" class="w-9 h-9 rounded-md ring-1 ring-white/10 flex-shrink-0" alt="assistant">
        <div class="flex-1 min-w-0">
            <div class="truncate">
                <div class="font-semibold text-sm leading-5 truncate">Trợ lý mua hàng</div>
                <div class="text-xs text-white/80 truncate">Gợi ý & hỗ trợ chọn sản phẩm</div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div id="chatTabs" role="tablist" class="flex gap-1 mr-1">
                <button id="tabChat" role="tab" class="px-3 py-1 text-xs rounded-full bg-white/10 hover:bg-white/20">Chat</button>
                <button id="tabHistory" role="tab" class="px-3 py-1 text-xs rounded-full bg-white/5 hover:bg-white/20">Lịch sử</button>
            </div>
            <button id="newChatBtn" class="p-2 rounded-full bg-white/6 hover:bg-white/10 text-white text-xs" title="Cuộc trò chuyện mới" aria-label="Cuộc trò chuyện mới">
                <i class="fas fa-plus"></i>
            </button>
            <button id="toggleSizeBtn" class="p-2 rounded-full bg-white/6 hover:bg-white/10 text-white text-sm" title="Phóng to/Thu nhỏ" aria-pressed="false" aria-label="Phóng to khung chat">
                <i class="fas fa-expand-alt" aria-hidden="true"></i>
            </button>
            <button id="closeChatBtn" class="p-2 rounded-full bg-white/6 hover:bg-white/10 text-white text-sm" aria-label="Đóng cửa sổ chat">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div id="chatMessages" class="p-3 bg-slate-900 h-80 overflow-auto" role="log" aria-live="polite"></div>
    <div class="composer flex gap-2 items-center px-3 py-3 bg-slate-800 border-t border-slate-700">
        <input id="chatInput" class="flex-1 px-3 py-2 text-sm bg-slate-700 text-slate-100 border border-slate-700 rounded-xl focus:outline-none" placeholder="Bạn cần tư vấn gì?" aria-label="Nhập câu hỏi" />
        <button id="chatSendBtn" class="bg-gradient-to-r from-indigo-600 to-indigo-500 text-white px-4 py-2 rounded-xl text-sm">Gửi</button>
    </div>
    <div class="text-xs text-slate-400 px-3 py-2">Ghi chú: Thông tin chỉ mang tính gợi ý.</div>
</div>

<script>
window.CHATBOT_API_URL = window.CHATBOT_API_URL || 'http://localhost:3000/api/chat';
window.CHATBOT_HISTORY_URL = window.CHATBOT_HISTORY_URL || '/sell-shop-SPU/chatbot/get_history.php';
window.CURRENT_USER_ID = <?php echo isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; ?>;

const openBtn = document.getElementById('openChatBtn');
const closeBtn = document.getElementById('closeChatBtn');
const chatWindow = document.getElementById('chatWindow');
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const chatSendBtn = document.getElementById('chatSendBtn');
const tabChat = document.getElementById('tabChat');
const tabHistory = document.getElementById('tabHistory');
const newChatBtn = document.getElementById('newChatBtn');
const toggleSizeBtn = document.getElementById('toggleSizeBtn');
const composerEl = document.querySelector('.composer');

openBtn.addEventListener('click', ()=>{ chatWindow.classList.remove('hidden'); chatWindow.setAttribute('aria-hidden','false'); chatInput.focus(); });
closeBtn.addEventListener('click', ()=>{ chatWindow.classList.add('hidden'); chatWindow.setAttribute('aria-hidden','true'); });

if (toggleSizeBtn){
    toggleSizeBtn.addEventListener('click', ()=>{
        const expanded = chatWindow.classList.toggle('chat-expanded');
        toggleSizeBtn.setAttribute('aria-pressed', expanded ? 'true' : 'false');
        const icon = toggleSizeBtn.querySelector('i');
        if (icon){
            icon.classList.toggle('fa-expand-alt', !expanded);
            icon.classList.toggle('fa-compress-alt', expanded);
        }
        // when expanded, ensure messages area grows
        if (expanded) {
            chatMessages.classList.add('chat-messages-full');
        } else {
            chatMessages.classList.remove('chat-messages-full');
        }
    });
}

// Draggable behavior for launcher and chat window (pointer events)
function makeDraggable(containerEl, handleEl, opts){
    opts = opts || {};
    let dragging = false;
    let potential = false;
    let interactive = false;
    let startX=0, startY=0, startLeft=0, startTop=0;
    let pointerId = null;

    function startDragging(e){
        dragging = true;
        try{ handleEl.setPointerCapture(pointerId || e.pointerId); }catch(_){}
        const rect = containerEl.getBoundingClientRect();
        containerEl.style.right = 'auto';
        containerEl.style.bottom = 'auto';
        containerEl.style.left = rect.left + 'px';
        containerEl.style.top = rect.top + 'px';
        startLeft = rect.left; startTop = rect.top;
        containerEl.style.cursor = 'grabbing';
        handleEl.style.cursor = 'grabbing';
    }

    function onPointerDown(e){
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        pointerId = e.pointerId;
        potential = true;
        interactive = false;
        try{ if (e.target && e.target.closest && e.target.closest('button, a, input, textarea, select, label')) interactive = true; }catch(err){}
        startX = e.clientX; startY = e.clientY;
        const rect = containerEl.getBoundingClientRect();
        startLeft = rect.left; startTop = rect.top;
    }

    function onPointerMove(e){
        if (!potential && !dragging) return;
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        if (!dragging){
            if (interactive){
                if (Math.hypot(dx,dy) > 6){
                    startDragging(e);
                } else return;
            } else {
                startDragging(e);
            }
        }
        // now dragging
        e.preventDefault();
        let nx = startLeft + dx;
        let ny = startTop + dy;
        const vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
        const vh = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        const w = containerEl.offsetWidth; const h = containerEl.offsetHeight;
        nx = Math.min(Math.max(6, nx), vw - w - 6);
        ny = Math.min(Math.max(6, ny), vh - h - 6);
        containerEl.style.left = nx + 'px';
        containerEl.style.top = ny + 'px';
    }

    function onPointerUp(e){
        potential = false;
        if (!dragging) return;
        dragging = false;
        try{ handleEl.releasePointerCapture(pointerId || e.pointerId); }catch(_){}
        containerEl.style.cursor = '';
        handleEl.style.cursor = '';
        if (opts.persistKey){
            localStorage.setItem(opts.persistKey, JSON.stringify({ left: containerEl.style.left, top: containerEl.style.top }));
        }
    }

    handleEl.addEventListener('pointerdown', onPointerDown);
    window.addEventListener('pointermove', onPointerMove);
    window.addEventListener('pointerup', onPointerUp);

    // restore persisted position
    if (opts.persistKey){
        try{
            const raw = localStorage.getItem(opts.persistKey);
            if (raw){
                const pos = JSON.parse(raw);
                if (pos.left) containerEl.style.left = pos.left;
                if (pos.top) containerEl.style.top = pos.top;
                containerEl.style.right = 'auto'; containerEl.style.bottom = 'auto';
            }
        }catch(e){/* ignore */}
    }
}

// attach draggable: launcher (drag whole launcher) and chatWindow (drag by header)
const chatLauncher = document.getElementById('chatLauncher');
if (chatLauncher){
    makeDraggable(chatLauncher, chatLauncher, { persistKey: 'chat.launcher.pos' });
}
if (chatWindow){
    const chatHeader = document.getElementById('chatHeader');
    if (chatHeader) makeDraggable(chatWindow, chatHeader, { persistKey: 'chat.window.pos' });
}

function setActiveTab(t){
    if (t === 'history'){
        tabHistory.classList.add('bg-white/20'); tabChat.classList.remove('bg-white/20');
        composerEl.classList.add('hidden');
        loadHistory(true);
    } else {
        tabChat.classList.add('bg-white/20'); tabHistory.classList.remove('bg-white/20');
        composerEl.classList.remove('hidden');
    }
}

tabHistory.addEventListener('click', ()=> setActiveTab('history'));
tabChat.addEventListener('click', ()=> setActiveTab('chat'));

newChatBtn.addEventListener('click', ()=>{
    window.CHAT_SESSION_ID = (function(){ const a = new Uint8Array(12); window.crypto.getRandomValues(a); return Array.from(a).map(x=>x.toString(16).padStart(2,'0')).join(''); })();
    chatMessages.innerHTML = '';
    setActiveTab('chat');
    chatInput.focus();
});

function escapeHtml(s){
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function linkify(text){
    return text.replace(/(https?:\/\/[^\s]+)/g, (m)=>`<a href="${m}" target="_blank" rel="noopener noreferrer" class="underline text-blue-600">${m}</a>`);
}

function renderReplyAsHTML(text){
    if (!text) return '';
    let out = escapeHtml(text);
    if (/^\s*[-\*]\s+/m.test(out)){
        out = out.split(/\r?\n/).map(line=>{
            if (/^\s*[-\*]\s+/.test(line)) return `<li>${line.replace(/^\s*[-\*]\s+/,'')}</li>`;
            return `<p>${line}</p>`;
        }).join('');
        out = `<ul class="pl-4 my-1">${out}</ul>`;
    } else {
        out = out.replace(/\r?\n/g,'<br>');
    }
    out = linkify(out);
    return out;
}

function appendMessage(who, text){
    const div = document.createElement('div');
    div.className = 'flex gap-2 mb-3 ' + (who==='user' ? 'justify-end items-end' : 'justify-start items-start');
    const avatar = document.createElement('div'); avatar.className = 'w-8 h-8 rounded-full flex-shrink-0';
    const bubble = document.createElement('div'); bubble.className = 'px-3 py-2 rounded-xl max-w-[78%] text-sm';

    if (who === 'user'){
        avatar.style.background = 'linear-gradient(90deg,#4f46e5,#6366f1)';
        bubble.classList.add('bg-gradient-to-r','from-indigo-600','to-indigo-500','text-white','rounded-br-sm');
        bubble.textContent = text;
    } else {
        avatar.style.background = '#0f1724';
        bubble.classList.add('bg-slate-800','text-slate-100','ring','ring-slate-700');
        bubble.innerHTML = renderReplyAsHTML(text);
    }

    div.appendChild(avatar);
    div.appendChild(bubble);
    chatMessages.appendChild(div);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    return div;
}

let _loadingHistory = false;
async function loadHistory(){
    if (_loadingHistory) return;
    _loadingHistory = true;
    tabHistory.disabled = true;
    try{
        const r = await fetch(window.CHATBOT_HISTORY_URL, { credentials: 'same-origin' });
        if (r.status === 401) {
            chatMessages.innerHTML = '<div class="p-3 text-gray-500">Vui lòng đăng nhập để xem lịch sử chat.</div>';
            return;
        }
        if (!r.ok) {
            chatMessages.innerHTML = '<div class="p-3 text-gray-500">Không thể tải lịch sử.</div>';
            return;
        }
        const j = await r.json();
        const msgs = j.messages || [];
        chatMessages.innerHTML = '';
        for (const m of msgs) {
            const who = (m.role === 'user') ? 'user' : 'bot';
            appendMessage(who, m.message || '');
        }
    } catch(e){
        console.warn('Could not load chat history', e);
    } finally {
        _loadingHistory = false;
        tabHistory.disabled = false;
    }
}

openBtn.addEventListener('click', ()=>{ 
    if (!window.CHAT_SESSION_ID) {
        window.CHAT_SESSION_ID = (function(){ const a = new Uint8Array(12); window.crypto.getRandomValues(a); return Array.from(a).map(x=>x.toString(16).padStart(2,'0')).join(''); })();
    }
    setActiveTab('chat');
});

function makeTypingElement(){
    const div = document.createElement('div'); div.className='flex gap-2 mb-3 justify-start items-start';
    const avatar = document.createElement('div'); avatar.className='w-8 h-8 rounded-full flex-shrink-0'; avatar.style.background='#e6eef6';
    const bubble = document.createElement('div'); bubble.className='px-3 py-2 rounded-xl max-w-[78%] text-sm bg-gray-100';
    const typing = document.createElement('span'); typing.className='inline-flex gap-1';
    typing.innerHTML = '<span class="w-2 h-2 bg-slate-500 rounded-full animate-bounce"></span><span class="w-2 h-2 bg-slate-500 rounded-full animate-bounce" style="animation-delay:.12s"></span><span class="w-2 h-2 bg-slate-500 rounded-full animate-bounce" style="animation-delay:.24s"></span>';
    bubble.appendChild(typing);
    div.appendChild(avatar); div.appendChild(bubble);
    return div;
}

async function sendMessage(){
    const v = chatInput.value.trim(); if(!v) return;
    appendMessage('user', v);
    chatInput.value='';

    const typingEl = makeTypingElement();
    chatMessages.appendChild(typingEl);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    try{
        const r = await fetch(window.CHATBOT_API_URL, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ message: v, user_id: (window.CURRENT_USER_ID || null), session_id: (window.CHAT_SESSION_ID || null) }) });
        const j = await r.json();
        typingEl.remove();
        if (j.reply) {
            appendMessage('bot', j.reply);
        } else if (j.error) {
            appendMessage('bot', 'Lỗi: ' + (j.message || j.error));
        } else {
            appendMessage('bot', 'Không nhận được phản hồi.');
        }
    } catch(e){
        typingEl.remove();
        appendMessage('bot', 'Lỗi kết nối tới chatbot');
    }
}

chatSendBtn.addEventListener('click', sendMessage);
chatInput.addEventListener('keydown', (e)=>{ if (e.key === 'Enter') sendMessage(); });
</script>
