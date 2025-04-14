AOS.init({ duration: 1200, once: true });

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Chatbot Functionality
let promptInput = document.querySelector("#prompt");
let submitBtn = document.querySelector("#submit");
let chatContainer = document.querySelector(".chat-container");

const Api_Url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyBJvhPW1ZSldEsWcGoNwgN4LuarfBPtnXY";

async function generateResponse(userMessage) {
    let aiChatBox = document.createElement("div");
    aiChatBox.classList.add("chat-box");
    let aiBubble = document.createElement("div");
    aiBubble.classList.add("chat-bubble", "ai-chat");
    aiBubble.textContent = "Thinking...";
    aiChatBox.appendChild(aiBubble);
    chatContainer.appendChild(aiChatBox);
    chatContainer.scrollTop = chatContainer.scrollHeight;
    
    try {
        let response = await fetch(Api_Url, {
            method: "POST",
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({"contents": [{"parts": [{text: userMessage}]}]})
        });
        let data = await response.json();
        aiBubble.textContent = data.candidates[0].content.parts[0].text.replace(/\*\*(.*?)\*\*/g,"$1").trim();
    } catch(error) {
        aiBubble.textContent = "Error fetching response.";
    }
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function handleUserMessage() {
    let userMessage = promptInput.value.trim();
    if (!userMessage) return;
    
    let userChatBox = document.createElement("div");
    userChatBox.classList.add("chat-box");
    let userBubble = document.createElement("div");
    userBubble.classList.add("chat-bubble", "user-chat");
    userBubble.textContent = userMessage;
    userChatBox.appendChild(userBubble);
    chatContainer.appendChild(userChatBox);
    chatContainer.scrollTop = chatContainer.scrollHeight;
    
    promptInput.value = "";
    generateResponse(userMessage);
}

promptInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
        handleUserMessage();
    }
});
submitBtn.addEventListener("click", handleUserMessage);