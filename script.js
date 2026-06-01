document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('login-form');
  const signUpForm = document.getElementById('sign-up-form');
  const showLogin = document.getElementById('show-login');
  const showSignUp = document.getElementById('show-signup');

  if (!loginForm || !signUpForm) return;

  
  loginForm.classList.add('hidden');
  signUpForm.classList.remove('hidden');

  if (showLogin) {
    showLogin.addEventListener('click', (e) => {
      e.preventDefault();
      loginForm.classList.remove('hidden');
      signUpForm.classList.add('hidden');
      loginForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  }

  if (showSignUp) {
    showSignUp.addEventListener('click', (e) => {
      e.preventDefault();
      signUpForm.classList.remove('hidden');
      loginForm.classList.add('hidden');
      signUpForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  }
});
