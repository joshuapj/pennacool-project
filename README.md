## APPLICATION OVERVIEW
This application is meant to provide a simple dashboard for managing schools and
their users. It provides the functionality to create, edit and delete schools and their
users as well as a some information about the number of users in each role.

The application also provides session based authentication for logging in. A user with
either an admin or principal role will have access to the management features of the
dashboard.

## PREREQUESITES
1. Docker should be installed.


## SETUP

### 1. Run Docker Compose
```docker-compose up -d```

### 2. Copy over the database file from the folder into the mysql-container
```docker cp "./db/setup_sample_data.sql" mysql-container:/database.sql```

### 3. Access the root user of the mysql container
```docker exec -it mysql-container mysql -u root -p```

### 4. Enter the password
rootpassword

### 5. Run the database script to populate the database with sample data
```source /database.sql;```

### 6. Accessing phpadmin
http://localhost:8081

### 7. Accessing the dashboard
http://localhost:8080/index.php

### Credentials
email: admin@schoolmanagement.com
password: adminpassword

## TECHNOLOGIES USED

### 1. Docker for containerization.
### 2. MySQL database for storing school/user data.
### 3. PHP for server side logic, role assignment.
### 4. Bootstram as the frontend framework used for design and styling.


## TROUBLESHOOTING
```docker-compose restart```



