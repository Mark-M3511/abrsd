# User Registration Module

This module provides a custom webform handler that is run when a user submits a custom User Registration webform. This custom webform replaces the default Drupal user registration webform. See the [Webform contrib module](https://drupal.org/project/webform) for more information on creating custom webforms for Drupal sites.

## Overview

- The code implements a **Webform handler** that creates a new user ent2ity when a Webform submission is saved. The handler is defined in the _annotation of the class_, and the _postSave_ method is called when a Webform submission is saved. The _createUser_ method creates a new user entity using the values from the Webform submission. These are exapmples of 2 of the methods used to integrate features/functions of the webform module with Drupal user services.
- The UserRegistration class extends WebformHandlerBase and implements the **postSave** method. This method is called after the webform submission is saved. The **postSave** method receives the WebformSubmissionInterface object and a boolean value indicating whether the submission is an update.
- The **postSave** method gets the values from the submission and calls the createUser method to create a new user. The createUser method creates a new user entity, sets the username, email, and password, and saves the user.
- The **UserRegistration** class is annotated with the **@WebformHandler** annotation. This annotation provides metadata about the handler, such as the ID, label, category, description, cardinality, and results.
- The **UserRegistrationHelper** class is a helper class that assists with user registration. It contains methods for creating and updating user accounts, as well as updating a user's profile picture.
- The **UserRegistrationRedirectSubscriber** class is an event subscriber that listens for the **KernelEvents::REQUEST** event in a Drupal application. When such an event is dispatched, this class performs a redirect action based on the current path and user status.


## Features

- Custom user registration form
- Automatic user creation with form submission
- Customizable user roles
- Error logging

## Usage

1. Install the module in your Drupal installation.
2. Navigate to the module's settings to configure the user roles.
3. Use the provided form for user registration.

## UserRegistration Class Methods

- `__construct()`: This is the constructor method for the UserRegistration class. It initializes several services and properties used throughout the class.

- `create()`: This static method is used to create a new instance of the UserRegistration class. It retrieves necessary services from the container and passes them to the constructor.

- `preSave()`: This method is called before a webform submission is saved. It checks if a user with the submitted email address already exists and throws an exception if so.

- `postSave()`: This method is called after a webform submission is saved. It checks the form id and calls the appropriate helper method to add or update the user account.

- `searchUserByEmail()`: This method takes an email address as a parameter and searches for a user with that email. It returns the user ID if found, or NULL if not.

- `getSubmissionId()`: This method takes a user ID as a parameter and retrieves the associated submission ID. It returns the submission ID if found, or NULL if not.

Remember to update this section if you add, remove, or change methods in the `UserRegistration` class.


## UserRegistration Class Dependencies

The `UserRegistration` class in our Drupal module depends on several core services. These are injected into the class through the constructor. Here's a list of those dependencies:

- `LanguageManagerInterface`: This is used for handling language-related functionality.
- `ConfigFactoryInterface`: This is used for accessing configuration settings.
- `PasswordGeneratorInterface`: This is used for generating passwords.
- `LoggerInterface`: This is used for logging messages.
- `AccountProxyInterface`: This is used for getting information about the currently logged-in user.
- `EntityTypeManagerInterface`: This is used for managing entities. In this class, it's specifically used for managing file entities.

Each of these dependencies should be passed to the `UserRegistration` class's constructor when creating a new instance of the class.

## UserRegistrationHelper Class Methods

- `__construct(User $user, WebformSubmissionInterface $webform_submission, UserRegistration $userRegistration)`: This is the constructor method. It initializes the `User`, `WebformSubmissionInterface`, and `UserRegistration` objects.

- `addUserAccount()`: This method adds a new user account. It retrieves form data, checks if a user with the submitted email address already exists, and creates a new user account if not.

- `updateUserAccount()`: This method updates an existing user account with the provided values. It retrieves form data, checks if the current user has permission to update the account, and updates the user account if so.

- `updateProfilePicture(?int $fid, User $user)`: This method updates the profile picture of a user. It takes a file ID and a `User` object as parameters, and updates the user's profile picture with the provided file.

- `createUserAccount(array $values): ?User`: This method creates a new user account with the provided values. It takes an array of values as a parameter, creates a new `User` object with these values, and returns the `User` object.

## UserRegistrationRedirectSubscriber Class Methods

- `__construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger)`: This is the constructor method. It initializes the `ConfigFactoryInterface` and `LoggerChannelFactoryInterface` objects.

- `create(ContainerInterface $container)`: This static method creates a new instance of the `UserRegistrationRedirectSubscriber` class. It retrieves necessary services from the container and passes them to the constructor.

- `getSubscribedEvents()`: This static method returns an array mapping event names to method names. This tells Drupal which method to call when a certain event is dispatched.

- `onRedirectUserRegister(RequestEvent $event)`: This method is called whenever the `KernelEvents::REQUEST` event is dispatched. It checks if the current path is in the allowed paths and if the user is anonymous. If both conditions are met, it redirects the user to a specified path.

- `getRedirectPathFromConfig(string $path, string $config_name)`: This method retrieves the redirect path from a configuration based on the given path and configuration name. It returns the redirect path if found, or null if not found.

Please note that the actual behavior of these methods might be different based on the rest of your codebase.


## File Structure

```
abrsd_user_registration/
├── abrsd_user_registration.info.yml
├── abrsd_user_registration.install
├── abrsd_user_registration.libraries.yml
├── abrsd_user_registration.module
├── abrsd_user_registration.services.yml
├── README>md
├── config/
│   ├── install/ (*.yml)
│   ├── schema/ (*.yml)
├── css/
├── js/
├── src/
│   ├── EventSubscriber/ (*.php)
│   │   ├── UserRegistrationRedirectSubscriber.php
│   ├── Helper/ (*.php)
│   │   ├── UserRegistrationHelper.php
│   ├── Plugin/ (*.php)
│   │   ├── WebformHandler/
│   │   │   ├── UserRegistration.php
├── tests/
│   ├── src/
│   │   ├── Functional/
│   │   │   ├── AbrsdUserRegistrationTest.php

```
## Contributing

Please read CONTRIBUTING.md for details on our code of conduct, and the process for submitting pull requests to us.

## License

This project is licensed under the MIT License - see the LICENSE.md file for details
