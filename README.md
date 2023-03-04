#Task Management
---------------------------------

This Project contain only APIs.

Requirements to setup and run this project as below.
- Xampp / WampServer require to run the PHP files.
- Mysql require to setup the database.


To start setup please follow the below instructions.
- Create a blank database in with any name.
- Update the Config.php file with you Database name and Connection.
- This project is not contain any SQL file. So, to create tables in the database please run this install.php file in your browser one time.
- Setup the postman collection (find it in root directory) in your postman software to test APIs.


All Endpoints are listed below for your reference:
- action=userLogin
- action=userRegister
- action=addTask
- action=addNote
- action=getTasks&user_id=#
- action=filter&status={New, Incomplete, Complete}&due_date={YYYY-MM-DD}&priority={High, Medium, Low}&notes={true, false}
