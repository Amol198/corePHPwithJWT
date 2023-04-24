Core PHP assignment

Requirements
PHP 7.2 or later
MySQL 5.7 or later
Composer
Xampp / Wamp / Lamp
Firebase JWT

Installation
1 Clone the repository to your local machine:
2 Navigate to the project directory:
3 Install above dependencies using Composer:

All Major files are in the Project folder only.
In index.php URL parsing is written.

In Api.php file all APIs are written like login, registration, create task, reports.
it has one extended parent file name BaseController.php.
in BaseController file JWT authentication part and database connections written.
Except Login and register other APIs are JWT authenticated for security purpose.

our first API will be registration API.
http://localhost/Tasks/index.php/Api/registration
by clicking this you can register yourself. You have to pass name, email and password to register.
email should be valid, password must contain 8 characters with atleast 1 capital letter, 1 small letter 1 number and 1 special character
after registration you will get access_token which is created through JWT.

second API will be Login API.
http://localhost/Tasks/index.php/Api/login
here you can pass your email ID and valid password. after successful login you will get access_token created through JWT.
We are providing access_token for registration as well cause If Android / iOS user wants to redirect to Home page or want to other activities then he can do.

our Third API will be create task API
http://localhost/Tasks/index.php/Api/create_task
You can create task and notes by this API.
for this API we are getting userId by access token and that userId we are storing in task table
for Task creation task subject, task description, start date, due date , task status and task priority are required parameters.
if anything is missing then it will show fail response or we can say validation error response.
 after entering all above fields you have to add notes as well for the task.
notes can be many for one task. So we are getting data for notes in Array format.
for creating notes you have to pass subject and note in array format as the format I have already given in POSTMAN collection.
as well as we have multiple attachments for each note So for attachment we have more arrays in note section. that attachments format also I have given in POSTMAN collection.
Notes are not compulsary under tasks so subject , description and attachments are not mandetory for creating task.
Yes previously I have added mandetory option for subject and description and the code is commented in the API file.
If we want to do mandetory fields for notes then we can just uncomment the code which is written in create_task API.
along with all those parameters you have to pass access_token in Authorization from POSTMAN to run this API. without Authorization Token you cannot access this API.
this API first validate necessary things and then first inserts tasks data. after successful insertion of task data it will go to notes data insertion as well as we have file upload section as well. 
File upload section will get file from end user and it will upload the file in /images folder which is child folder of our main Project folder itself. we are not storing file in base64 or anyother format to mysql. we simply storing file into the /images folder. and we get the name of those uploaded files. Yes we may have multiple files for each note and for images we have not created any separate table. we have stored images in json format for each note which has single file or multiple files.
after successful submission of data , User will get success response for data insertion.
 
 our Last API will be Reports API
http://localhost/Tasks/index.php/Api/reports
 you can say reports or you can say getting data for all tasks created with notes for each task.
 here one necessary parameter means you have to pass authorization token through POSTMAN to access this API.
 expect no parameter is compulsary for this API. you can just hit the API and you will get all tasks result, inside tasks. notes result in array format and inside notes, files result in array format.
 currently we are showing the data with High priority first as this is the requirement for the project.
 and we have filter as well for this.
 
 minimum_one_note: if you pass this parameter it will give you the result those tasks which are having atleast 1 note. you can pass this 
 parameter blank as well.
 
 status: value for this parameter must be 'New' or 'Incomplete' or 'Complete'. by this if you want to see only New tasks or only Imcomplete tasks or only Completed tasks then you can pass this parameter with value. this cannot be blank.
 
due_date: if you want to check that which tasks have due on which date then you can pass the date in this parameter. the date format must be yyyy-mm-dd .

priority: we have default priority high for sorting as this is our requirement. but if you pass "Low" in priority parameter then it will sort the data from Low priority to High Priority.

So We have used JWT for create task and reports API.
I have added comments as well in the code.
I am attaching POSTMAN collection as well along with this project.
If you have any queries then you can please let me know.	

