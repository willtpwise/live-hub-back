# LiveHUB Backend
 ---
REST API & Data store

## Get users
Endpoint `/users/`

Make a post request to `/users/` to query one or more users/

### Options
The following params can be sent as post data
#### Field name
A field name representing an argument for filtering the results
```javascript
{
  id: 1 // Return user 1, only
}
```
```javascript
// Return all users who's first name starts will 'will' and last name is
// exactly 'wise'
{
  first_name: {
    operator: 'LIKE',
    value: 'will%'
  },
  last_name: 'wise'
}
```

#### Ordering
The order object in your post data allows you to control the order of results
```javascript
// Return results in alphabetical order, based on first_name
{
  order: {
    column: ['first_name'],
    direction: 'ASC'
  }
}
```

#### Limiting
Limit the number of results returned using the limit property in your post data
```javascript
{
  limit: 10
}
```
By default the limit for results returned is `25`. You can remove this limit by
passing `-1`.

### Response value
The response value is a JSON object. Within it's body property will be an array
containing your results.

## Create Users





## Create user
```/users/create/```
