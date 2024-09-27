function openModal(electionId) {
  document.getElementById("election-id").value = electionId;
  document.getElementById("password-modal").style.display = "block";
}

function closeModal() {
  document.getElementById("password-modal").style.display = "none";
}
