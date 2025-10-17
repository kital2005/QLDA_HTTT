# TODO: Add JavaScript for Login and Registration Forms

## Steps to Complete

1. **Move inline password toggle scripts to script.js**

   - Remove inline scripts from login.html and register.html.
   - Add unified password toggle handlers in script.js for #togglePassword, #toggleConfirmPassword.

2. **Add form submission handler for #loginForm**

   - Validate email and password fields.
   - Simulate login (e.g., check against hardcoded credentials: email: 'user@example.com', password: 'password').
   - Handle "remember me" checkbox: store credentials in localStorage if checked.
   - Display success/error messages using Bootstrap alerts.

3. **Add form submission handler for #registerForm**

   - Validate name, email, password, confirmPassword fields.
   - Ensure password and confirmPassword match.
   - Add password strength check (e.g., minimum 6 characters).
   - Simulate registration (e.g., store user data in localStorage).
   - Display success/error messages using Bootstrap alerts.

4. **Enhance client-side validation**

   - Add custom validation for email format.
   - Add password strength indicator if needed.
   - Integrate with Bootstrap's needs-validation class.

5. **Test the implementation**
   - Open login.html and register.html in browser.
   - Test password toggles, form submissions, validations, and messages.
   - Run locally via live server if necessary.

## Progress Tracking

- [x] Step 1: Move password toggles to script.js
- [x] Step 2: Implement login form handler
- [x] Step 3: Implement register form handler
- [x] Step 4: Add validation enhancements
- [x] Step 5: Test functionality
