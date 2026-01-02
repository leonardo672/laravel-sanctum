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

### 🔑 Login
- Users can log in using their **email and password**
- Successful login returns a **Sanctum access token**
- The token is required to access protected endpoints

### 🔁 Resend Verification Code
- If the verification code expires:
  - Users may request a new verification code
- Security rules:
  - Any previously issued codes are **invalidated**
  - Only the **latest code** remains valid

### ✔️ Verify Code
- Users must verify the code sent to their email
- Verification is required to complete the registration process
- Expired or invalid codes are rejected

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

### 2️⃣ Password Recovery
- Implement a **password recovery** feature
- Users can request a password reset by providing their email address
- The system sends a **password reset email** containing a secure link

### 3️⃣ Password Reset Confirmation
- The password reset link:
  - Confirms the user’s intent to reset their password
  - Prevents unauthorized password change attempts

### 4️⃣ Secure Reset Token
- The password reset link includes a **unique, time-limited token**
- The token:
  - Authorizes access to the password change endpoint
  - Becomes invalid after use or expiration

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

### 2️⃣ Morph Relationships
- Research and understand **morph (polymorphic) relationships** in Laravel:
  - **What is a morph relationship?**
  - **How is it used?**
  - **Why it is preferred over standard relationships in some cases?**
- Implement a **Media table** to store user media (currently only user profile images)
- Link the **Media table to Users** using a **morph relationship**, allowing:
  - Each user to have their own media
  - Flexibility to extend to other models in the future (e.g., posts, products)

### Outcome
- Robust exception handling across the application  
- Flexible and scalable database relationships using polymorphic associations  
- Foundation for future media handling beyond just user images

-----

## V4 Requirements / Updates

The fourth version of the project focuses on **advanced error handling** and the introduction of a **Podcast Channel feature** to expand user functionality.

### 1️⃣ Fixes & Improvements
- Address all errors and issues highlighted in the **previous task discussion session**
- Ensure stability, clean code, and adherence to best practices

### 2️⃣ Custom Exception Handler
- Implement a **custom exception handler** for specific errors in the project
- Guidelines:
  - Use Laravel’s **built-in exceptions** when they already cover the error type
  - Only create a custom exception if necessary
- Customize exception responses to:
  - Return **consistent error messages**
  - Follow the **project's required response format**
  - Improve debugging and API consistency

### 3️⃣ Podcast Channel Feature
- Users can create their own **Podcast Channel**, linked to their account
- Channels serve as a personal space for publishing audio content (podcasts or audiobooks)
- Functionalities:
  - Create, manage, and view a user’s channel
  - Publish content directly to the channel
- API responses for all actions should be returned **in JSON format only**

### Outcome
- Improved error handling with clear, consistent responses
- Extended user functionality with a **personalized content publishing platform**

-----

## V5 Requirements / Updates

The fifth version of the project focuses on **content interaction**, **media enrichment**, and overall system refinement.

### 1️⃣ Fixes & Improvements
- Resolve all errors and issues identified during the **previous task discussion session**
- Improve performance, validation, and API stability

### 2️⃣ Podcast Upload & Media Support
- Allow users to **upload their own podcasts**
- Each podcast may include:
  - Audio file
  - Optional **cover image**
- Ensure proper validation and secure storage of uploaded media files

### 3️⃣ Podcast Comments System
- Enable users to **comment on podcasts**
- Implement a **nested commenting system**, allowing:
  - Replies to comments
  - Replies to existing replies (threaded discussions)
- Supports structured conversations and user engagement

### Outcome
- Rich podcast content with media customization
- Interactive community features through threaded comments
- A more complete and user-centered content platform
- JSON-based APIs ensure compatibility with frontend clients and mobile apps

----

## V6 Requirements / Updates

The sixth version focuses on **data seeding**, **advanced querying**, and **content discovery features** to enhance scalability and user experience.

### 1️⃣ Factories & Seeders
- Create **Factories and Seeders** for the following models:
  - Comments
  - Podcasts
- Ensure that:
  - Each podcast is assigned a **random number of comments**
  - Comment data supports nested replies where applicable
- Enables realistic test data for development and testing environments

### 2️⃣ Podcast Details Endpoint
- Create an API endpoint to retrieve a **single podcast** along with all of its comments
- The response must include:
  - Top-level comments
  - Nested comments (replies to comments)
- Ensures efficient data loading and structured JSON responses

### 3️⃣ Podcast Likes
- Add the ability for users to **like podcasts**
- Implement the feature using a **clean and efficient approach**, such as:
  - Pivot tables
  - Optimized queries
  - Proper relationship design
- Prevent duplicate likes from the same user

### 4️⃣ Podcast Categories
- Add **categories** to podcasts
- Define and implement the appropriate relationship between:
  - Podcasts
  - Categories
- Enable podcasts to belong to one or multiple categories, as required by the design

### 5️⃣ Random Podcasts Listing
- Create an endpoint to fetch a **random list of podcasts**
- Requirements:
  - Pagination support
  - Consistent performance
  - Clean and predictable API responses

### Outcome
- Realistic seeded data for testing and demos
- Optimized endpoints for podcast retrieval and discovery
- Enhanced user engagement through likes and categorization
- Scalable backend architecture ready for production use
