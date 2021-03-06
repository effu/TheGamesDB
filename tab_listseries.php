<?php	## Handle searches differently
if ($_SESSION['userid'] && !$alllang) {
    $languagelimit = "AND languageid = (SELECT languageid FROM users WHERE id = " . $_SESSION['userid'] . ")";
    $query = "SELECT languages.name FROM users INNER JOIN languages ON users.languageid = languages.id WHERE users.id=" . $_SESSION['userid'];
    $result = $database->query($query) or die('Query failed: ' . mysql_error());
}

if ($function == 'Search') {
    $title = 'Search : ' . $string;
}
if ($function == 'Advanced Search') {
    $title = 'Advanced Search_';
} elseif ($function == 'OverviewSearch') {
    $title = 'Overview Search : ' . $string;
} else {
    $title = $letter;
}

include 'simpleimage.php';
function imageResize($filename, $cleanFilename, $target)
{
    if (!file_exists($cleanFilename)) {
        $dims = getimagesize($filename);
        $width = $dims[0];
        $height = $dims[1];
        //takes the larger size of the width and height and applies the formula accordingly...this is so this script will work dynamically with any size image
        if ($width > $height) {
            $percentage = ($target / $width);
        } else {
            $percentage = ($target / $height);
        }

        //gets the new value and applies the percentage, then rounds the value
        $width = round($width * $percentage);
        $height = round($height * $percentage);

        $image = new SimpleImage();
        $image->load($filename);
        $image->resize($width, $height);
        $image->save($cleanFilename);
        $image = null;
    }
    //returns the new sizes in html image tag format...this is so you can plug this function inside an image tag and just get the
    return "src=\"$baseurl/$cleanFilename\"";
}
?>

	<?php
if (isset($user->favorites_displaymode)) {
    $searchview = $user->favorites_displaymode;
}
//If there isn't a search view set then make one default
if ($searchview != "tile" && $searchview != "boxart" && $searchview != "banner" && $searchview != "table" && $searchview != "listing") {
    $searchview = "listing";
}
?>

	<?php	## Run the games query
$gamecount = 0;
$string = mysql_real_escape_string($string);
$letter = mysql_real_escape_string($letter);

if ($function == 'Search') {

    /*$query = "SELECT g.*, ( MATCH (g.GameTitle) AGAINST ('$string') OR g.GameTitle SOUNDS LIKE '$string' ) AS MatchValueBoolean, MATCH (g.GameTitle) AGAINST ('$string') AS MatchValue, p.id AS platformid, p.alias AS PlatformAlias, p.name, p.icon FROM games AS g, platforms AS p WHERE (( MATCH (g.GameTitle) AGAINST ('$string') OR g.GameTitle SOUNDS LIKE '$string' ) OR ( MATCH (g.Alternates) AGAINST ('$string') OR g.Alternates SOUNDS LIKE '$string' )) AND g.Platform = p.id HAVING MatchValueBoolean > 0 ";
    if(!empty($sortBy) && $sortBy != "relevance")
    {
    $query .= " ORDER BY $sortBy, GameTitle ASC";
    }
    else
    {
    $query .= " ORDER BY MatchValue DESC, MatchValueBoolean DESC";
    }*/

    // Set Initial Elasticsearch Search Parameters
    $searchParams = array();
    $searchParams['index'] = 'thegamesdb';
    $searchParams['type'] = 'game';

    if (!empty($page) && !empty($limit)) {
        $searchParams['from'] = ($page - 1) * $limit;
        $searchParams['size'] = $limit;
    } else {
        $searchParams['from'] = 0;
        $searchParams['size'] = 20;
    }

    $searchterm = $string;

    // Check if $search term contains an integer
    if (strcspn($searchterm, '0123456789') != strlen($searchterm)) {
        // Extract first number found in string
        preg_match('/\d+/', $searchterm, $numbermatch, PREG_OFFSET_CAPTURE);
        $numberAsNumber = $numbermatch[0][0];

        // Convert Number to Roman Numerals
        $numberAsRoman = romanNumerals($numberAsNumber);

        // Replace Number in string with RomanNumerals
        $searchtermRoman = str_replace($numberAsNumber, $numberAsRoman, $searchterm);

        $json = '{
					      "query": {
					        "bool": {
					          "must": [
					            {
					              "match": {
					                "GameTitle": "' . $searchterm . '"
					              }
					            },
					            {
					              "match": {
					                "GameTitle": "' . $searchtermRoman . '"
					              }
					            }
					          ]
					        }
					      }
					    }';
        $searchParams['body'] = $json;
    } else {
        $json = '{
					      "query": {
					        "bool": {
					          "must": [
					            {
					              "match": {
					                "GameTitle": "' . $searchterm . '"
					              }
					            }
					            ' . $searchPlatform . '
					          ]
					        }
					      }
					    }';
        $searchParams['body'] = $json;
    }

    $searchParams['sort'] = '"PlatformName": { "order": "asc" }';
}
## Start Advanced Search Query
elseif ($function == 'Advanced Search') {
    /*$query = "SELECT g.*, ( MATCH (g.GameTitle) AGAINST ('$string') OR g.GameTitle SOUNDS LIKE '$string' ) AS MatchValueBoolean, MATCH (g.GameTitle) AGAINST ('$string') AS MatchValue, p.id AS platformid, p.alias AS PlatformAlias, p.name, p.icon FROM games AS g, platforms AS p WHERE (( MATCH (g.GameTitle) AGAINST ('$string') OR g.GameTitle SOUNDS LIKE '$string' ) OR ( MATCH (g.Alternates) AGAINST ('$string') OR g.Alternates SOUNDS LIKE '$string' )) AND g.Platform = p.id HAVING MatchValueBoolean > 0 ";
    if($stringPlatform != "")
    {
    $query = $query .  " AND g.Platform = '$stringPlatform' ";
    }
    if($stringRating != "")
    {
    $query = $query .  " AND g.Rating = '$stringRating' ";
    }
    if($stringGenres != "")
    {
    $query = $query .  " AND g.Genre LIKE '%$stringGenres%' ";
    }
    if($stringCoop != "")
    {
    $query = $query .  " AND g.coop = '$stringCoop' ";
    }
    if(!empty($sortBy) && $sortBy != "relevance")
    {
    //$query .= " ORDER BY $sortBy, GameTitle ASC";
    //$searchParams['sort'] = '{ "PlatformName": { "order": "asc" }, _score }';
    //echo "<script>alert('sorting by $sortBy'); </script>";
    }
    else
    {
    $query .= " ORDER BY MatchValue DESC, MatchValueBoolean DESC";
    }*/

    // Set Initial Elasticsearch Search Parameters
    $searchParams = array();
    $searchParams['index'] = 'thegamesdb';
    $searchParams['type'] = 'game';

    if (!empty($page) && !empty($limit)) {
        $searchParams['from'] = ($page - 1) * $limit;
        $searchParams['size'] = $limit;
    } else {
        $searchParams['from'] = 0;
        $searchParams['size'] = 20;
    }

    $searchPlatform = "";
    if (!empty($stringPlatform)) {
        $searchPlatform = ',
						            "filter": {
										term: {
						              		"PlatformId": "' . $stringPlatform . '"
										}
						            }
						          ';
    }
    $searchRating = "";
    if (!empty($stringRating)) {
        $searchRating = ',{
						            "match": {
						              "Rating": "' . $stringRating . '"
						            }
						          }';
    }
    $searchGenres = "";
    if (!empty($stringGenres)) {
        $searchGenres = ',{
						            "match": {
						              "Genre": "' . $stringGenres . '"
						            }
						          }';
    }
    $searchCoop = "";
    if (!empty($stringCoop)) {
        $searchCoop = ',{
						            "match": {
						              "coop": "' . $stringCoop . '"
						            }
						          }';
    }

    $searchterm = $string;

    // Check if $search term contains an integer
    if (strcspn($searchterm, '0123456789') != strlen($searchterm)) {
        // Extract first number found in string
        preg_match('/\d+/', $searchterm, $numbermatch, PREG_OFFSET_CAPTURE);
        $numberAsNumber = $numbermatch[0][0];

        // Convert Number to Roman Numerals
        $numberAsRoman = romanNumerals($numberAsNumber);

        // Replace Number in string with RomanNumerals
        $searchtermRoman = str_replace($numberAsNumber, $numberAsRoman, $searchterm);

        $json = '{
					      "query": {
					        "bool": {
					          "must": [
					            {
					              "match": {
					                "GameTitle": "' . $searchterm . '"
					              }
					            },
					            {
					              "match": {
					                "GameTitle": "' . $searchtermRoman . '"
					              }
					            }
					          ]
					        }
					      }
					    }';
        $searchParams['body'] = $json;
    } else {
        $json = '{
					      "query": {
					        "bool": {
					          "must": [
					            {
					              "match": {
					                "GameTitle": "' . $searchterm . '"
					              }
					            }
					            ' . $searchRating . $searchGenres . $searchCoop . '
					          ]
					        }
					      }' . $searchPlatform . '
					    }';
        $searchParams['body'] = $json;
    }

    $searchParams['sort'] = '"PlatformName": { "order": "asc" }';
}
## End Advanced Search Query

$elasticResults = $elasticsearchClient->search($searchParams);
?>

	<!-- Start Pagination -->
	<?php

$adjacents = 3;

/*
First get total number of rows in data table.
If you have a WHERE clause in your query, make sure you mirror it here.
 */
//$total_pages = count($database->query($query));
$total_pages = $elasticResults['hits']['total'];

/* Setup vars for query. */
if (!isset($limit)) {
    $limit = 20; //how many items to show per page
}
if ($page) {
    $start = ($page - 1) * $limit;
}
//first item to display on this page
else {
        $start = 0;
    }
    //if no page var is given, set start to 0

    /* Get data. */
    $query = $query . " LIMIT $start, $limit";

    /* Setup page vars for display. */
    if ($page == 0) {
        $page = 1;
    }
    //if no page var is given, default to 1.
    $prev = $page - 1; //previous page is page - 1
    $next = $page + 1; //next page is page + 1
    $lastpage = ceil($total_pages / $limit); //lastpage is = total pages / items per page, rounded up.
    $lpm1 = $lastpage - 1; //last page minus 1

    /*
    Now we apply our rules and draw the pagination object.
    We're actually saving the code to a variable in case we want to draw it more than once.
     */
    $pagination = "";
    if ($lastpage > 1) {
        $pagination .= "<div class=\"pagination\">";
        //previous button
        if ($page > 1) {
            $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$prev&stringPlatform=$stringPlatform\">&laquo; prev</a>";
        } else {
            $pagination .= "<span class=\"disabled\">&laquo; prev</span>";
        }

        //pages
        if ($lastpage < 7 + ($adjacents * 2)) //not enough pages to bother breaking it up
    {
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page) {
                    $pagination .= "<span class=\"current\">$counter</span>";
                } else {
                    $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$counter&stringPlatform=$stringPlatform\">$counter</a>";
                }

            }
        } elseif ($lastpage > 5 + ($adjacents * 2)) //enough pages to hide some
    {
        //close to beginning; only hide later pages
        if ($page < 1 + ($adjacents * 2)) {
            for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                if ($counter == $page) {
                    $pagination .= "<span class=\"current\">$counter</span>";
                } else {
                    $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$counter&stringPlatform=$stringPlatform\">$counter</a>";
                }

            }
            $pagination .= "...";
            $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$lpm1&stringPlatform=$stringPlatform\">$lpm1</a>";
            $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$lastpage&stringPlatform=$stringPlatform\">$lastpage</a>";
        }
        //in middle; hide some front and some back
        elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
            $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=1&stringPlatform=$stringPlatform\">1</a>";
            $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=2&stringPlatform=$stringPlatform\">2</a>";
            $pagination .= "...";
            for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                if ($counter == $page) {
                    $pagination .= "<span class=\"current\">$counter</span>";
                } else {
                    $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$counter&stringPlatform=$stringPlatform\">$counter</a>";
                }

            }
            $pagination .= "...";
            $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$lpm1&stringPlatform=$stringPlatform\">$lpm1</a>";
            $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$lastpage&stringPlatform=$stringPlatform\">$lastpage</a>";
        }
        //close to end; only hide early pages
        else {
                $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=1&stringPlatform=$stringPlatform\">1</a>";
                $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=2&stringPlatform=$stringPlatform\">2</a>";
                $pagination .= "...";
                for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                    if ($counter == $page) {
                        $pagination .= "<span class=\"current\">$counter</span>";
                    } else {
                        $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$counter&stringPlatform=$stringPlatform\">$counter</a>";
                    }

                }
            }
        }

        //next button
        if ($page < $counter - 1) {
            $pagination .= "<a href=\"$baseurl/search/?searchview=$searchview&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$next&stringPlatform=$stringPlatform\">next &raquo;</a>";
        } else {
            $pagination .= "<span class=\"disabled\">next &raquo;</span>";
        }

        $pagination .= "</div>";
    }
    ?>
				<!-- End Pagination -->

				<div id="gameHead">

				<?php if ($errormessage): ?>
				<div class="error"><?=$errormessage?></div>
				<?php endif;?>
	<?php if ($message): ?>
	<div class="message"><?=$message?></div>
	<?php endif;?>

	<h1 style="float: left;">Search: <?=$string?></h1>

	<!-- Start View Mode Links -->
	<div>
		<div style="width: 80px; text-align: center; float: right;">
			<a href="<?="$baseurl/search/?searchview=table&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>"><img src="<?=$baseurl?>/images/common/icons/viewicons/table.png" alt="table"/></a>
			<p style="margin-top: 2px;"><a href="<?="$baseurl/search/?searchview=table&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>" style="color: #dd4400">Table</a></p>
		</div>
		<div style="width: 80px; text-align: center; float: right;">
			<a href="<?="$baseurl/search/?searchview=banner&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>"><img src="<?=$baseurl?>/images/common/icons/viewicons/banner.png" alt="banner"/></a>
			<p style="margin-top: 2px;"><a href="<?="$baseurl/search/?searchview=banner&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>" style="color: #dd4400">Banner</a></p>
		</div>
		<div style="width: 80px; text-align: center; float: right;">
			<a href="<?="$baseurl/search/?searchview=boxart&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>"><img src="<?=$baseurl?>/images/common/icons/viewicons/boxart.png" alt="boxart"/></a>
			<p style="margin-top: 2px;"><a href="<?="$baseurl/search/?searchview=boxart&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>" style="color: #dd4400">Boxart</a></p>
		</div>
		<div style="width: 80px; text-align: center; float: right;">
			<a href="<?="$baseurl/search/?searchview=tile&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>"><img src="<?=$baseurl?>/images/common/icons/viewicons/tile.png" alt="tile"/></a>
			<p style="margin-top: 2px;"><a href="<?="$baseurl/search/?searchview=tile&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>" style="color: #dd4400">Tile</a></p>
		</div>
		<div style="width: 80px; text-align: center; float: right;">
			<a href="<?="$baseurl/search/?searchview=listing&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>"><img src="<?=$baseurl?>/images/common/icons/viewicons/listing.png" alt="listing"/></a>
			<p style="margin-top: 2px;"><a href="<?="$baseurl/search/?searchview=listing&string=$string&function=$function&sortBy=$sortBy&limit=$limit&page=$page&updateview=yes&stringPlatform=$stringPlatform"?>" style="color: #dd4400">Listing</a></p>
		</div>
		<div style="clear: both;"></div>
	</div>
	<!-- End View Mode Links -->

	<!-- Start Advanced Search Form -->
	<div style="width: 80%; margin: auto; margin-bottom: 12px;">
		<?php
if ($function == "Advanced Search") {
    ?>
		<a href="javascript: void();" onclick="$('#advancedSearchPanel').slideToggle(); if($('#chevron').attr('src') == '<?=$baseurl;?>/images/common/icons/expand_16.png') { $('#chevron').attr('src', '<?=$baseurl;?>/images/common/icons/collapse_16.png'); } else { $('#chevron').attr('src', '<?=$baseurl;?>/images/common/icons/expand_16.png'); }" style="text-decoration: none; outline: 0px; color: #EF5F00; font-weight: bold;">Advanced Search <img id="chevron" src="<?=$baseurl?>/images/common/icons/collapse_16.png" alt="Expand/Collapse" style="vertical-align:middle;" /></a>
		<div id="advancedSearchPanel" style="border: 1px solid #666; background-color: #999; padding: 15px; border-radius: 10px; color: #FFF; font-weight: bold;">
			<?php
} else {
    ?>
		<a href="javascript: void();" onclick="$('#advancedSearchPanel').slideToggle(); if($('#chevron').attr('src') == '<?=$baseurl;?>/images/common/icons/expand_16.png') { $('#chevron').attr('src', '<?=$baseurl;?>/images/common/icons/collapse_16.png'); } else { $('#chevron').attr('src', '<?=$baseurl;?>/images/common/icons/expand_16.png'); }" style="text-decoration: none; outline: 0px; color: #EF5F00; font-weight: bold;">Advanced Search <img id="chevron" src="<?=$baseurl?>/images/common/icons/expand_16.png" alt="Expand/Collapse" style="vertical-align:middle;" /></a>
		<div id="advancedSearchPanel" style="display: none; border: 1px solid #666; background-color: #999; padding: 15px; border-radius: 10px; color: #FFF; font-weight: bold;">
			<?php
}
?>
			<form method="post" action="<?=$baseurl;?>/search/">
				<table cellspacing="6" width="100%">
					<tr>
						<td>Search: <input type="text" name="string" value="<?php echo $string; ?>" size="40" /></td>
						<td>Platform:
							<select name="stringPlatform">
								<option value="">Any</option>
								<?php
$platformQuery = $database->query(" SELECT * FROM platforms ORDER BY name ASC");
while ($platformResult = $platformQuery->fetch(PDO::FETCH_ASSOC)) {
    ?>
											<option<?php if ($stringPlatform == $platformResult['id']) {echo " selected";}?> value="<?php echo $platformResult['id']; ?>"><?php echo $platformResult['name']; ?></option>
										<?php
}
?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Rating:
							<select name="stringRating">
								<option value="">Any</option>
								<option<?php if ($stringRating == "eC - Early Childhood") {echo " selected";}?>>EC - Early Childhood</option>
                                <option<?php if ($stringRating == "E - Everyone") {echo " selected";}?>>E - Everyone</option>
                                <option<?php if ($stringRating == "E10+ - Everyone 10+") {echo " selected";}?>>E10+ - Everyone 10+</option>
                                <option<?php if ($stringRating == "T - Teen") {echo " selected";}?>>T - Teen</option>
                                <option<?php if ($stringRating == "M - Mature") {echo " selected";}?>>M - Mature</option>
                                <option<?php if ($stringRating == "RP - Rating Pending") {echo " selected";}?>>RP - Pating Pending</option>
							</select>
						</td>
						<td>Genre:
							<select name="stringGenres">
								<option value="">Any</option>
								<?php
$genresQuery = $database->query(" SELECT * FROM genres ");
while ($genresResult = $genresQuery->fetch(PDO::FETCH_ASSOC)) {
    ?>
											<option<?php if ($stringGenres == $genresResult['genre']) {echo " selected";}?> value="<?php echo $genresResult['genre']; ?>"><?php echo $genresResult['genre']; ?></option>
										<?php
}
?>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Co-op:
                            <select name="stringCoop">
								<option value="">Any</option>
								<option<?php if ($stringCoop == "Yes") {echo " selected";}?>>Yes</option>
								<option<?php if ($stringCoop == "No") {echo " selected";}?>>No</option>

							</select>
						</td>
						<td align="right">
                            <input type="hidden" name="searchview" value="<?=$searchview;?>" />
                            <input type="hidden" name="function" value="Advanced Search" />
							<input type="submit" value="Search..."/>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
	<!-- End Advanced Search Form -->

	<!-- Start Sort By -->
	<form method="post" action="<?=$baseurl;?>/search/" style="text-align: right;">
		<input type="hidden" name="searchview" value="<?=$searchview;?>" />
        <input type="hidden" name="function" value="<?=$function?>" />
		<input name="string" type="hidden" value="<?=$string?>" />
		<input name="stringPlatform" type="hidden" value="<?=$stringPlatform?>" />
		<input name="stringRating" type="hidden" value="<?=$stringRating?>" />
		<input name="stringGenres" type="hidden" value="<?=$stringGenres?>" />
		<!--<p style="font-weight: bold;">Sort By: <select name="sortBy" onchange="this.form.submit();">
			<option <?php if ($sortBy == "relevance") {echo "selected";}?> value="relevance">Relevance</option>
			<option <?php if ($sortBy == "GameTitle") {echo "selected";}?> value="g.GameTitle">Name</option>
			<option <?php if ($sortBy == "PlatformName") {echo "selected";}?> value="p.name">Platform</option>
			<option <?php if ($sortBy == "Genre") {echo "selected";}?> value="g.Genre">Genre</option>
			<option <?php if ($sortBy == "Rating") {echo "selected";}?> value="g.Rating">Rating</option>
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;</p>-->
		<p>Show: <select name="limit" onchange="this.form.submit();" style="min-width: 200px">
			<option <?php if ($limit == 10) {echo "selected";}?> value="10">10 Rows</option>
			<option <?php if ($limit == 20) {echo "selected";}?> value="20">20 Rows</option>
			<option <?php if ($limit == 40) {echo "selected";}?> value="40">40 Rows</option>
			<option <?php if ($limit == 80) {echo "selected";}?> value="80">80 Rows</option>
			<option <?php if ($limit == 100) {echo "selected";}?> value="100">100 Rows</option>
		</select></p>
	</form>
	<!-- End Sort By -->

	<div style="clear: both;"></div>

	<?php
##  START RUN SEARCH QUERY!!!!
//$result = $database->query($query) or die('Query failed: ' . mysql_error());
##  END RUN SEARCH QUERY!!!!
?>

	<?php

$increment = "odd";
$counter = 0;
?>
			<div class="bgBlack" style="text-align: center; width: 800px; padding: 15px; margin:30px auto; background-color: #eee; border: 1px solid #666; color: #333;">

		<h3><?php echo $elasticResults['hits']['total']; ?> Games Found</h3>;

		<!-- Start Show Pagination -->
		<?=$pagination?>
		<!-- End Show Pagination -->

		<?
if (count($elasticResults['hits']['hits'])) {
    if ($searchview == "table") {
        ?>
					<table width="100%" border="0" cellspacing="1" cellpadding="7" id="listtable" calss="boxShadow">
						<tr>
							<td class="head" align="center">ID</td>
							<td class="head arcade">Game Title</td>
							<td class="head arcade">Platform</td>
							<td class="head arcade">Year</td>
							<td class="head">Genre</td>
							<td class="head">ESRB</td>
							<td class="head">Boxart</td>
							<td class="head">Fanart</td>
							<td class="head">Banner</td>
						</tr>
			<?php
}
    foreach ($elasticResults['hits']['hits'] as $game) {
        $game = (object) $game['_source'];
        if ($searchview == "listing") {
            if ($boxartResult = $database->query(" SELECT b.filename FROM banners as b WHERE b.keyvalue = '$game->id' AND b.filename LIKE '%boxart%front%' LIMIT 1 ")) {
                $boxart = $boxartResult->fetch(PDO::FETCH_OBJ);
            }
            ?>
							<div class="backgroundGradientGrey boxShadow" style="padding: 10px; margin: 10px; border: 1px solid #333; background-color: #fff; text-align: left !important; border-radius: 6px;">
								<div style="height: 102px; width: 102px; text-align: center; padding-right: 10px; float:left">
								<?php
if ($boxart->filename != "") {
                ?>
									<img <?=imageResize("$baseurl/banners/$boxart->filename", "banners/_favcache/_tile-view/$boxart->filename", 100)?> alt="<?=$game->GameTitle?> Boxart" style="border: 1px solid #666;"/>
								<?php
} else {
                ?>
									<img src="<?=$baseurl?>/images/common/placeholders/boxart_blank.png" alt="<?=$game->GameTitle?> Boxart"  style="width:70px; height: 100px; border: 1px solid #666;"/>
								<?php
}
            ?>
								</div>
								<span style=" float: right; background-color: #333; padding: 7px 6px; border-radius: 6px; border: 1px solid #222; color: #eeeeee; font-weight: bold; margin-left: 10px; box-shadow: 0px 0px 6px #000;">
								<?php
$ratingquery = "SELECT AVG(rating) AS average, count(*) AS count FROM ratings WHERE itemtype='game' AND itemid=$game->id";
            $ratingresult = $database->query($ratingquery) or die('Query failed: ' . mysql_error());
            $rating = $ratingresult->fetch(PDO::FETCH_OBJ);
            for ($i = 2; $i <= 10; $i = $i + 2) {
                if ($i <= $rating->average) {
                    print "<img src=\"$baseurl/images/game/star_on.png\" width=15 height=15 border=0 />";
                } else if ($rating->average > $i - 2 && $rating->average < $i) {
                    print "<img src=\"$baseurl/images/game/star_half.png\" width=15 height=15 border=0 />";
                } else {
                    print "<img src=\"$baseurl/images/game/star_off.png\" width=15 height=15 border=0 />";
                }
            }
            ?>
								</span>
								<?php
if (!empty($game->ReleaseDate)) {
                $releaseDate = null;
                ?>
									<span style=" float: right; background-color: #333; padding: 6px; border-radius: 6px; border: 1px solid #222; color: #eeeeee; font-weight: bold; margin-left: 10px; box-shadow: 0px 0px 6px #000;">
									<?php
if (strlen($game->ReleaseDate) == 4) {
                    echo $game->ReleaseDate;
                } else {
                    $releaseDate = explode('/', $game->ReleaseDate);
                    echo $releaseDate[2];
                }
                ?>
									</span>
								<?php
}
            ?>
								<h3 style="margin-top: 0px;"><a href="<?=$baseurl?>/game/<?=$game->id?>/" style="color: #000;"><?=$game->GameTitle?></a></h3>
								<p style="text-align: justify;"><?php if (!empty($game->Overview)) {echo substr($game->Overview, 0, 300) . "...";} else {echo "<em><br />There is no overview available for this game.</em><br /><br />";}?></p>
									<p style="font-size: 16px; color: #333;"><img src="<?=$baseurl?>/images/common/consoles/png24/<?=$game->PlatformIcon?>" alt="<?=$game->PlatformName?>" style="vertical-align: -6px;" />&nbsp;<a style="color: #000;" href="<?=$baseurl;?>/platform/<?php if (!empty($game->PlatformAlias)) {echo $game->PlatformAlias;} else {echo $game->PlatformId;}?>/"><?=$game->PlatformName?></a>&nbsp;|&nbsp;
								<?php
$boxartQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$game->id' AND banners.filename LIKE '%front%' LIMIT 1");
            $boxartResult = count($boxartQuery);

            $fanartQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$game->id' AND keytype = 'fanart' LIMIT 1");
            $fanartResult = count($fanartQuery);

            $bannerQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$game->id' AND keytype = 'series' LIMIT 1");
            $bannerResult = count($bannerQuery);

            if ($boxartResult != 0) {?>Boxart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" style="vertical-align: -3px;" /> | <?php } else {?>Boxart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" style="vertical-align: -3px;" /> | <?php }
            if ($fanartResult != 0) {?>Fanart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" style="vertical-align: -3px;" /> | <?php } else {?>Fanart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" style="vertical-align: -3px;" /> | <?php }
            if ($bannerResult != 0) {?>Banner:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" style="vertical-align: -3px;" /><?php } else {?>Banner:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" style="vertical-align: -3px;" /><?php }?></p>
								<div style="clear: both;"></div>
							</div>
						<?php
} elseif ($searchview == "tile") {
            if ($boxartResult = $database->query(" SELECT b.filename FROM banners as b WHERE b.keyvalue = '$game->id' AND b.filename LIKE '%boxart%front%' LIMIT 1 ")) {
                $boxart = $boxartResult->fetch(PDO::FETCH_OBJ);
            }
            ?>
									<div class="backgroundGradientGrey boxShadow" style="width: 356px; min-height: 102px; float: left; padding: 10px; margin: 10px; border-radius: 16px; border: 2px solid #333; background-color: #fff;">
										<div style="height: 102px; float:left">
										<?php
if ($boxart->filename != "") {
                ?>
											<img <?=imageResize("$baseurl/banners/$boxart->filename", "banners/_favcache/_tile-view/$boxart->filename", 100)?> alt="<?=$game->GameTitle?> Boxart" style="border: 1px solid #666;"/>
										<?php
} else {
                ?>
											<img src="<?=$baseurl?>/images/common/placeholders/boxart_blank.png" alt="<?=$game->GameTitle?> Boxart"  style="width:70px; height: 100px; border: 1px solid #666;"/>
										<?php
}
            ?>
										</div>
										<h3 style="margin-top: 0px;"><a href="<?=$baseurl?>/game/<?=$game->id?>/" style="color: #000;"><?=$game->GameTitle?></a></h3>
											<p><img src="<?=$baseurl?>/images/common/consoles/png24/<?=$game->PlatformIcon?>" alt="<?=$game->PlatformName?>" style="vertical-align: -6px;" />&nbsp;<a style="color: #000;" href="<?=$baseurl;?>/platform/<?php if (!empty($game->PlatformAlias)) {echo $game->PlatformAlias;} else {echo $game->PlatformId;}?>/"><?=$game->PlatformName?></a></p>
										<?php
$boxartQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$game->id' AND banners.filename LIKE '%front%' LIMIT 1");
            $boxartResult = count($boxartQuery);

            $fanartQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$game->id' AND keytype = 'fanart' LIMIT 1");
            $fanartResult = count($fanartQuery);

            $bannerQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$game->id' AND keytype = 'series' LIMIT 1");
            $bannerResult = count($bannerQuery);

            if ($boxartResult != 0) {?>Boxart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" /> | <?php } else {?>Boxart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" /> | <?php }
            if ($fanartResult != 0) {?>Fanart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" /> | <?php } else {?>Fanart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" /> | <?php }
            if ($bannerResult != 0) {?>Banner:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" /><?php } else {?>Banner:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" /><?php }?>
										<div style="clear: both;"></div>
									</div>
								<?php
if ($increment == "even") {
                ?>
									<div style="clear: both;"></div>
								<?
            }
        } elseif ($searchview == "boxart") {
            if ($boxartResult = $database->query(" SELECT b.filename FROM banners as b WHERE b.keyvalue = '$game->id' AND b.filename LIKE '%boxart%front%' LIMIT 1 ")) {
                $boxart = $boxartResult->fetch(PDO::FETCH_OBJ);
            }
            ?>
									<div class="backgroundGradientGrey boxShadow" style="width: 222px; min-height: 280px; float: left; padding: 10px; margin: 10px; border-radius: 16px; border: 2px solid #333; background-color: #fff;">
										<div style="height: 200px;">
										<?php
if ($boxart->filename != "") {
                ?>
											<img <?=imageResize("$baseurl/banners/$boxart->filename", "banners/_favcache/_boxart-view/$boxart->filename", 200)?> alt="<?=$game->GameTitle?> Boxart" style="border: 1px solid #666;"/>
										<?php
} else {
                ?>
											<img src="<?=$baseurl?>/images/common/placeholders/boxart_blank.png" alt="<?=$game->GameTitle?> Boxart"  style="width:140px; height: 200px; border: 1px solid #666;"/>
										<?php
}
            ?>
										</div>
										<h3><a href="<?=$baseurl?>/game/<?=$game->id?>/" style="color: #000;"><?=$game->GameTitle?></a></h3>
										<p><img src="<?=$baseurl?>/images/common/consoles/png24/<?=$game->PlatformIcon?>" alt="<?=$game->PlatformName?>" style="vertical-align: -6px;" />&nbsp;<a style="color: #000;" href="<?=$baseurl;?>/platform/<?php if (!empty($game->PlatformAlias)) {echo $game->PlatformAlias;} else {echo $game->PlatformId;}?>/"><?=$game->PlatformName?></a></p>
										<div style="clear: both;"></div>
									</div>
								<?php
if ($counter == 2) {
                $counter = 0;
                ?>
									<div style="clear: both;"></div>
								<?
            } else {
                $counter++;
            }
        } elseif ($searchview == "banner") {
            if ($bannerResult = $database->query(" SELECT b.filename FROM banners as b WHERE b.keyvalue = '$game->id' AND b.keytype = 'series' LIMIT 1 ")) {
                $banner = $bannerResult->fetch(PDO::FETCH_OBJ);
            }
            ?>
									<div class="backgroundGradientGrey  boxShadow" style="width: 222px; min-height: 80px; float: left; padding: 10px; margin: 10px; border-radius: 16px; border: 2px solid #333; background-color: #fff;">
										<div style="height: 47px;">
										<?php
if ($banner->filename != "") {
                ?>
											<img <?=imageResize("$baseurl/banners/$banner->filename", "banners/_favcache/_banner-view/$banner->filename", 200)?> alt="<?=$game->GameTitle?> Boxart" style="border: 1px solid #666;"/>
										<?php
} else {
                ?>
											<img src="<?=$baseurl?>/images/common/placeholders/banner_blank.png" alt="<?=$game->GameTitle?> Boxart"  style="width:200px; height: 47px; border: 1px solid #666;"/>
										<?php
}
            ?>
										</div>
										<h3><a href="<?=$baseurl?>/game/<?=$game->id?>/" style="color: #000;"><?=$game->GameTitle?></a></h3>
										<p><img src="<?=$baseurl?>/images/common/consoles/png24/<?=$game->PlatformIcon?>" alt="<?=$game->PlatformName?>" style="vertical-align: -6px;" />&nbsp;<a style="color: #000;" href="<?=$baseurl;?>/platform/<?php if (!empty($game->PlatformAlias)) {echo $game->PlatformAlias;} else {echo $game->PlatformId;}?>/"><?=$game->PlatformName?></a></p>
										<div style="clear: both;"></div>
									</div>
								<?php
if ($counter == 2) {
                $counter = 0;
                ?>
									<div style="clear: both;"></div>
								<?
            } else {
                $counter++;
            }
        } elseif ($searchview == "table") {
            $boxartQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$game->id' AND banners.filename LIKE '%front%' LIMIT 1");
            $boxartResult = count($boxartQuery);

            $fanartQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$game->id' AND keytype = 'fanart' LIMIT 1");
            $fanartResult = count($fanartQuery);

            $bannerQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$game->id' AND keytype = 'series' LIMIT 1");
            $bannerResult = count($bannerQuery);

            if ($class == 'odd') {$class = 'even';} else { $class = 'odd';}
            ?>
								<tr>
									<td align="center" class="<?php echo $class; ?>"><?php echo $game->id; ?></td>
									<td class="<?php echo $class; ?>"><a href="<?php echo $baseurl; ?>/game/<?=$game->id?>/"><?php echo $game->GameTitle; ?></a></td>
									<td class="<?php echo $class; ?>"><img src="<?=$baseurl?>/images/common/consoles/png16/<?php echo $game->PlatformIcon; ?>" alt="<?php echo $game->PlatformName; ?>" style="vertical-align: middle;" /> <a style="color: #000;" href="<?=$baseurl;?>/platform/<?php if (!empty($game->PlatformAlias)) {echo $game->PlatformAlias;} else {echo $game->PlatformId;}?>/"><?=$game->PlatformName?></a></td>
									<td class="<?php echo $class; ?>">
										<?php
if (strlen($game->ReleaseDate) == 4) {
                echo $game->ReleaseDate;
            } else {
                $releaseDate = explode('/', $game->ReleaseDate);
                echo $releaseDate[2];
            }
            ?>
									</td>
									<td class="<?php echo $class; ?>">
										<?php if (!empty($game->Genre)) {
                $mainGenre = explode("|", $game->Genre);
                if (!empty($stringGenres)) {
                    for ($i = 0; $i <= count($mainGenre); $i++) {
                        if ($mainGenre[$i] == $stringGenres) {
                            if (strlen($mainGenre[$i]) > 15) {
                                $mainGenre[$i] = substr($mainGenre[$i], 0, 15) . "...";
                            }
                            echo $mainGenre[$i];
                        }
                    }
                } else {
                    if (strlen($mainGenre[1]) > 15) {
                        $mainGenre[1] = substr($mainGenre[1], 0, 15) . "...";
                    }
                    echo $mainGenre[1];
                }
            }
            ?>
									</td>
									<td class="<?php echo $class; ?>"><?php echo $game->Rating; ?></td>
									<td align="center" class="<?php echo $class; ?>"><?php if ($boxartResult != 0) {?><img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" /><?php } else {?><img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="Yes" /><?php }?></td>
									<td align="center" class="<?php echo $class; ?>"><?php if ($fanartResult != 0) {?><img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" /><?php } else {?><img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="Yes" /><?php }?></td>
									<td align="center" class="<?php echo $class; ?>"><?php if ($bannerResult != 0) {?><img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" /><?php } else {?><img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="Yes" /><?php }?></td>
								</tr>
							<?php
}

        if ($increment == "odd") {
            $increment = "even";
        } else {
            $increment = "odd";
        }
    }

    if ($searchview == "table") {
        ?>
					</table>
			<?php
}

} else {
    ?>
			<h2 style="color:#fff;">The game you searched for has not been added yet,<br />would you like to <a style="color: #ee4400" href="<?=$baseurl;?>/addgame/?passTitle=<?=$string;?>">create it?</a></h2>
            <?php
}
?>
		<div style="clear: both;"></div>

		<!-- Start Show Pagination -->
		<?=$pagination?>
		<!-- End Show Pagination -->

		</div>

	</div>