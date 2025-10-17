// messages.js - simple local chat between users and refugios using localStorage
document.addEventListener('DOMContentLoaded', ()=>{
  const yearEl = document.getElementById('year'); yearEl && (yearEl.textContent = new Date().getFullYear());
  const convEl = document.getElementById('conversations');
  const chatWindow = document.getElementById('chat-window');
  const chatForm = document.getElementById('chat-form');
  const chatInput = document.getElementById('chat-input');

  function loadConversations(){ try{return JSON.parse(localStorage.getItem('ps_conversations')||'[]')}catch(e){return []} }
  function saveConversations(arr){ try{ localStorage.setItem('ps_conversations', JSON.stringify(arr)) }catch(e){} }
  function getCurrentUser(){ try{return JSON.parse(localStorage.getItem('ps_current')||'null')}catch(e){return null} }

  // demo convs if empty
  if(loadConversations().length===0){
    const demo = [
      {id:'c1',title:'Refugio Central',messages:[{from:'Refugio Central',text:'Hola, tenemos información sobre el beagle en adopción.'}],last:Date.now()},
      {id:'c2',title:'Usuario2',messages:[{from:'Usuario2',text:'¿Todavía buscas al siames perdido?'}],last:Date.now()-60000}
    ]; saveConversations(demo);
  }

  function renderConversations(){
    const convs = loadConversations(); convEl.innerHTML='';
    convs.forEach(c=>{
      const li = document.createElement('li'); li.style.padding='.6rem'; li.style.borderBottom='1px solid rgba(11,22,30,0.04)'; li.style.cursor='pointer'; li.dataset.id=c.id;
      li.innerHTML = `<strong>${c.title}</strong><div style="font-size:.88rem;color:var(--muted-text)">${(c.messages[c.messages.length-1]||{}).text||''}</div>`;
      li.addEventListener('click', ()=> openConversation(c.id));
      convEl.appendChild(li);
    });
  }

  let activeConv = null;
  function openConversation(id){
    const convs = loadConversations(); const c = convs.find(x=>x.id===id); if(!c) return; activeConv = c; renderMessages(c);
  }

  function renderMessages(conv){
    chatWindow.innerHTML = '';
    conv.messages.forEach(m=>{
      const div = document.createElement('div'); div.style.marginBottom='.6rem';
      div.innerHTML = `<div style="font-weight:700">${m.from}</div><div style="color:var(--muted-text)">${m.text}</div>`;
      chatWindow.appendChild(div);
    });
    chatWindow.scrollTop = chatWindow.scrollHeight;
  }

  chatForm.addEventListener('submit', (e)=>{
    e.preventDefault(); if(!activeConv) return alert('Selecciona una conversación');
    const txt = chatInput.value.trim(); if(!txt) return; const user = getCurrentUser() || {name:'Anon'};
    const convs = loadConversations(); const c = convs.find(x=>x.id===activeConv.id); c.messages.push({from:user.name || user.email || 'Anon', text:txt}); c.last = Date.now(); saveConversations(convs); renderMessages(c); chatInput.value=''; renderConversations();
  });

  renderConversations();
  // open first by default
  // if another page asked to open a specific conversation, handle it
  const pendingOpen = localStorage.getItem('ps_open_conversation');
  if(pendingOpen){
    try{ localStorage.removeItem('ps_open_conversation'); openConversation(pendingOpen); }
    catch(e){ console.error('No se pudo abrir conversación pendiente', e); }
  } else {
    const first = loadConversations()[0]; if(first) openConversation(first.id);
  }
});
