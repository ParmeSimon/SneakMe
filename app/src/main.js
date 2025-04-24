document.querySelector('#app').innerHTML = `
  <div class="chat-container">
    <h2>💬 SneakMe Chatbot</h2>
    <div id="chatbox" class="chatbox"></div>
    <form id="chatForm" class="chat-form">
      <input type="text" id="userInput" placeholder="Pose-moi une question..." autocomplete="off" required />
      <button type="submit">Envoyer</button>
    </form>
  </div>
`;

const style = document.createElement('style');
style.textContent = `
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f4f4;
    margin: 0;
    padding: 2rem;
  }

  .chat-container {
    max-width: 700px;
    margin: auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 2rem;
    display: flex;
    flex-direction: column;
    height: 80vh;
  }

  .chatbox {
    flex: 1;
    overflow-y: auto;
    margin-bottom: 1rem;
    padding-right: 10px;
  }

  .message {
    margin: 0.5rem 0;
    padding: 1rem;
    border-radius: 12px;
    max-width: 80%;
    line-height: 1.4;
    word-wrap: break-word;
  }

  .user {
    background: #d1e7ff;
    align-self: flex-end;
    text-align: right;
  }

  .bot {
    background: #e9ecef;
    align-self: flex-start;
  }

  .chat-form {
    display: flex;
    gap: 0.5rem;
  }

  .chat-form input {
    flex: 1;
    padding: 0.8rem;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 1rem;
  }

  .chat-form button {
    padding: 0.8rem 1.5rem;
    border-radius: 10px;
    background: #000;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
  }
`;
document.head.appendChild(style);

const chatbox = document.getElementById('chatbox');
const chatForm = document.getElementById('chatForm');
const userInput = document.getElementById('userInput');

chatForm.addEventListener('submit', async (e) => {
	e.preventDefault();
	const text = userInput.value.trim();
	if (!text) return;

	addMessage(text, 'user');

	try {
		const res = await fetch('http://localhost/SneakMe/app/public/handler.php?action=chatbot', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({ prompt: text })
		});

		const result = await res.json();
		addMessage(result.response || "❌ Je n'ai pas compris…", 'bot');
	} catch (err) {
		addMessage("❌ Erreur de connexion au serveur", 'bot');
		console.error(err);
	}

	userInput.value = '';
});

function addMessage(text, sender) {
	const msg = document.createElement('div');
	msg.className = `message ${sender}`;
	msg.textContent = text;
	chatbox.appendChild(msg);
	chatbox.scrollTop = chatbox.scrollHeight;
}
