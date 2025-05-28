document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("register-form");
    
    form.addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent form submission until validation passes

        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value;
        const errorContainer = document.getElementById("error-message");
        errorContainer.innerHTML = ""; // Clear previous errors

        let errors = [];

        // Username validation (at least 3 characters, no special characters)
        if (username.length < 3 || !/^[a-zA-Z0-9]+$/.test(username)) {
            errors.push("❌ Användarnamnet måste vara minst 3 tecken och kan bara innehålla bokstäver och siffror.");
        }

        // Password validation (at least 8 characters, including a number)
        if (password.length < 8 || !/\d/.test(password)) {
            errors.push("❌ Lösenordet måste vara minst 8 tecken långt och innehålla minst en siffra.");
        }

        // Show errors if any
        if (errors.length > 0) {
            errors.forEach(error => {
                const errorItem = document.createElement("p");
                errorItem.textContent = error;
                errorContainer.appendChild(errorItem);
            });
        } else {
            form.submit(); // Submit form if no errors
        }
    });
});
