# RestApiGeneratorBundle

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
- [x] Add Travis-CI
- [x] Add Coveralls
- [ ] Pagination
- [ ] Search filter
- [x] Add security options
- [ ] Nested Resources
- [ ] Configure returned Response
- [ ] Configuration Options
  - [ ] Set another Controller
  - [x] Define which endpoints should be created (only|except)
  - [x] Override resource name
  - [ ] Configure bundle prefix
- [ ] NelmioApiDocBundle integration
  - [x] Basic integreation
  - [ ] Show security setting
  - [ ] Better description
  - [ ] Add Filter Params
  - [ ] Add Pagination 
- [ ] Add bundle specific EntityNotFoundException
- [ ] Add hooks/ events (?)
- [ ] Provide endpoint for batch create (?)
- [ ] XML-Support
- [ ] Documentation
  - [ ] Security section
