# RestApiGeneratorBundle

[![Build Status](https://travis-ci.org/fludio/rest-api-generator-bundle.svg?branch=master)](https://travis-ci.org/fludio/rest-api-generator-bundle)
[![Coverage Status](https://coveralls.io/repos/github/fludio/rest-api-generator-bundle/badge.svg?branch=master)](https://coveralls.io/github/fludio/rest-api-generator-bundle?branch=master)

An easy way to provide a restful API with CRUD endpoints.

## Install

Install via composer

```
composer require ...
```
Then activate the bundle in your AppKernel.

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
And finally, add this to your routing configuration.

``` yaml
// app/config/routing.yml

rest_api_generator:
    resource: "@FludioRestApiGeneratorBundle/Resources/config/routing.yml"
```

With these basic steps the bundle is ready to be used. Now you can generate your entities as you are used to.


## Configuration

Now that you have some entities in your project, add them to the configuration, so that the bundle can generate
some endpoints for these entities.

``` yaml
// app/config/config.yml

fludio_rest_api_generator:
    entities:
        MyBundle\Entity\Post: ~
        MyBundle\Entity\Comment: ~
```
Et voilà, now you have a fully working api. This will result in the following endpoints:

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
  - [x] Add metadata (pagination links, total)
- [ ] Sort via query
- [ ] Expose via query
- [ ] Nested Resources
- [ ] Access entities by something else than the id
- [ ] Resource Actions as classes (polymorphic)
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
- [ ] defaults: _format: json|xml
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