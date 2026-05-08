<?php /* Chat bubble UI included in footer */ ?>
<style>
/* Chat bubble styles - improved layout */
:root{ --chat-bg:#ffffff; --primary:#047857; --muted:#6b7280; --bot-bg:#f1f5f9; --user-bg:var(--primary); }
.chat-bubble-btn { position: fixed; right: 18px; bottom: 18px; z-index: 2000; }
.chat-window { position: fixed; right: 18px; bottom: 78px; width: 360px; max-width: calc(100vw - 36px); max-height: 70vh; z-index:2000; display:none; box-shadow:0 12px 40px rgba(2,6,23,0.2); border-radius:10px; overflow:hidden; font-family:Inter,ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial }
.chat-window .header { background:linear-gradient(90deg,var(--primary),#065f46);color:#fff;padding:12px 14px;display:flex;align-items:center;gap:8px }
.chat-window .header .title { font-weight:600 }
.chat-window .messages { background:var(--chat-bg);padding:12px; height:320px; overflow:auto }
.chat-window .composer { padding:10px; background:#fbfdfe;border-top:1px solid #eef2f7; display:flex; gap:8px; align-items:center }
.chat-msg { margin-bottom:10px; display:flex; gap:8px; align-items:flex-end }
.chat-msg .avatar { width:34px; height:34px; border-radius:50%; flex:0 0 34px; display:inline-block }
.chat-msg.bot { justify-content:flex-start }
.chat-msg.user { justify-content:flex-end }
.chat-msg .bubble { display:inline-block;padding:10px 14px;border-radius:14px; max-width:78%; line-height:1.38; font-size:14px }
.chat-msg.user .bubble { background:var(--user-bg); color:#fff; border-bottom-right-radius:6px }
.chat-msg.bot .bubble { background:var(--bot-bg); color:#0f172a }
.chat-time { font-size:11px; color:var(--muted); margin-top:4px }
.typing { display:inline-block; height:18px; width:46px; background:transparent }
.typing > span { display:inline-block; width:8px; height:8px; margin-right:6px; background:#cbd5e1; border-radius:50%; opacity:0.6; animation:blink 1s infinite }
.typing > span:nth-child(2){ animation-delay: .12s }
.typing > span:nth-child(3){ animation-delay: .24s }
@keyframes blink { 0%{ transform: translateY(0); opacity:.3 } 50%{ transform: translateY(-4px); opacity:1 } 100%{ transform: translateY(0); opacity:.3 } }
.suggestions { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px }
.chip { padding:6px 10px; background:#eef2ff; color:#0b3a6b; border-radius:999px; cursor:pointer; font-size:13px }
.chat-window .footer-note { font-size:12px; color:var(--muted); padding:6px 12px }
</style>

<div class="chat-bubble-btn">
    <button id="openChatBtn" class="btn btn-success rounded-circle" title="Chat tư vấn">
        <i class="fas fa-comments"></i>
    </button>
</div>

<div class="chat-window" id="chatWindow" aria-hidden="true">
    <div class="header">
        <img src="/public/image/avatars/assistant.png" onerror="this.style.display='none'" style="width:34px;height:34px;border-radius:50%">
        <div style="flex:1">
            <div class="title">Trợ lý mua hàng</div>
            <div style="font-size:12px;color:rgba(255,255,255,0.9)">Gợi ý + hỗ trợ chọn sản phẩm</div>
        </div>
        <button id="closeChatBtn" class="btn btn-sm btn-light">×</button>
    </div>
    <div class="messages" id="chatMessages" role="log" aria-live="polite"></div>
    <div class="composer">
        <input id="chatInput" class="form-control form-control-sm" placeholder="Bạn cần tư vấn gì?" aria-label="Nhập câu hỏi" />
        <button id="chatSendBtn" class="btn btn-primary btn-sm">Gửi</button>
    </div>
    <div class="footer-note">Ghi chú: Thông tin chỉ mang tính gợi ý.</div>
</div>

<script>
window.CHATBOT_API_URL = window.CHATBOT_API_URL || 'http://localhost:3000/api/chat';
const openBtn = document.getElementById('openChatBtn');
const closeBtn = document.getElementById('closeChatBtn');
const chatWindow = document.getElementById('chatWindow');
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const chatSendBtn = document.getElementById('chatSendBtn');

openBtn.addEventListener('click', ()=>{ chatWindow.style.display='block'; chatWindow.setAttribute('aria-hidden','false'); chatInput.focus(); });
closeBtn.addEventListener('click', ()=>{ chatWindow.style.display='none'; chatWindow.setAttribute('aria-hidden','true'); });

function escapeHtml(s){
        return String(s)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/\"/g,'&quot;')
            .replace(/\'/g,'&#39;');
}

function linkify(text){
    return text.replace(/(https?:\/\/[^\s]+)/g, (m)=>`<a href="${m}" target="_blank" rel="noopener noreferrer">${m}</a>`);
}

function renderReplyAsHTML(text){
    if (!text) return '';
    // escape then convert list lines and links
    let out = escapeHtml(text);
    // convert lines that start with '- ' into <ul>
    if (/^\s*[-\*]\s+/m.test(out)){
        out = out.split(/\r?\n/).map(line=>{
            if (/^\s*[-\*]\s+/.test(line)) return `<li>${line.replace(/^\s*[-\*]\s+/,'')}</li>`;
            return `<p>${line}</p>`;
        }).join('');
        out = `<ul style="padding-left:18px;margin:6px 0">${out}</ul>`;
    } else {
        out = out.replace(/\r?\n/g,'<br>');
    }
    out = linkify(out);
    return out;
}

function appendMessage(who, text, opts){
        const div = document.createElement('div');
        div.className = 'chat-msg ' + (who==='user'?'user':'bot');
        const avatar = document.createElement('div'); avatar.className='avatar';
        const bubble = document.createElement('div'); bubble.className='bubble';
        if (who === 'user'){
            avatar.style.background = 'linear-gradient(45deg,#065f46,#10b981)';
            bubble.textContent = text;
        } else {
            avatar.style.background = '#e6eef6';
            bubble.innerHTML = renderReplyAsHTML(text);
        }
        div.appendChild(avatar);
        div.appendChild(bubble);
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return div;
}

function makeTypingElement(){
    const div = document.createElement('div'); div.className='chat-msg bot';
    const avatar = document.createElement('div'); avatar.className='avatar'; avatar.style.background='#e6eef6';
    const bubble = document.createElement('div'); bubble.className='bubble';
    const typing = document.createElement('span'); typing.className='typing';
    typing.innerHTML = '<span></span><span></span><span></span>';
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
                const r = await fetch(window.CHATBOT_API_URL, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ message: v }) });
                const j = await r.json();
                // replace typing with actual reply
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
