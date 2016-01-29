# Rest Api Bundle

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
        new Fludio\ApiAdminBundle\FludioApiAdminBundle(),
        // ...
    ];
    
    // ...
}
```

## Configuration

Add those entities to the configuration, that need some endpoints.


``` yaml
// app/config/config.yml

fludio_api_admin:
    entities:
        - MyBundle\Entity\Post
        - MyBundle\Entity\Comment
```
This will result in the following endpoints:

| Name                                    | Method            | Scheme | Host | Path            |
|-----------------------------------------|-------------------|--------|------|-----------------|
| fludio.api_admin.index.post             | GET               | ANY    | ANY  | /posts          |
| fludio.api_admin.show.post              | GET               | ANY    | ANY  | /posts/{id}     |
| fludio.api_admin.create.post            | POST              | ANY    | ANY  | /posts          |
| fludio.api_admin.update.post            | PUT &#124; PATCH  | ANY    | ANY  | /posts/{id}     |
| fludio.api_admin.batch\_update.post     | PUT &#124; PATCH  | ANY    | ANY  | /posts          |
| fludio.api_admin.delete.post            | DELETE            | ANY    | ANY  | /posts/{id}     |
| fludio.api_admin.batch\_delete.post     | DELETE            | ANY    | ANY  | /posts          |
| fludio.api_admin.index.comment          | GET               | ANY    | ANY  | /comments       |
| fludio.api_admin.show.comment           | GET               | ANY    | ANY  | /comments/{id}  |
| fludio.api_admin.create.comment         | POST              | ANY    | ANY  | /comments       |
| fludio.api_admin.update.comment         | PUT &#124;  PATCH | ANY    | ANY  | /comments/{id}  |
| fludio.api_admin.batch\_update.comment  | PUT &#124;  PATCH | ANY    | ANY  | /comments       |
| fludio.api_admin.delete.comment         | DELETE            | ANY    | ANY  | /comments/{id}  |
| fludio.api_admin.batch\_delete.comment  | DELETE            | ANY    | ANY  | /comments       |

## TODO

- [x] Provide basic endpoints
- [x] Provide endpoint for batch update
- [x] Provide endpoint for batch delete
- [ ] Pagination
- [ ] Search filter
- [ ] Add security options
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
