# Basic JSON REST API example based on Slim.

I'm trying to set this up as a working starting point for a more custom API. Should also be extendable by following [Slim documentation](https://www.slimframework.com/docs/)

## Example Endpoints:

Method | Endpoint | Result
--- | --- | ---
GET | `/api/v1/things` | lists all the things
POST | `/api/v1/things` | creates a thing, returns the new thing
GET | `/api/v1/things/{id}` | returns a certain thing
PATCH | `/api/v1/things/{id}` | updates specific details on  thing, returns the new thing)
PUT | `/api/v1/things/{id}` | replaces an entire thing, returns the new thing)
POST | `/api/v1/users` | creates a new user, returns the details
GET | `/api/v1/users/{username}` | returns a user's details
PATCH | `/api/v1/users/{username}` | updates specific details on a user (eg. password), returns the
GET | `/api/v1/authenticate` | logs in using HTTP Basic Authorisation and returns an auth token user's details

## Authentication

There are 2 steps to making authorised requests:

1. Get your login token sending HTTP Basic Auth header to the authenticate endpoint (Username & password should be in the format `username:password` and base64 encoded)
2. Send your token in a Bearer Auth header.
```
GET /api/v1/authenticate
Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ

GET /api/v1/resource
Authorization: Bearer yDTVTfpbFlv8K39AgmWBT619sM+udFfulHGFAp07ECU
```

Each request attempts to extract the bearer token, but doesn't attempt to validate it or fetch a user. If you want to do anything with the token, you'll have to do that in the callback.

For simplicity, this example stores user details in a hard-coded array. This should obviously be replaced with a different solution, as should the logic of looking up a valid user.


## Setup notes

* Make sure to install dependencies with Composer
* Config settings which relate to specific environments (eg. database settings) should be defined as properties on the `$slimConfig` array in `inclues/config.php` (git ignored)
