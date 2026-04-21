
document.getElementById("loginForm").addEventListener("submit", function(e) {

  var email = document.getElementById("email").value.trim();
  var password = document.getElementById("password").value;
  var errorBox = document.getElementById("errorMsg");

  errorBox.style.display = "none";

  if (email === "" || password === "") {
    e.preventDefault();
    errorBox.style.display = "block";
    errorBox.textContent = "Veuillez remplir tous les champs.";
    return;
  }

  var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!emailPattern.test(email)) {
    e.preventDefault();
    errorBox.style.display = "block";
    errorBox.textContent = "Veuillez entrer une adresse e-mail valide.";
    return;
  }

  if (password.length < 8) {
    e.preventDefault();
    errorBox.style.display = "block";
    errorBox.textContent = "Le mot de passe doit contenir au moins 8 caractères.";
    return;
  }

});
