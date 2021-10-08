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
# Account can be found in the URL: https://[account].snowflakecomputing.com/
$client->setAccount('<account>');
# Username
$client->setUser('<username>');
# Public key from step 3
$client->setPublicKey('<publicKey>');
# Private key from step 1. Must be the path of the file.
$client->setPrivateKey('rsa_key.pem');
# This command will generate the JWT token. First parameter is the number of seconds the token will expire.
$client->setToken();

$service = $client->getService();
# Warehouse you want to use. Not required when default Warehouse is set in Snowflake for this user.
$service->setWarehouse('<warehouse>');
# Database you want to use. Not required when default Database is set in Snowflake for this user. 
$service->setDatabase('<database>');
# Schema you want to use. Not required when default Schema is set in Snowflake for this user.
$service->setSchema('<schema>');
# Role you want to use. Not required when default Role is set in Snowflake for this user.
$service->setRole('<role>');
```

Optional configuration:

```php
# Set too true to execute the statement asynchronously and return the statement handle. Default false
$service->setAsync(false); 
# Set too false to return a SQL NULL value as the string "null", rather than as the value null. Default: true
$service->setNullable(false);
```

To execute the statement:

```php
$result = $service->postStatement($statement);
```

The statement is executed and the results are returned as ```Result``` object. If asynchronously is not specified or is set to false (and if the execution is completed in 45 seconds), then you will get a ```Result``` object with ```isExecuted()``` is set to true. Properties from the ```Result``` object:

```php
# The "Query ID" from Snowflake.
$result->getId();
# Total number of results (NULL when `isExecuted` is false).
$result->getTotal();
# Current page (NULL when `isExecuted` is false).
$result->getPage();
# Total number of pages (NULL when `isExecuted` is false).
$result->getPageTotal();
# Get fields (NULL when `isExecuted` is false).
$result->getFields();
# Get the raw data (NULL when `isExecuted` is false).
$result->getDataRaw();
# Get the data converted to PHP variables (NULL when `isExecuted` is false).
#
# The fields are converted and type juggling because:
# Boolean is returned as string, "0" will be false and "1" will be true.
# Number is returned as string, will be converted to float or int
# Date is returned as integer (in a string) of the number of days since the Epoch. For example: 18262. Will be converted to DateTime object.
# Time is returned as float (in a string with 9 decimal places) of the number of seconds since the Epoch. For example: 82919.000000000. Will be converted to DateTime object.
# Time with Timezone is returned as float (in a string with 9 decimal places) of the number of seconds since the Epoch, followed by a space and the time zone offset in minutes. For example: 1616173619000000000 960. Will be converted to DateTime object.
$result->getData();
# Get `DateTime` object when statement is executed.
$result->getTimestamp();
# Check if the statement is executed.
$result->isExecuted();
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