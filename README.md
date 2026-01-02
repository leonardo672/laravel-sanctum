# Authentication System Using Laravel Sanctum

## 📌 Task Description

In this task, we aim to build a **fully integrated authentication system** using **Laravel Sanctum**, following modern security and API authentication best practices.

---

## ✅ Requirements

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

---
