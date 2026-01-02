# Authentication System Using Laravel Sanctum

## 📌 Task Description

In this task, we aim to build a **fully integrated authentication system** using **Laravel Sanctum**, following modern security and API authentication best practices.

## V1 Requirements

### 🔐 Registration
- Users can register using:
  - Name
  - Email
  - Password
- Upon registration:
  - A **verification code** is generated
  - The code is sent to the user’s email
  - The code is stored with an **expiration time of 10 minutes**

---

### 🔑 Login
- Users can log in using their **email and password**
- Successful login returns a **Sanctum access token**
- The token is required to access protected endpoints

---

### 🔁 Resend Verification Code
- If the verification code expires:
  - Users may request a new verification code
- Security rules:
  - Any previously issued codes are **invalidated**
  - Only the **latest code** remains valid

---

### ✔️ Verify Code
- Users must verify the code sent to their email
- Verification is required to complete the registration process
- Expired or invalid codes are rejected

---

### ♻️ Refresh Token
- Implement a **token refresh mechanism**
- When an access token expires:
  - A new token is issued
- Ensures continuous and secure authenticated sessions

----

## V2 Requirements / Updates
The second version of the task extends the authentication system by improving stability, security, and account recovery features.

### 1️⃣ Bug Fixes & Improvements
- Fix all errors and issues identified during the **previous task discussion session**
- Improve code quality, validation, and overall system reliability

---

### 2️⃣ Password Recovery
- Implement a **password recovery** feature
- Users can request a password reset by providing their email address
- The system sends a **password reset email** containing a secure link

---

### 3️⃣ Password Reset Confirmation
- The password reset link:
  - Confirms the user’s intent to reset their password
  - Prevents unauthorized password change attempts

---

### 4️⃣ Secure Reset Token
- The password reset link includes a **unique, time-limited token**
- The token:
  - Authorizes access to the password change endpoint
  - Becomes invalid after use or expiration

---

### 5️⃣ Two-Factor Authentication (2FA)
- Add **Two-Factor Authentication (2FA)** as an extra security layer
- Authentication flow:
  1. User enters email and password
  2. If credentials are valid, a **verification code** is sent to the user’s email
  3. The user must enter the code to complete the login process
- Ensures enhanced protection against unauthorized access

----

## V3 Requirements / Updates

The third version focuses on improving **error handling** and implementing **advanced database relationships** in Laravel.

### 1️⃣ Exceptions
- Research the concept of **exceptions** in Laravel and how they are handled in your current Laravel version.
- Implement **exceptions** throughout the project to handle:
  - Validation errors
  - Authentication errors
  - Database and application errors
- Ensures **cleaner error handling** and **more maintainable code**

---

### 2️⃣ Morph Relationships
- Research and understand **morph (polymorphic) relationships** in Laravel:
  - **What is a morph relationship?**
  - **How is it used?**
  - **Why it is preferred over standard relationships in some cases?**
- Implement a **Media table** to store user media (currently only user profile images)
- Link the **Media table to Users** using a **morph relationship**, allowing:
  - Each user to have their own media
  - Flexibility to extend to other models in the future (e.g., posts, products)

---

### Outcome
- Robust exception handling across the application  
- Flexible and scalable database relationships using polymorphic associations  
- Foundation for future media handling beyond just user images



