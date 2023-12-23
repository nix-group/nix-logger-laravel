```php

\Log::debug('');
\Log::debug($object);
\Log::debug($array);
\Log::debug($collection);
\Log::debug($exception);


// String
app(NixLogger::class)->debug(['name' => 'Xinh']);
// Array
app(NixLogger::class)->debug('Hello');
// Model/Object
$user = new \App\Models\User();
$user->id = 111;
app(NixLogger::class)->debug($user);
```