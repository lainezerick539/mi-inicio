function handleCredentialResponse(response) {
  const token = response.credential;
  fetch('../insert.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'google_token=' + encodeURIComponent(token)
  })
  .then(res => res.text())
  .then(data => {
    console.log('Respuesta del servidor:', data);
    if (data.includes("exitoso")) {
      window.location.href = "../menu.html";
    } else {
      alert(data);
    }
  })
  .catch(err => console.error('Error:', err));
}

window.onload = function () {
  google.accounts.id.initialize({
    client_id: "644819228817-vi6akujhhqs6fd2l72a59snp8gcjojum.apps.googleusercontent.com",
    callback: handleCredentialResponse
  });
  google.accounts.id.renderButton(
    document.querySelector(".g_id_signin"),
    { theme: "filled_black", size: "large", shape: "pill" }
  );

  const inputs = document.querySelectorAll("input[type='text'], input[type='email'], input[type='password']");
  inputs.forEach(inp => {
    inp.addEventListener("focus", () => inp.style.backgroundColor = "rgba(255,255,255,0.15)");
    inp.addEventListener("blur", () => inp.style.backgroundColor = "rgba(255,255,255,0.08)");
  });
};
