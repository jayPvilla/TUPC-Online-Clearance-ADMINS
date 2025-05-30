document.addEventListener('DOMContentLoaded', function () {
  const loginBtn = document.querySelector('.login-btn');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');

  loginBtn.addEventListener('click', function () {
    const email = emailInput.value.trim();
    const password = passwordInput.value;

    const isEmailValid = email.includes('@gmail.com') || email.includes('gsfe.tupcavite.edu.ph');
    const isPasswordValid = password.length >= 8;

    if (isEmailValid && isPasswordValid) {
      // Redirect to student/account.html if credentials are valid
      window.location.href = 'student/account.php';
    } else {
      alert('Invalid email or password.\n\nEmail must contain @gmail.com or gsfe.tupcavite.edu.ph\nPassword must be at least 8 characters.');
    }
  });
});
