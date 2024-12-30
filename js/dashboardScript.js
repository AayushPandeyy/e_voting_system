document.addEventListener("DOMContentLoaded", function () {
    function openModal() {
      document.getElementById("password-modal").style.display = "block";
    }
  
    function closeModal() {
      document.getElementById("password-modal").style.display = "none";
    }
  
    function submitPassword() {
      const password = document.getElementById("election-password").value;
  
      // Add your password validation logic here
      if (password) {
        alert("Password submitted successfully!"); // Replace with actual voting logic
        closeModal();
        // Here you would typically handle the voting logic (e.g., send to server)
      } else {
        alert("Please enter a password.");
      }
    }
  
    // Close the modal when clicking outside of it
    window.onclick = function (event) {
      const modal = document.getElementById("password-modal");
      if (event.target == modal) {
        closeModal();
      }
    };
    // Example JavaScript to dynamically update the statistics
    const totalVoters = document.getElementById("total-voters");
    const totalVotes = document.getElementById("total-votes");
    const activePolls = document.getElementById("active-polls");
  
    // Simulate fetching data from an API
    setTimeout(() => {
      totalVoters.textContent = "1500"; // Updated total voters
      totalVotes.textContent = "1000"; // Updated total votes
      activePolls.textContent = "5"; // Updated active polls
    }, 1000);
  });
  