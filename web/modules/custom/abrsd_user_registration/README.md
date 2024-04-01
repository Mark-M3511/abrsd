# User Registration Module

This module provides a custom webform handler that is run when a user submits a custom User Registration webform. This custom webform replaces the default Drupal user registration webform. See the [Webform contrib module](https://drupal.org/project/webform) for more information on creating custom webforms for Drupal sites.

## Overview

- The code implements a **Webform handler** that creates a new user entity when a Webform submission is saved. The handler is defined in the _annotation of the class_, and the _postSave_ method is called when a Webform submission is saved. The _createUser_ method creates a new user entity using the values from the Webform submission.
- The UserRegistration class extends WebformHandlerBase and implements the postSave method. This method is called after the webform submission is saved. The postSave method receives the WebformSubmissionInterface object and a boolean value indicating whether the submission is an update.
- The postSave method gets the values from the submission and calls the createUser method to create a new user. The createUser method creates a new user entity, sets the username, email, and password, and saves the user.
- The UserRegistrationHandler class is annotated with the @WebformHandler annotation. This annotation provides metadata about the handler, such as the ID, label, category, description, cardinality, and results.


## Features

- Custom user registration form
- Automatic user creation with form submission
- Customizable user roles
- Error logging

## Usage

1. Install the module in your Drupal installation.
2. Navigate to the module's settings to configure the user roles.
3. Use the provided form for user registration.

## Methods

- `postSave()`: This method is called after the webform submission is saved. It checks if the user already exists, and if not, it creates a new user.
- `createUser(array $values)`: This method creates a new user with the provided values.

## Dependencies

- Drupal's Language Manager
- Drupal's Config Factory
- Drupal's Password Generator
- Drupal's Logger Factory

## Contributing

Please read CONTRIBUTING.md for details on our code of conduct, and the process for submitting pull requests to us.

## License

This project is licensed under the MIT License - see the LICENSE.md file for details
