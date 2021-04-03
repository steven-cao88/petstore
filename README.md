# REST API for a pet store

REST API for a pet store using a microservices architecture style.

The API requirements based on this example https://petstore.swagger.io/

The API is implemented using Laravel Lumen, a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax.

## Structure

There are 4 main components in this pet store: Authentication, User, Pet, and Order. For simplicity and demo purposes, all these components are developed in a same repository here, but they can be easily separated into different services, which can be either based on the same Lumen framework or on any other stack (Express, etc.)

## Assumption
This API is for a store that is mainly maintained by store admins, who have CRUD access to all endpoints.

## Suggested Improvements
In future, maybe provide APIs to turn this into multi-tenant store, where normal users can manage their own pets and orders.

## Security Vulnerabilities

If you discover a security vulnerability with this API, please send an e-mail to Steven Cao at mailboxofsteven@gmail.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
