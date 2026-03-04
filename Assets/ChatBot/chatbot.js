(function(){
  const API_URL = "/Team_Project_TP2_Games_Store/Pages/chatbot.php"; // adjust if needed

  function el(tag, attrs={}, children=[]) {
    const n = document.createElement(tag);
    Object.entries(attrs).forEach(([k,v]) => {
      if (k === "class") n.className = v;
      else if (k === "html") n.innerHTML = v;
      else n.setAttribute(k, v);
    });
    children.forEach(c => n.appendChild(c));
    return n;
  }

  function iconChat() {
    return `
      <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M4 5.5C4 4.12 5.12 3 6.5 3h11C18.88 3 20 4.12 20 5.5v7C20 13.88 18.88 15 17.5 15H10l-4.2 3.15c-.7.52-1.8.02-1.8-.86V5.5Z"
              stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        <path d="M7.5 7.75h9M7.5 10.75h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
      </svg>`;
  }

  function safeText(str) {
    return (str ?? "").toString();
  }

  function appendMsg(container, role, text) {
    const msg = el("div", { class: `cb-msg ${role}` });
    msg.textContent = safeText(text);
    container.appendChild(msg);
    container.scrollTop = container.scrollHeight;
  }

  async function postJSON(url, data) {
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
      credentials: "same-origin"
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }

  // Build UI
  const launcher = el("button", { class:"cb-chat-launcher", type:"button", "aria-label":"Open chat", html: iconChat() });

  const panel = el("div", { class:"cb-chat-panel", role:"dialog", "aria-label":"CoreByte chat" });

  const header = el("div", { class:"cb-chat-header" }, [
    el("div", { class:"cb-chat-badge" }, [
    el("img", {
        src: "/Team_Project_TP2_Games_Store/Assets/Logo.png",
        alt: "CoreByte",
        style: "width:24px;height:24px;object-fit:contain;"
    })
    ]),    
    el("div", {}, [
      el("div", { class:"cb-chat-title" }, [document.createTextNode("CoreByte Assistant")]),
      el("div", { class:"cb-chat-subtitle" }, [document.createTextNode("Ask about games, orders, delivery, refunds.")])
    ]),
    el("button", { class:"cb-chat-close", type:"button" }, [document.createTextNode("Close")])
  ]);

  const messages = el("div", { class:"cb-chat-messages" });
  const typing = el("div", { class:"cb-typing" }, [document.createTextNode("Typing…")]);

  const inputWrap = el("div", { class:"cb-chat-input" });
  const input = el("input", { type:"text", placeholder:"Type your question…", maxlength:"600" });
  const sendBtn = el("button", { type:"button" }, [document.createTextNode("Send")]);

  inputWrap.appendChild(input);
  inputWrap.appendChild(sendBtn);

  panel.appendChild(header);
  panel.appendChild(messages);
  panel.appendChild(typing);
  panel.appendChild(inputWrap);

  document.body.appendChild(launcher);
  document.body.appendChild(panel);

  // State
  const state = {
    open: false,
    sessionId: localStorage.getItem("cb_chat_session") || (crypto.randomUUID ? crypto.randomUUID() : String(Date.now()))
  };
  localStorage.setItem("cb_chat_session", state.sessionId);

  function openChat() {
    state.open = true;
    panel.classList.add("open");
    input.focus();
    if (!state._welcomed) {
      state._welcomed = true;
      appendMsg(messages, "bot", "Hi! I’m CoreByte Assistant 👋  Ask me about a game, price, platform, delivery, refunds, or order status.");
      appendMsg(messages, "bot", "Try: “Do you have Elden Ring on PC?” or “What’s your refund policy?”");
    }
  }

  function closeChat() {
    state.open = false;
    panel.classList.remove("open");
  }

  async function sendMessage() {
    const text = input.value.trim();
    if (!text) return;
    input.value = "";
    appendMsg(messages, "user", text);

    typing.classList.add("show");

    try {
      const data = await postJSON(API_URL, { sessionId: state.sessionId, message: text });
      typing.classList.remove("show");

      if (data?.reply) appendMsg(messages, "bot", data.reply);
      if (Array.isArray(data?.suggestions) && data.suggestions.length) {
        appendMsg(messages, "bot", "Suggestions: " + data.suggestions.join(" • "));
      }
    } catch (e) {
      typing.classList.remove("show");
      appendMsg(messages, "bot", "Sorry — I couldn’t reach the server. Please try again.");
      console.error(e);
    }
  }

  launcher.addEventListener("click", () => state.open ? closeChat() : openChat());
  header.querySelector(".cb-chat-close").addEventListener("click", closeChat);
  sendBtn.addEventListener("click", sendMessage);
  input.addEventListener("keydown", (e) => {
    if (e.key === "Enter") sendMessage();
  });
})();