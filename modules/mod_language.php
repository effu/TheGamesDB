<?php

#####################################################
## Language stuff
#####################################################
## Get list of languages and store array
global $languages;
global $lid;
$query = "SELECT * FROM languages ORDER BY name";
$result = $database->query($query) or die('Query failed: ' . mysql_error());
while ($lang = $result->fetch(PDO::FETCH_OBJ)) {
    $languages[$lang->id] = $lang->name;
}

## Set the default language
if (!isset($lid)) {
    if (isset($user) && $user->languageid) {
        $lid = $user->languageid; ## user preferred language
    } else {
        $lid = 1; ## English
    }
}
