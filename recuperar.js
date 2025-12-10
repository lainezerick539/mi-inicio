const form = document.getElementById("recuperarForm");
const msg = document.getElementById("mensaje");

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  const datos = new FormData(form);
  
  const res = await fetch("recuperar.php", { method: "POST", body: datos });
  const texto = await res.text();

  msg.style.display = "block";
  if (texto.toLowerCase().includes("correo enviado")) {
    msg.className = "msg success";
  } else {
    msg.className = "msg error";
  }
  msg.textContent = texto;
});
