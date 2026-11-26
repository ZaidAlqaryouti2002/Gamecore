document.querySelectorAll(".password-toggle").forEach(function (button) {
  button.addEventListener("click", function () {
    const input = document.getElementById(button.dataset.password);
    const showPassword = input.type === "password";
    input.type = showPassword ? "text" : "password";

    const fieldName = input.id === "confirm-password" ? "confirm password" : "password";
    const label = (showPassword ? "Hide " : "Show ") + fieldName;
    button.setAttribute("aria-label", label);
    button.setAttribute("title", label);
    button.setAttribute("aria-pressed", String(showPassword));
    button.querySelector("i").className = showPassword ? "bi bi-eye-slash" : "bi bi-eye";
  });
});
