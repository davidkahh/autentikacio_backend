document.addEventListener('DOMContentLoaded', () => {
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', handleRegister);
  }

  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', handleLogin);
  }
});

function setMessage(elementId, text, color) {
  const element = document.getElementById(elementId);
  if (!element) {
    return;
  }

  element.textContent = text;
  element.style.color = color;
}

async function sendRequest(payload) {
  const response = await fetch('service.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
    },
    body: new URLSearchParams(payload)
  });

  const data = await response.json();
  return { ok: response.ok, data };
}

async function handleRegister(event) {
  event.preventDefault();

  const username = document.getElementById('regUser')?.value.trim() ?? '';
  const email = document.getElementById('regEmail')?.value.trim() ?? '';
  const password = document.getElementById('regPass')?.value ?? '';

  if ( !username || !email || !password) {
    setMessage('regMessage', 'Töltsd ki az összes mezőt!', 'red');
    return;
  }
  // bevarja az adatokat a szervernek, és várja a választ
  const result = await sendRequest({
    action: 'register',
    username,
    email,
    password
  });

  if (!result.ok || !result.data.success) {
    setMessage('regMessage', result.data.message || 'Sikertelen regisztráció.', 'red');
    return;
  }

  setMessage('regMessage', result.data.message, 'limegreen');
  event.target.reset();
}

async function handleLogin(event) {
  event.preventDefault();

  const loginValue = document.getElementById('loginUser')?.value.trim() ?? '';
  const password = document.getElementById('loginPass')?.value ?? '';

  if (!loginValue || !password) {
    setMessage('loginMessage', 'Add meg a felhasználónevet/emailt és a jelszót!', 'red');
    return;
  }

  const result = await sendRequest({
    action: 'login',
    loginUser: loginValue,
    password
  });

  if (!result.ok || !result.data.success) {
    setMessage('loginMessage', result.data.message || 'Hibás bejelentkezés.', 'red');
    return;
  }

  setMessage('loginMessage', `${result.data.message} Üdv, ${result.data.user.name}!`, 'limegreen');
  event.target.reset();
}