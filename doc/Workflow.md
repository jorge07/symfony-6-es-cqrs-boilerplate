## Workflow

**Given** An annon user

 **When** enters in homepage 
 
 **Then** should be able to see the Sign Up button
 
![Home](https://imgur.com/ykxHf1d.png)

 **When** user click in Sign Up button 
 
 **Then** should be redirected to Sign Up page

![Sign up](https://imgur.com/qZs8iIP.png)


**Given** a user in Sign Up page

 **When** enter in the form with a valid email and password
 
![invalid email](https://imgur.com/w9Z1w8d.png)

 **Then** it should be registered

  **And** display de user information
 
![signed up](https://imgur.com/XbRtfUh.png)

 **Then** when user enter in Sign In page should be able to Sign In
   
  **And** open a new session

 ![Signed in](https://imgur.com/ZjmTDYU.png)

 **Given** All user events happened in UI
   
   **And** published in rabbit

 ![rmq](https://imgur.com/XobqV9j.png)

  **Then** it should be consumed to be  stored in elastic

   **And** visible in Kibana

![kibana](https://imgur.com/VMRLSDJ.png)
