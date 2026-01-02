In this task, we aim to build a fully integrated authentication system using Laravel Sanctum.
  
Requirements:  
Registration: 
Users can register with their email, name, and password. A verification code is generated 
and sent to the user’s email. Store the code with an expiration time of 10 minutes. 
Login: 
Users can log in using their email and password. Login should return an access token. 
Resend Code: 
If the verification code expires, users can request a new one. Old codes should be 
invalidated when a new code is requested. 
Verify Code: 
The user must verify the code sent to their email for registration. 
Refresh Token: 
Implement token refresh functionality to provide a new token when the old one expires. 
