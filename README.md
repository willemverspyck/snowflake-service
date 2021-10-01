# Snowflake Api

Snowflake API connector in PHP

## Requirements
PHP ^7.4|^8.0

## Installation
Install it using [Composer](https://getcomposer.org/):

```sh
composer require willemverspyck/snowflake-service
```

### 1. Generate own public and private keys:

```sh
openssl genrsa -out rsa_key.pem 2048
openssl rsa -in rsa_key.pem -pubout -out rsa_key.pub
```

### 2. Set the public key in Snowflake for the user:

Set the public key for the user who will connect with the Snowflake API. Make sure that you put your own public key (rsa_key.pub) in a single line.

```sql
ALTER USER <username> SET rsa_public_key = '<publicKey>';
```

### 3. Get public key from Snowflake:

Run the describe command for the user to get the RSA_PUBLIC_KEY_FP.

```sql
DESC USER <username>;
```

## Example usage
```php
<?php
 
$client = new Client();
$client->setAccount('<account>'); // Account can be found in the URL: https://[account].snowflakecomputing.com/
$client->setUser('<username>'); // Username
$client->setPublicKey('<publicKey>'); // Public key from step 3
$client->setPrivateKey('rsa_key.pem'); // Private key from step 1. Must be the path of the file.
$client->setToken(); // This command will generate the JWT token. First parameter is the number of seconds the token will expire.

$service = $client->getService();
$service->setWarehouse('<warehouse>'); // Warehouse you want to use
$service->setDatabase('<database>'); // Database you want to use
$service->setSchema('<schema>'); // Schema you want to use
$service->setRole('<role>'); // Role you want to use for this statement
```

Optional configuration:

```php
$service->setAsync(false); // Set too true to execute the statement asynchronously and return the statement handle. Default false
$service->setNullable(false); // Set too false to return a SQL NULL value as the string "null", rather than as the value null. Default: true
```

To execute the statement:

```php
$result = $service->postStatement($statement);
```

The statement is executed and the results are returned as ```Result``` object. If asynchronously is not specified or is set to false (and if the execution is completed in 45 seconds), then you will get a ```Result``` object with ```isExecuted()``` is set to true. Properties from the ```Result``` object:

```php
$result->getId(); // The "Query ID" from Snowflake 
$result->getTotal(); // Total number of results
$result->getPage(); // Current page
$result->getPageTotal(); // Total number of pages
$result->getFields(); // Get fields
$result->getData(); // Get data
$result->getTimestamp(); // Get ```DateTime``` object when statement is executed
$result->isExecuted(); // Check if the statement is executed
```

If asynchronously is set to true or the statement execution takes longer than 45 seconds to complete, then ```Result``` object is returned with ```isExecuted()``` set to false. All fields are NULL, except ```getId()```.

You can check the status of the statement with:

```php
$result = $service->getStatement($results->getId());
```

For example: Try every 10 minutes until the statement is executed and the ```isExecuted``` will be true.

To get more results:

```php
$page = 2;

$result = $service->getStatement($results->getId(), $page);
```

To cancel the statement:
```php
$service->cancelStatement($results->getId());
```

```ResultException``` is thrown when something goes wrong or ```ParameterException``` when a required configuration is missing.

More information can be found at https://docs.snowflake.com/en/developer-guide/sql-api/reference.html.