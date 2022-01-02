Toml 0.8.18
===========
Minimal TOML parser.

## How to use Toml from other extensions

This extension provides the following TOML-related functions:

`toml_parse($input)`  
`toml_parse_file($fileName)`  
`toml_parse_url($url)`  

They return the value encoded in appropriate PHP type or `null` on failure. Errors can be accessed in `YellowToml::$error`.

The most used features of TOML are supported: bare keys, dotted bare keys, basic strings, literal strings, numbers, booleans, dates and times (returned as strings), arrays, tables, arrays of tables, and comments.

Not supported are: quoted keys, multiline strings, multiline arrays, inline tables.

## Example

```
$toml = '
# This is a TOML document

title = "TOML Example"

[owner]
name = "Tom Preston-Werner"
dob = 1979-05-27T07:32:00-08:00

[database]
enabled = true
ports = [ 8000, 8001, 8002 ]
data = [ ["delta", "phi"], [3.14] ]
temp_targets.cpu = 79.5
temp_targets.case = 72.0

[servers]

[servers.alpha]
ip = "10.0.0.1"
role = "frontend"

[servers.beta]
ip = "10.0.0.2"
role = "backend"
';

$parsed = toml_parse($toml);
```

This is the same [example used in TOML site](https://toml.io/en/), adapted. Note the writing in two lines of `temp_targets`, since inline tables are not supported.

## Installation

[Download extension](https://github.com/GiovanniSalmeri/yellow-toml/archive/master.zip) and copy zip file into your `system/extensions` folder. Right click if you use Safari.

## Developer

Giovanni Salmeri. [Get help](https://github.com/GiovanniSalmeri/yellow-toml/issues).
