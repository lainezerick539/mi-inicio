function handleCredentialResponse(response) {
  console.log("ID Token recibido:", response.credential);
  localStorage.setItem('isLoggedIn', 'true');
  window.location.href = "menu.php";
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
};