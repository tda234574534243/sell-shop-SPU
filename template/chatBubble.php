<?php /* Chat bubble UI using Tailwind CSS */ ?>

<div class="fixed right-4 bottom-4 z-50 flex items-end gap-3">
    <div>
        <button id="openChatBtn" class="bg-green-600 text-white p-3 rounded-full shadow-lg hover:bg-green-700 focus:outline-none" title="Chat tư vấn">
            <i class="fas fa-comments"></i>
        </button>
    </div>
    <div>
        <a id="openZaloBtn" href="https://zalo.me/0917337576" target="_blank" rel="noopener noreferrer" title="Chat qua Zalo">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo" style="width:46px;height:46px;border-radius:8px;box-shadow:0 6px 12px rgba(0,0,0,0.12);display:block;">
        </a>
    </div>
</div>

<div id="chatWindow" class="hidden fixed right-4 bottom-20 w-80 max-w-[calc(100vw-36px)] max-h-[70vh] z-50 rounded-lg shadow-xl overflow-hidden font-sans bg-white" aria-hidden="true">
    <div class="flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-green-700 to-green-800 text-white">
        <img src="/public/image/avatars/assistant.png" onerror="this.style.display='none'" class="w-8 h-8 rounded-full" alt="assistant">
        <div class="flex-1">
            <div class="font-semibold">Trợ lý mua hàng</div>
            <div class="text-sm text-white/90">Gợi ý + hỗ trợ chọn sản phẩm</div>
        </div>
        <div class="flex items-center gap-2">
            <div id="chatTabs" class="flex gap-2 mr-1">
                <button id="tabChat" class="px-2 py-1 text-sm rounded bg-white/10">Chat</button>
                <button id="tabHistory" class="px-2 py-1 text-sm rounded bg-white/10">Lịch sử</button>
            </div>
            <button id="newChatBtn" class="px-2 py-1 text-sm rounded border border-white/20">New</button>
            <button id="closeChatBtn" class="px-2 py-1 text-sm rounded bg-white/10">×</button>
        </div>
    </div>
    <div id="chatMessages" class="p-3 bg-white h-80 overflow-auto" role="log" aria-live="polite"></div>
    <div class="composer flex gap-2 items-center px-3 py-2 bg-gray-50 border-t border-gray-200">
        <input id="chatInput" class="flex-1 px-3 py-2 text-sm border rounded-md focus:outline-none" placeholder="Bạn cần tư vấn gì?" aria-label="Nhập câu hỏi" />
        <button id="chatSendBtn" class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm">Gửi</button>
    </div>
    <div class="text-sm text-gray-500 px-3 py-2">Ghi chú: Thông tin chỉ mang tính gợi ý.</div>
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
const composerEl = document.querySelector('.composer');

openBtn.addEventListener('click', ()=>{ chatWindow.classList.remove('hidden'); chatWindow.setAttribute('aria-hidden','false'); chatInput.focus(); });
closeBtn.addEventListener('click', ()=>{ chatWindow.classList.add('hidden'); chatWindow.setAttribute('aria-hidden','true'); });

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
        avatar.style.background = 'linear-gradient(45deg,#065f46,#10b981)';
        bubble.classList.add('bg-green-600','text-white','rounded-br-sm');
        bubble.textContent = text;
    } else {
        avatar.style.background = '#e6eef6';
        bubble.classList.add('bg-gray-100','text-gray-900');
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
    typing.innerHTML = '<span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span><span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:.12s"></span><span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:.24s"></span>';
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
