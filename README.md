# Codeigniter 4 API Server
Client can register and login, then get school member's info.
 - Provide CRUD Restful API.
 - Reference [this website](https://www.twilio.com/blog/create-secured-restful-api-codeigniter-php) for structure.
 - Use [jwt-framework](https://github.com/web-token/jwt-framework) to authenticate client.
 - In GET members API will return plain data if HTTP Request is XMLHttpRequest; otherwise, return Nested JWT (JWE that JWT as payload).

## Init project

1. Download this project.   
`git clone https://github.com/JessieMosbi/Codeigniter4-API-practice.git`

2. Install both production and development dependencies in this project.   
`composer install`

3. Create .env file.
    ```
    # CI environment setting
    CI_ENVIRONMENT = development

    # CI database setting
    database.default.hostname = 'localhost'
    database.default.database = 'data_api_manage'
    database.default.username = '<your_username>'
    database.default.password = '<your_password>'
    database.default.DBDriver = 'MySQLi'
    database.default.charset  = 'utf8mb4'
    database.default.DBCollat = 'utf8mb4_general_ci'

    database.member.hostname = 'localhost'
    database.member.database = 'member'
    database.member.username = '<your_username>'
    database.member.password = '<your_password>'
    database.member.DBDriver = 'MySQLi'
    database.member.charset  = 'utf8mb4'
    database.member.DBCollat = 'utf8mb4_general_ci'

    # JWT setting
    KEY_FILE_PASSWORD = '<your_key_password>'
    PUBLIC_KEY_FILE = '<your_public_key_path>'
    PRIVATE_KEY_FILE = '<your_pricate_key_path>'

    KEY_FILE_PASSWORD_CLIENT1 = '<client1_key_password>'
    PUBLIC_KEY_FILE_CLIENT1 = '<client1_public_key_path>'
    PRIVATE_KEY_FILE_CLIENT1 = '<client1_pricate_key_path>'

    JWT_ISSUER = '<your_name>'
    JWT_TIME_TO_LIVE = 300 # in second
    ```

## Create Databases and seeder
`php spark db:create`: data_api_manage   
`php spark migrate` (member database will be created in migration)   
`php spark db:seed ClientSeeder`   
`php spark db:seed ZipSeeder`   
`php spark db:seed SchoolSeeder`   
`php spark db:seed StatusSeeder`   
`php spark db:seed UserSeeder`   

## Start API Server
`php spark serve`   
Open browser and connect to http://localhost:8080, you can see the welcome page of CodeIgniter 4.1.9.

## Generate keys for JWT
Use command line   
```
cd app
mkdir Keys
cd Keys

openssl genrsa -des3 -out private.pem 2048
openssl rsa -in private.pem -outform PEM -pubout -out public.pem
openssl genrsa -des3 -out private_client1.pem 2048
openssl rsa -in private.pem -outform PEM -pubout -out public_client1.pem
```

## Use POSTMAN or other tools to test APIs

Seeder: 
|Name    | Email                           | Password  |
|:------:|:-------------------------------:|:---------:|
|test    | test<span>@gmail.com</span>     | 12345678  |

POST http://localhost:8080/login
- Request: application/json
  ```
  {
      "email": "test@gmail.com",
      "password": "12345678"
  }
  ```
- Response: application/json
  ```
  {
      "status": "success",
      "result": {
          "user": {
              "email": "test@gmail.com"
          },
          "access_token": "<JWT>"
      }
  }
  ```

Put token in HTTP Header `Authorization: Bearer Token <JWT>`

POST http://localhost:8080/register   
- Request: multipart/form-data
  ```
  avatar (optional file)
  name = Jessie
  email = test2@gmail.com
  password = 12345678
  passconf = 12345678
  ```
- Response
  ```
  {
      "status": "success",
  }
  {
      "status": "fail",
      "message": {
          "email": "email is duplicated"
      }
  }
  ```

PATCH http://localhost:8080/client   
- Request: multipart/form-data
  ```
  avatar (optional file)
  name = Jessie
  ```
- Response
  ```
  {
      "status": "success",
  }
  ```

DELETE http://localhost:8080/client   
- Response
  ```
  {
      "status": "success",
  }
  ```

GET http://localhost:8080/members   
- Request
  ```
  {
      "status": 2,
      "school": "<6 characters>"
  }
  ```
- Response
  ```
  {
      "status": "success",
      "result": "<plain result>"
  }
  {
      "status": "success",
      "result": "<Nested JWT>",
      "decode": "<plain result from Nested JWT>"
  }
  ```
