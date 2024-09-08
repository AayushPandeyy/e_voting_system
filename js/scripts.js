// Selecting the buttons and container element
const signUpButton = document.getElementById("signUp");
const signInButton = document.getElementById("signIn");
const container = document.getElementById("container");

// Add event listener to 'Sign Up' button
signUpButton.addEventListener("click", () => {
  container.classList.add("right-panel-active");
});

// Add event listener to 'Sign In' button
signInButton.addEventListener("click", () => {
  container.classList.remove("right-panel-active");
});
