### Status
This is old and probably does not work. I do not recommend using this.

### Introduction

This fork began long ago, at a time when the API was more difficult to use. The API is much simpler now. This code was modified to work with the new API, but it's incomplete. However, it works, and is a decent start.

This is a fork of the original Fantasy Football Nerd API. It follows their license.

* Their site:       <http://www.fantasyfootballnerd.com/>
* The official API: <http://www.fantasyfootballnerd.com/fantasy-football-api>

### How to use the demo/example

* Get an API key
* Add this key to sdk.php, as the API_KEY constant
* Execute sdk.php

### Example

After modifying (adding) your API key, you could use PHP's internal web server to test via `sdk.php` like so:

````
$ php -S localhost:8888 sdk.php
````

Then, open your browser to `localhost:8888`
