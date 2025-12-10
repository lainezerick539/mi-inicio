document.addEventListener("DOMContentLoaded", () => {

function timeAgo(ts){
    if(!ts) return "";
    const now = Date.now();
    const past = new Date(ts).getTime();
    const s = Math.floor((now - past) / 1000);
    if(s < 60) return s + "s";
    const m = Math.floor(s / 60); if(m < 60) return m + "m";
    const h = Math.floor(m / 60); if(h < 24) return h + "h";
    const d = Math.floor(h / 24); if(d < 7) return d + "d";
    return Math.floor(d / 7) + " sem";
}

document.querySelectorAll(".conv-time").forEach(el => {
    el.textContent = timeAgo(el.dataset.time);
});

const search = document.getElementById("search");
if (search) {
    search.addEventListener("input", () => {
        const q = search.value.toLowerCase();
        document.querySelectorAll("#conversations .conv-item").forEach(c => {
            const name = c.querySelector(".conv-name").textContent.toLowerCase();
            const last = c.querySelector(".conv-last").textContent.toLowerCase();
            c.style.display = (name.includes(q) || last.includes(q)) ? "" : "none";
        });
    });
}

document.querySelectorAll(".tab").forEach(tab => {
    tab.addEventListener("click", () => {
        document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
        tab.classList.add("active");
        const filter = tab.dataset.filter;
        document.querySelectorAll("#conversations .conv-item").forEach(c => {
            const badge = c.querySelector(".conv-badge");
            if(filter === "unread") c.style.display = badge ? "" : "none";
            else c.style.display = "";
        });
    });
});

const messagesEl = document.getElementById("messages");
if(messagesEl){

    const conv = messagesEl.dataset.conv;
    const textInput = document.getElementById("textInput");
    const sendBtn = document.getElementById("sendBtn");
    const imgBtn = document.getElementById("imgBtn");
    const imageInput = document.getElementById("imageInput");
    const recBtn = document.getElementById("recBtn");
    const emojiBtn = document.getElementById("emojiBtn");
    const typingEl = document.getElementById("typing");

    let mediaRecorder = null;
    let audioChunks = [];

    function loadMessages(){
        fetch("fetch_mensajes.php?c=" + conv)
        .then(r => r.text())
        .then(html => {
            messagesEl.innerHTML = html;
            messagesEl.scrollTop = messagesEl.scrollHeight;
        });
    }

    loadMessages();
    setInterval(loadMessages, 2000);

    sendBtn.addEventListener("click", () => {
        const t = textInput.value.trim();
        if(!t) return;
        const fd = new FormData();
        fd.append("c", conv);
        fd.append("message", t);
        fetch("send_message.php", { method:"POST", body:fd })
        .then(() => {
            textInput.value = "";
            loadMessages();
        });
    });

    textInput.addEventListener("keydown", e => {
        if(e.key === "Enter" && !e.shiftKey){
            e.preventDefault();
            sendBtn.click();
        }
    });

    imgBtn.addEventListener("click", () => imageInput.click());
    imageInput.addEventListener("change", () => {
        const f = imageInput.files[0];
        if(!f) return;
        const fd = new FormData();
        fd.append("c", conv);
        fd.append("image", f);
        fetch("send_image.php", { method:"POST", body:fd })
        .then(() => {
            imageInput.value = "";
            loadMessages();
        });
    });

    emojiBtn.addEventListener("click", () => {
        let panel = document.getElementById("emojiPanel");
        if(!panel){
            panel = document.createElement("div");
            panel.id = "emojiPanel";
            panel.style.position = "absolute";
            panel.style.bottom = "80px";
            panel.style.left = "16px";
            panel.style.background = "#121416";
            panel.style.border = "1px solid #222";
            panel.style.padding = "8px";
            panel.style.borderRadius = "8px";
            panel.style.zIndex = 9999;
            const emojis = ["😀","😁","😂","🤣","😊","😍","😎","😢","😡","👍","👎","🙏","🔥","🎉"];
            emojis.forEach(e => {
                const b = document.createElement("button");
                b.textContent = e;
                b.style.padding = "6px";
                b.style.margin = "4px";
                b.style.background = "transparent";
                b.style.border = "none";
                b.style.fontSize = "18px";
                b.addEventListener("click", () => {
                    textInput.value += e;
                    textInput.focus();
                });
                panel.appendChild(b);
            });
            document.body.appendChild(panel);
        } else {
            panel.remove();
        }
    });

    recBtn.addEventListener("click", async () => {
        if(mediaRecorder && mediaRecorder.state === "recording"){
            mediaRecorder.stop();
            recBtn.textContent = "🎙️";
            return;
        }
        const stream = await navigator.mediaDevices.getUserMedia({ audio:true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.onstop = async () => {
            const blob = new Blob(audioChunks, { type:"audio/webm" });
            const fd = new FormData();
            fd.append("c", conv);
            fd.append("audio", blob, "voice.webm");
            await fetch("send_audio.php", { method:"POST", body:fd });
            loadMessages();
        };
        mediaRecorder.start();
        recBtn.textContent = "■";
    });

}
});
