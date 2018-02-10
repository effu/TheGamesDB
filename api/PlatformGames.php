<?php
## Interface that allows clients to get
## platform list using platform
## Parameters:
##   $_REQUEST["platform"]
##   $_REQUEST["language"]        (optional)
##   $_REQUEST["user"]            (optional... overrides language setting)
##
## Returns:
##   XML items holding the games that matches the platform string

## Include functions, db connection, etc
include "include.php";

## Prepare the search string
$platform = addslashes(stripslashes(stripslashes($_REQUEST["platform"])));
//$platform = str_replace(array(' - ', '-'), '%', $platform);
$platform = $_REQUEST["platform"];

//$language        = $_REQUEST["language"];
$user = $_REQUEST["user"];

if (empty($platform)) {
    print "<Error>A platform is required</Error>\n";
    exit;
}

$query;
if (isset($platform) && !empty($platform)) {
    if ($platformQuery = $database->query(" SELECT id FROM platforms WHERE name = '$platform' ")) {
        if ($platformResult = $platformQuery->fetch(PDO::FETCH_OBJ)) {
            $platformid = $platformResult->id;
            $query = " SELECT id FROM games WHERE platform = $platformid ";
        }
    }
}

$result = $database->query($query) or die('Query failed: ' . mysql_error());

print "<Data>\n";
while ($obj = $result->fetch(PDO::FETCH_OBJ)) {
    print "<Game>\n";

    // Base Info
    $subquery = "SELECT games.id, games.GameTitle, games.ReleaseDate FROM games WHERE games.id={$obj->id}";
    $baseResult = $database->query($subquery) or die('Query failed: ' . mysql_error());
    $baseObj = $baseResult->fetch(PDO::FETCH_OBJ);
    foreach ($baseObj as $key => $value) {
        ## Prepare the string for output
        if (!empty($value)) {
            $value = xmlformat($value, $key);
            if ($key == "platform") {
                $key = "Platform";
            }
            print "<$key>$value</$key>\n";
        }
    }

    // Get Thumbnail if exists
    $thumbQuery = $database->query(" SELECT banners.filename AS thumb FROM games, banners WHERE banners.keyvalue={$obj->id} AND banners.filename LIKE '%front%' LIMIT 1 ");
    if ($thumbResult = $thumbQuery->fetch(PDO::FETCH_OBJ)) {
        $thumb = $thumbResult->thumb;
        print "<thumb>$thumb</thumb>";
    }

    ## End XML item
    print "</Game>\n";
}
?>
</Data>
