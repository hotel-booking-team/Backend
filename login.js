
document.getElementById("loginForm").addEventListener("submit", function(e) {

   email = document.getElementById("email").value.trim();
  password = document.getElementById("password").value;
  errorBox = document.getElementById("errorMsg");

  errorBox.style.display = "none";

  if (email === "" || password === "") {
    e.preventDefault();
    errorBox.style.display = "block";
    errorBox.textContent = "Veuillez remplir tous les champs.";
    return;
  }

 emailPattern = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;

  if (!emailPattern.test(email)) {
    e.preventDefault();
    errorBox.style.display = "block";
    errorBox.textContent = "Veuillez entrer un email Gmail valide (ex: exemple@gmail.com).";
    return;
  }

 passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;

  if (!passwordPattern.test(password)) {
    e.preventDefault();
    errorBox.style.display = "block";
    errorBox.textContent = "Le mot de passe doit contenir au moins 8 caractères avec lettres et chiffres.";
    return;
  }

});