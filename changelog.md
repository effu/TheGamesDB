# PHP
Enabled GD library
Enabled short_open_tags

# PHP mysql_ function to PDO statements
Change this
`mysql_fetch_object($searchQuery)`
regex `mysql_fetch_object\((\$[a-zA-Z_]*)\)`
To this
`$searchQuery->fetch(PDO::FETCH_OBJ)`
regex `$1->fetch(PDO::FETCH_OBJ)`

Change this
`$faResult->fetch(PDO::FETCH_ASSOC)`
regex `mysql_fetch_object\((\$[a-zA-Z_]*)\)`
To this
`$searchQuery->fetch(PDO::FETCH_ASSOC)`
regex `$1->fetch(PDO::FETCH_ASSOC)`

Change
`mysql_query($query)`
to this
`$database->query($query)`

change 
`mysql_num_rows`
to 
`count`
