# RestApiGeneratorBundle

[![Build Status](https://travis-ci.org/fludio/rest-api-generator-bundle.svg?branch=master)](https://travis-ci.org/fludio/rest-api-generator-bundle)
[![Coverage Status](https://coveralls.io/repos/github/fludio/rest-api-generator-bundle/badge.svg?branch=master)](https://coveralls.io/github/fludio/rest-api-generator-bundle?branch=master)

An easy way to provide a restful API with CRUD endpoints.

## Install

Install via composer

```
composer require ...
```
Activate the bundle in your AppKernel.

``` php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Fludio\RestApiGeneratorBundle\FludioRestApiGeneratorBundle(),
        // ...
    ];
    
    // ...
}
```

## Configuration

Add those entities to the configuration, that need some endpoints.


``` yaml
// app/config/config.yml

fludio_rest_api_generator:
    entities:
        MyBundle\Entity\Post: ~
        MyBundle\Entity\Comment: ~
```
This will result in the following endpoints:

| Name                                             | Method            | Scheme | Host | Path            |
|--------------------------------------------------|-------------------|--------|------|-----------------|
| fludio.rest_api_generator.index.post             | GET               | ANY    | ANY  | /posts          |
| fludio.rest_api_generator.show.post              | GET               | ANY    | ANY  | /posts/{id}     |
| fludio.rest_api_generator.create.post            | POST              | ANY    | ANY  | /posts          |
| fludio.rest_api_generator.update.post            | PUT &#124; PATCH  | ANY    | ANY  | /posts/{id}     |
| fludio.rest_api_generator.batch\_update.post     | PUT &#124; PATCH  | ANY    | ANY  | /posts          |
| fludio.rest_api_generator.delete.post            | DELETE            | ANY    | ANY  | /posts/{id}     |
| fludio.rest_api_generator.batch\_delete.post     | DELETE            | ANY    | ANY  | /posts          |
| fludio.rest_api_generator.index.comment          | GET               | ANY    | ANY  | /comments       |
| fludio.rest_api_generator.show.comment           | GET               | ANY    | ANY  | /comments/{id}  |
| fludio.rest_api_generator.create.comment         | POST              | ANY    | ANY  | /comments       |
| fludio.rest_api_generator.update.comment         | PUT &#124;  PATCH | ANY    | ANY  | /comments/{id}  |
| fludio.rest_api_generator.batch\_update.comment  | PUT &#124;  PATCH | ANY    | ANY  | /comments       |
| fludio.rest_api_generator.delete.comment         | DELETE            | ANY    | ANY  | /comments/{id}  |
| fludio.rest_api_generator.batch\_delete.comment  | DELETE            | ANY    | ANY  | /comments       |

## TODO

- [x] Provide basic endpoints
- [x] Provide endpoint for batch update
- [x] Provide endpoint for batch delete
- [x] Search filter
- [x] Pagination
- [x] Add security options
- [x] Dynamic FormTypes
- [x] Listener to format all DateTimes to a specific format
- [x] Better error messages
- [x] Streamline returned Response
  - [ ] Add metadata (pagination links, total?)
- [ ] defaults: _format: json|xml
- [ ] Access entities by something else than the id
- [ ] Resource Actions as classes (polymorphic)
- [ ] Nested Resources
- [ ] Configuration Options
  - [x] Define which endpoints should be created (only|except)
  - [x] Override resource name
  - [ ] Enable/Disable NelmioApiDoc
  - [ ] Set another Controller
  - [ ] Configure bundle prefix
- [x] NelmioApiDocBundle integration
  - [x] Basic integreation
  - [x] Show security setting
  - [x] Better description
  - [x] Add Filter Params
  - [x] Add Pagination
  - [ ] Better descriptions 
- [ ] XML-Support
- [ ] Add hooks/ events
- [ ] Documentation
  - [ ] Route selection
  - [ ] Resource name
  - [ ] Filter/Pagination
  - [ ] Security section
  - [ ] DateTimeFormatListener
- [ ] Provide endpoint for batch create (?)

Bugs:
- ResourceActionData::getActionFromRoute needs to detect prefix
- BetweenFilterType for ApiDoc