<?php
include 'simpleimage.php';

function imageResize($filename, $cleanFilename, $target, $axis)
{
    if (!file_exists($cleanFilename)) {
        $dims = getimagesize($filename);
        $width = $dims[0];
        $height = $dims[1];
        //takes the larger size of the width and height and applies the formula accordingly...this is so this script will work dynamically with any size image

        if ($axis == "width") {
            $percentage = ($target / $width);
        } else if ($axis == "height") {
            $percentage = ($target / $height);
        } else if ($width > $height) {
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
    //returns the new sizes in html image tag format...this is so you can plug this function inside an image tag and just get the src attribute
    return "src=\"$baseurl/$cleanFilename\"";
}

function imageDualResize($filename, $cleanFilename, $wtarget, $htarget)
{
    if (!file_exists($cleanFilename)) {
        $dims = getimagesize($filename);
        $width = $dims[0];
        $height = $dims[1];

        while ($width > $wtarget || $height > $htarget) {
            if ($width > $wtarget) {
                $percentage = ($wtarget / $width);
            }

            if ($height > $htarget) {
                $percentage = ($htarget / $height);
            }

            /*if($width > $height)
            {
            $percentage = ($target / $width);
            }
            else
            {
            $percentage = ($target / $height);
            }*/

            //gets the new value and applies the percentage, then rounds the value
            $width = round($width * $percentage);
            $height = round($height * $percentage);
        }

        $image = new SimpleImage();
        $image->load($filename);
        $image->resize($width, $height);
        $image->save($cleanFilename);
        $image = null;
    }
    //returns the new sizes in html image tag format...this is so you can plug this function inside an image tag and just get the src attribute
    return "src=\"$baseurl/$cleanFilename\"";
}

function imageUsername($artID)
{
    global $database;
    ## Get the site banner rating
    $query = "SELECT u.id, u.username FROM users AS u, banners AS b WHERE b.id = '$artID' AND u.id = b.userid";
    $result = $database->query($query);
    $imageUser = $result->fetch(PDO::FETCH_OBJ);

    $str = "Uploader:&nbsp;<a href='$baseurl/artistbanners/?id=$imageUser->id' style='color: orange;'>$imageUser->username</a>";

    return $str;
}
?>

<?php
// Fetch Game Information from DB
$id = intval($id);
$query = "SELECT g.*, p.name as PlatformName, p.alias AS PlatformAlias, p.icon as PlatformIcon FROM games as g, platforms as p WHERE g.id=$id AND g.Platform = p.id";
$result = $database->query($query) or die('Fetch Game Info Query Failed: ' . mysql_error());
$rows = count($result);
$game = $result->fetch(PDO::FETCH_OBJ);
?>

	<div id="gameHead">

	<?php
if ($errormessage) {
    echo "<div class=\"error\">$errormessage</div>";
}
if ($message) {
    echo "<div class=\"message\">$message</div>";
}
?>

	<?php
if (count($result) == 0) {
    ?>
		<h1>Oops!</h1>
		<h2 style="text-align: center;">We can't find the game you requested...</h2>
		<p style="text-align: center;">If you believe you have recieved this message in error, please let us know.</p>
		<p style="text-align: center;"><a href="<?=$baseurl;?>/" style="color: orange;">Click here to return to the homepage</a></p>
	</div>
<?
} else {
    ?>

		<div id="gameTitle">

			<span id ="gameUserLinks">
				<?php if ($loggedin == 1) {
        ?>
				<a class="greyButton" href="<?=$baseurl?>?tab=game-edit&id=<?=$game->id?>"><img src="<?php echo $baseurl; ?>/images/common/icons/edit_16.png" style="vertical-align: -2px;"/>&nbsp;Edit</a>
				<a class="greyButton" onclick='faceboxReport(<?=$game->id?>, "game")'><img src="<?php echo $baseurl; ?>/images/common/icons/report_16.png" style="vertical-align: -2px;"/>&nbsp;Report</a>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<?php	## First, generate their userfavorites array
        $userfavorites = explode(",", $user->favorites);

        ## If the user has this as a favorite, display a message and a button
        ## to "Unfavorite".
        $action = "favorite";
        if (in_array($id, $userfavorites, 1)) {
            $action = "unfavorite";
        }
        ## If the user doesn't have this as a favorite, display a button to
        ## mark it as a favorite.
        else {
            $action = "favorite";
        }

        echo "<a class=\"greyButton\" href=\"/?function=ToggleFavorite&id=$id\"><img src=\"$baseurl/images/common/icons/" . $action . "_16.png\" style=\"vertical-align: -1px;\" />&nbsp;" . ucfirst($action) . "</a>";
        unset($action);
        ?>
				<?php }?>
				<a id="shareButton" class="greyButton"><img src="<?php echo $baseurl; ?>/images/common/icons/social_16.png" style="vertical-align: -2px;"/>&nbsp;Share</a>
			</span>

			<h1 style="margin: 0px; padding: 0px;"><?=$game->GameTitle;?></h1>

			<?php
if (!empty($game->Alternates)) {
        echo "<h3><span style='color: #888; font-size: 13px;'><em>";
        echo "a.k.a. ' " . str_replace(",", ", ", $game->Alternates) . " ' ";
        echo "</em></span></h3>";
    }
    ?>

		</div>

		<div id="gameCoversWrapper">

			<div id="gameCovers">
				<?php
if ($frontCoverResult = $database->query(" SELECT b.id, b.filename FROM banners as b WHERE b.keyvalue = '$game->id' AND b.filename LIKE '%boxart%front%' LIMIT 1 ")) {
        $front = $frontCoverResult->fetch(PDO::FETCH_OBJ);
        if (!empty($front)) {
            ?>
							<img id="frontCover" class="frontCover imgShadow" <?=imageResize("$baseurl/banners/$front->filename", "banners/_gameviewcache/$front->filename", 300, "width")?> alt="<?php echo $game->GameTitle; ?>" title="<?php echo $game->GameTitle; ?>" />
							<?php
if ($backCoverResult = $database->query(" SELECT b.id, b.filename FROM banners as b WHERE b.keyvalue = '$game->id' AND b.filename LIKE '%boxart%back%' LIMIT 1 ")) {
                $back = $backCoverResult->fetch(PDO::FETCH_OBJ);
                if (!empty($back)) {
                    ?>
								<img  id="backCover" class="backCover imgShadow" style="display: none;" <?=imageResize("$baseurl/banners/$back->filename", "banners/_gameviewcache/$back->filename", 300, "width")?> alt="<?php echo $game->GameTitle; ?>" title="<?php echo $game->GameTitle; ?>" />
								<?php
}
            }
        } else {
            ?>
							<img class="imgShadow" src="<?php echo $baseurl; ?>/images/common/placeholders/boxart_blank.png" width="300" height="417" alt="<?php echo $game->GameTitle; ?>" title="<?php echo $game->GameTitle; ?>" />
						<?php
}
    }
    ?>
			</div>

			<p style="text-align: center; font-size: 15px;">
			<?php
// Front and back flip
    if (!empty($front) && !empty($back)) {
        echo "<a href=\"javascript: void();\" class=\"gameCoversFlip\"><img src=\"$baseurl/images/common/icons/flip_32.png\" style=\"width:24px; height: 24px; vertical-align: -7px;\" /></a>&nbsp;<a href=\"javascript: void();\" class=\"gameCoversFlip\">Flip</a>&nbsp;&nbsp;|&nbsp;&nbsp;";
    }
    // Front only
    if (!empty($front)) {
        echo "<a href=\"$baseurl/banners/$front->filename\" target=\"_blank\"><img src=\"$baseurl/images/common/icons/expand_48.png\" style=\"width:24px; height: 24px; vertical-align: -6px;\" /></a>&nbsp;<a href=\"$baseurl/banners/$front->filename\" target=\"_blank\">Front</a>";
    }
    // No front or back
    if (!empty($front) && !empty($back)) {
        echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
    }
    // Just back
    if (!empty($back)) {
        echo "<a href=\"$baseurl/banners/$back->filename\" target=\"_blank\"><img src=\"$baseurl/images/common/icons/expand_48.png\" style=\"width:24px; height: 24px; vertical-align: -6px;\" /></a>&nbsp;<a href=\"$baseurl/banners/$back->filename\" target=\"_blank\">Back</a>";
    }
    ?>
			</p>

			<?php if (!empty($front) || !empty($back)) {
        ?>
			<table call-padding="0" cell-spacing="0" style="border: 2px solid #444; border-radius: 6px; background-color: #333; color: #FFF; border-collapse: separate; border-spacing: 2px; border-color: gray; width: 100%;">
				<tr>
					<?php
if (!empty($front)) {
            echo "<th style=\"background: #F1F1F1; background-image: -webkit-linear-gradient(bottom,#C5C5C5,#F9F9F9); padding: 7px 7px 8px; font-size: 16px; border-bottom: 1px solid #444; color: #333;\">Front Boxart</th>";
        }

        if (!empty($back)) {
            echo "<th style=\"background: #F1F1F1; background-image: -webkit-linear-gradient(bottom,#C5C5C5,#F9F9F9); padding: 7px 7px 8px; font-size: 16px; border-bottom: 1px solid #444; color: #333;\">Rear Boxart</th>";
        }

        ?>
				</tr>
				<tr>
					<?php
if (!empty($front)) {
            echo "<td style=\"padding: 10px 10px; vertical-align: top; text-align: center;\">" . imageUsername($front->id) . "</td>";
        }

        if (!empty($back)) {
            echo "<td style=\"padding: 10px 10px; vertical-align: top; text-align: center;\">" . imageUsername($back->id) . "</td>";
        }

        ?>
				</tr>
				<?if ($loggedin == 1) {
            ?>
				<tr>
					<?php
if (!empty($front)) {
                echo "<td style=\"padding: 10px 10px; vertical-align: top; text-align: center;\"><a href=\"$baseurl/scripts/reportqueue_submit.php?reporttype=image&reportid=$front->id\" rel=\"facebox\" style=\"color: orange;\">Report Image</a></td>";
            }

            if (!empty($back)) {
                echo "<td style=\"padding: 10px 10px; vertical-align: top; text-align: center;\"><a href=\"$baseurl/scripts/reportqueue_submit.php?reporttype=image&reportid=$back->id\" rel=\"facebox\" style=\"color: orange;\">Report Image</a></td>";
            }

            ?>
				</tr>
				<?}?>
			</table>
			<?}?>

		</div>

		<div id="gameInfo">

			<div id="gameShare" style="float: right">

				<!-- Google plus share button -->
				<span style="float: right;">
                <!-- Place this tag where you want the +1 button to render -->
                <link rel="canonical" href="<?="$baseurl/game/$game->id/";?>" />
				<g:plusone size="medium"></g:plusone>

                <!-- Place this render call where appropriate -->
				<script type="text/javascript">
                  window.___gcfg = {
                    lang: 'en-US',
                    parsetags: 'onload'
                  };
				  (function() {
					var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
					po.src = 'https://apis.google.com/js/plusone.js?onload=onLoadCallback';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
				  })();
				</script>
				</span>

				<!-- Twitter share button -->
				<span style="float: right;">
				<a href="https://twitter.com/share" class="twitter-share-button" data-url="<?="$baseurl/game/$game->id/"?>" data-text="<?="$game->GameTitle on TheGamesDB.net"?>" data-count="horizontal" data-via="thegamesdb">Tweet</a><script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
				</span>

				<!-- Facebook share button -->
				<span style="float: right; padding-top: 1px;">
				<div class="fb-share-button" data-href="<?="$baseurl/game/$game->id/";?>"></div>
				&nbsp;
				</span>

				<!-- Share via Email button -->
				<a href="<?=$baseurl;?>/mailshare.php?urlsubject=<?=urlencode("TheGamesDB.net - $game->GameTitle");?>&url=<?=urlencode("$baseurl/game/$game->id/");?>" rel="facebox" style="float: right; margin-right: 10px; padding: 1px 6px 1px 3px; color: #fff; text-decoration: none; background-color: #333; border: 1px solid #444; border-radius: 3px; font-size: 11px; font-weight: bold;" onmouseover="$('#mailIcon').attr('src', '<?=$baseurl?>/images/common/icons/social/24/share_active.png')" onmouseout="$('#mailIcon').attr('src', '<?=$baseurl?>/images/common/icons/social/24/share_dark.png')"><img id="mailIcon" src="<?=$baseurl?>/images/common/icons/social/24/share_dark.png" alt="Share via Email" title="Share via Email" style="vertical-align: middle; width: 18px; height: 18px;" />&nbsp;Share via Email</a>

			</div>

			<h2>
				<img src="<?php echo $baseurl; ?>/images/common/consoles/png32/<?php echo $game->PlatformIcon; ?>" alt="<?php echo $game->PlatformName; ?>" title="<?php echo $game->PlatformName; ?>" style="vertical-align: -8px;" />&nbsp;<?php if (!empty($game->PlatformName)) {?>
				<a style="color: #fff;" href="<?=$baseurl?>/platform/<?php if (!empty($game->PlatformAlias)) {echo $game->PlatformAlias;} else {echo $game->Platform;}?>/"><?=$game->PlatformName?></a>
				<?php } else {echo "N/A";}?>
			</h2>

			<hr />

			<div id="gameRating">
				<?php
$query = "SELECT AVG(rating) AS average, count(*) AS count FROM ratings WHERE itemtype='game' AND itemid=$id";
    $result = $database->query($query) or die('Query failed: ' . mysql_error());
    $rating = $result->fetch(PDO::FETCH_OBJ);

    for ($i = 2; $i <= 10; $i = $i + 2) {
        if ($i <= $rating->average) {
            print "<img src=\"$baseurl/images/game/star_on.png\" width=15 height=15 border=0>";
        } else if ($rating->average > $i - 2 && $rating->average < $i) {
            print "<img src=\"$baseurl/images/game/star_half.png\" width=15 height=15 border=0>";
        } else {
            print "<img src=\"$baseurl/images/game/star_off.png\" width=15 height=15 border=0>";
        }
    }
    ?>
					&nbsp;&nbsp;<span style="font-weight: bold; color: #bbb;"><?=(int) $rating->average?> / 10</span>
					&nbsp;&nbsp;<span style="color: #888; font-size: 13px;"><em><?=$rating->count?> rating<?php if ($rating->count != 1) {
        print "s";
    }
    ?></em></span>
					<?php	if ($loggedin == 1) {
        ?>
					&nbsp;&nbsp;|&nbsp;&nbsp;Your Rating:&nbsp;
					<?php
$query = "SELECT rating FROM ratings WHERE itemtype='game' AND itemid=$id AND userid=$user->id";
        $result = $database->query($query) or die('Query failed: ' . mysql_error());
        $rating = $result->fetch(PDO::FETCH_OBJ);
        if ($rating) {
            $rating = $rating->rating;
        } else {
            $rating = 0;
        }

        for ($i = 1; $i <= 10; $i++) {
            if ($i <= $rating) {
                print "<a href=\"$baseurl/game/$id/?function=UserRating&type=game&itemid=$id&rating=$i\" OnMouseOver=\"UserRating2('userrating',$i)\" OnMouseOut=\"UserRating2('userrating',$rating)\"><img src=\"$baseurl/images/game/star_on.png\" width=15 height=15 border=0 name=\"userrating$i\"></a>";
            } else {
                print "<a href=\"$baseurl/game/$id/?function=UserRating&type=game&itemid=$id&rating=$i\" OnMouseOver=\"UserRating2('userrating',$i)\" OnMouseOut=\"UserRating2('userrating',$rating)\"><img src=\"$baseurl/images/game/star_off.png\" width=15 height=15 border=0 name=\"userrating$i\"></a>";
            }
        }
    }?>
			</div>

			<?php
$clearlogoQuery = $database->query(" SELECT * FROM banners WHERE keytype='clearlogo' AND keyvalue='$game->id' LIMIT 1 ");
    if (count($clearlogoQuery) != 0) {
        $clearlogoResult = $clearlogoQuery->fetch(PDO::FETCH_OBJ);
        ?>
			<div style="margin: auto; padding-top: 10px;">
				<h2 class="grey">ClearLOGO</h2>
				<p style="text-align: center;"><img src="<?=$baseurl?>/banners/<?=$clearlogoResult->filename?>" alt="<?=$game->GameTitle . "ClearLOGO"?>" title="<?=$game->GameTitle . "ClearLOGO"?>" /><br /><br /><?=imageUsername($clearlogoResult->id)?> | <a href="<?=$baseurl?>/scripts/reportqueue_submit.php?reporttype=image&reportid=<?=$clearlogoResult->id?>" rel="facebox" style="color: orange;">Report Image</a></p>
			</div>
			<hr />
			<?php
}
    ?>

			<p>
				<?php if (!empty($game->Overview)) {
        echo nl2br(strip_tags($game->Overview));
    } else {
        echo "\"No overview is currently available for this title.\"";
    }?>
			</p>

			<hr />

			<div id="gameVitals">

				<div id="esrbIcon" style ="float: right; width: 72px; height: 100px;">
					<?php
$esrb;
    switch ($game->Rating) {
        case "EC - Early Childhood":$esrb = "ec";
            break;
        case "E - Everyone":$esrb = "everyone";
            break;
        case "E10+ - Everyone 10+":$esrb = "e10";
            break;
        case "T - Teen":$esrb = "teen";
            break;
        case "M - Mature":$esrb = "mature";
            break;
        case "RP - Rating Pending":$esrb = "rp";
            break;
    }
    if (isset($esrb)) {
        echo "<img src=\"$baseurl/images/game-view/esrb/esrb-$esrb.png\"/>";
    }
    unset($esrb);
    ?>
				</div>

				<p>
					<span class="grey">Players:</span>&nbsp;&nbsp;<?php if (!empty($game->Players)) {echo $game->Players;} else {echo "N/A";}?>
					<span class="grey" style="padding-left: 20px;">Co-op:</span>&nbsp;&nbsp;<?php if ($game->coop != false) {echo $game->coop;} else {echo "N/A";}?><br />
					<span class="grey">Genres:</span>&nbsp;&nbsp;<?php if (!empty($game->Genre)) {
        $genres = explode("|", $game->Genre);
        $genreCount = 1;
        while ($genreCount < count($genres) - 1) {
            echo $genres[$genreCount];
            if ($genreCount < count($genres) - 2) {
                echo ", ";
            }
            $genreCount++;
        }
    } else {echo "N/A";}?>
					<br />
					<span class="grey">Release Date:</span>&nbsp;&nbsp;<?php if (!empty($game->ReleaseDate)) {echo $game->ReleaseDate;} else {echo "N/A";}?><br /><br />

					<?php
// Start Developer Logo Replacement
    if (!empty($game->Developer)) {
        $developerLogoExists = false;
        $devArray = explode(" ", $game->Developer);
        $i = 0;

        for ($i = 0; $i < count($devArray); $i++) {
            $developerQuery = $database->query(" SELECT logo FROM pubdev WHERE keywords LIKE '%$devArray[$i]%' ");
            if ($developerQuery) {
                if (count($developerQuery) != 0) {
                    $developerResult = $developerQuery->fetch(PDO::FETCH_OBJ);
                    $developerLogoExists = true;
                    $i = count($devArray);
                }
            }
        }
        if ($developerLogoExists == true) {
            if (!file_exists("banners/_gameviewcache/publishers/$developerResult->logo")) {
                WideImage::load("banners/publisher-logos/$developerResult->logo")->resize(400, 60)->saveToFile("banners/_gameviewcache/publishers/$developerResult->logo");
            }
            ?>
							<span class="grey">Developer:</span> <?php if (!empty($game->Developer)) {echo $game->Developer;} else {echo "N/A";}?>
							<br/>
							<img src="<?=$baseurl;?>/banners/_gameviewcache/publishers/<?=$developerResult->logo;?>" alt="<?=$game->Developer;?>" title="<?=$game->Developer;?>" style="vertical-align: middle; padding-bottom: 14px; padding-top: 4px;" />
							<br/>
						<?php
} else {
            ?>
							<span class="grey">Developer:</span>&nbsp;&nbsp;<?php if (!empty($game->Developer)) {echo $game->Developer;} else {echo "N/A";}?><br />
						<?php
}
    } else {
        ?>
						<span class="grey">Developer:</span>&nbsp;&nbsp;<?php if (!empty($game->Developer)) {echo $game->Developer;} else {echo "N/A";}?><br />
					<?php
}
    ?>

					<?php
// Start Publisher Logo Replacement
    if (!empty($game->Publisher)) {
        $publisherLogoExists = false;
        $pubArray = explode(" ", $game->Publisher);
        $i = 0;

        for ($i = 0; $i < count($pubArray); $i++) {
            $publisherQuery = $database->query(" SELECT logo FROM pubdev WHERE keywords LIKE '%$pubArray[$i]%' ");
            if ($publisherQuery) {
                if (count($publisherQuery) != 0) {
                    $publisherResult = $publisherQuery->fetch(PDO::FETCH_OBJ);
                    $publisherLogoExists = true;
                    $i = count($pubArray);
                }
            }
        }
        if ($publisherLogoExists == true) {
            if (!file_exists("banners/_gameviewcache/publishers/$publisherResult->logo")) {
                WideImage::load("banners/publisher-logos/$publisherResult->logo")->resize(400, 60)->saveToFile("banners/_gameviewcache/publishers/$publisherResult->logo");
            }
            ?>
							<span class="grey">Publisher:</span> <?php if (!empty($game->Publisher)) {echo $game->Publisher;} else {echo "N/A";}?>
							<br/>
							<img src="<?=$baseurl;?>/banners/_gameviewcache/publishers/<?=$publisherResult->logo;?>" alt="<?=$game->Publisher;?>" title="<?=$game->Publisher;?>" style="vertical-align: middle; padding-bottom: 14px; padding-top: 4px;" />
							<br/>
						<?php
} else {
            ?>
							<span class="grey">Publisher:</span>&nbsp;&nbsp;<?php if (!empty($game->Publisher)) {echo $game->Publisher;} else {echo "N/A";}?>
						<?php
}
    } else {
        ?>
						<span class="grey">Publisher:</span>&nbsp;&nbsp;<?php if (!empty($game->Publisher)) {echo $game->Publisher;} else {echo "N/A";}?>
					<?php
}
    ?>

				</p>

				<div style="clear: both;"></div>

				<?php if ($game->Platform == 1 || $game->Platform == 37) {?>
				<hr />
				<div id="sysReq">
					<p><span class="grey">System Requirements</span></p>
					<p><span class="grey">OS:</span> <?php if ($game->os == "") {echo "N/A";} else {echo $game->os;}?><br />
					<span class="grey">Processor:</span> <?php if ($game->processor == "") {echo "N/A";} else {echo $game->processor;}?><br />
					<span class="grey">RAM:</span> <?php if ($game->ram == "") {echo "N/A";} else {echo $game->ram;}?><br />
					<span class="grey">Hard Drive:</span> <?php if ($game->hdd == "") {echo "N/A";} else {echo $game->hdd;}?><br />
					<span class="grey">Video:</span> <?php if ($game->video == "") {echo "N/A";} else {echo $game->video;}?><br />
					<span class="grey">Sound:</span> <?php if ($game->sound == "") {echo "N/A";} else {echo $game->sound;}?></p>
				</div>
				<?}?>

			</div>

		</div>

		<div style="clear:both"></div>

	</div>

	<div id="gameContent">

		<div id="gameContentTop">
			<a name="midPanel"></a>
			<div id="panelNav">
				<ul>
					<li><a id="nav_fanartScreens" class="active" href="#midPanel" onclick="contentShow('fanartScreens');">Fanart &amp; Screenshots</a></li>
					<li><a id="nav_banners" href="#midPanel" onclick="contentShow('banners');">Banners</a></li>
					<li><a id="nav_platforms" href="#midPanel" onclick="contentShow('platforms');">Other Platforms</a></li>
					<li><a id="nav_trailer" href="#midPanel" onclick="contentShow('trailer');">Game Trailer</a></li>
				</ul>
				<div style="clear: both;"></div>
			</div>

			<div style="clear: both;"></div>

			<hr />

			<div id="fanartScreens">

				<div id="fanart">

					<div class="slider-wrapper theme-default">
						<div id="fanartRibbon" style="position: absolute; width: 125px; height: 125px; background: url(<?=$baseurl?>/images/game-view/ribbon-fanart.png) no-repeat; z-index: 10"></div>
						<?php
if ($fanartResult = $database->query(" SELECT b.id, b.filename FROM banners as b WHERE b.keyvalue = '$game->id' AND b.keytype = 'fanart' ")) {
        $fanSlideCount = 0;
        if (count($fanartResult) > 0) {
            ?>
								<div id="fanartSlider" class="nivoSlider">
							<?php
while ($fanart = $fanartResult->fetch(PDO::FETCH_OBJ)) {
                // $dims = getimagesize("$baseurl/banners/$fanart->filename"); echo "$dims[0] x $dims[1]";
                ?>
									<img class="fanartSlide imgShadow" <?=imageResize("$baseurl/banners/$fanart->filename", "banners/_gameviewcache/$fanart->filename", 470, "width")?> alt="<?=$game->GameTitle?> Fanart" title="<?=imageUsername($fanart->id)?> | <a href='javascript:void();' onclick='faceboxReport(<?=$fanart->id?>);' style='color: orange;'>Report Image</a> <br/> <a href='<?=$baseurl;?>/banners/<?=$fanart->filename?>' target='_blank'>View Full-Size</a> | <a href='<?=$baseurl?>/game-fanart-slideshow.php?id=<?=$game->id?>' target='_blank'>Full-screen Slideshow</a>"/>
							<?php
$fanSlideCount++;
            }
            ?>
								</div>
							<?php
} else {
            ?>
								<img class="imgShadow" src="<?=$baseurl?>/images/common/placeholders/fanart_blank.png" width="470" height="264" alt="<?=$game->GameTitle?>" title="<?=$game->GameTitle?>" />
							<?php
}
    }
    ?>
					</div>

				</div>

				<div id="screens">

					<div class="slider-wrapper theme-default">
						<div id="screensRibbon" style="position: absolute; width: 125px; height: 125px; background: url(<?=$baseurl?>/images/game-view/ribbon-screens.png) no-repeat; z-index: 10"></div>
						<?php
if ($screenResult = $database->query(" SELECT b.id, b.filename FROM banners as b WHERE b.keyvalue = '$game->id' AND b.keytype = 'screenshot' ")) {
        if (count($screenResult) > 0) {
            ?>
							<div id="screenSlider" class="nivoSlider">
							<?php
$screenSlideCount = 0;
            while ($screen = $screenResult->fetch(PDO::FETCH_OBJ)) {
                ?>
									<img class="screenSlide" <?=imageDualResize("$baseurl/banners/$screen->filename", "banners/_gameviewcache/$screen->filename", 470, 264)?> alt="<?=$game->GameTitle?> Screenshot" title="<?=imageUsername($screen->id)?> | <a href='javascript:void();' onclick='faceboxReport(<?=$screen->id?>);' style='color: orange;'>Report Image</a><br /><a href='<?="$baseurl/banners/$screen->filename"?>' target='_blank'>View Full-Size</a>"/>
							<?php
$screenSlideCount++;
            }
            ?>
							</div>
							<?php
} else {
            ?>
									<img class="imgShadow" src="<?=$baseurl?>/images/common/placeholders/fanart_blank.png" width="470" height="264" alt="<?=$game->GameTitle?>" title="<?=$game->GameTitle?>" />
							<?php
}
    }
    ?>
					</div>
				</div>

				<div style="clear: both;"></div>

			</div>

			<div id="banners">
				<div class="slider-wrapper theme-default">
					<div id="bannerRibbon" style="display: none; position: absolute; width: 125px; height: 125px; background: url(<?=$baseurl?>/images/game-view/ribbon-banners.png) no-repeat; z-index: 10"></div>
					<?php
if ($bannerResult = $database->query(" SELECT b.id, b.filename FROM banners as b WHERE b.keyvalue = '$game->id' AND b.keytype = 'series' ") or die("banner query failed" . mysql_error())) {
        if (count($bannerResult) > 0) {
            ?>
							<div id="bannerSlider" class="nivoSlider" style="width:760px important; height: 140px !important;">
						<?php
$bannerSlideCount = 0;
            while ($banner = mysql_fetch_array($bannerResult)) {
                ?>
								<img class="bannerSlide" src="<?="$baseurl/banners/$banner[filename]"?>" width="760" height="140" alt="<?=$game->GameTitle?> Banner" title="<?=imageUsername($banner[id])?> | <a href='javascript:void();' onclick='faceboxReport(<?=$banner[id]?>)' style='color: orange;'>Report Image</a>"/>
						<?php
$bannerSlideCount++;
            }
            ?>
							</div>
						<?php
} else {
            ?>
							<img class="imgShadow" src="<?=$baseurl;?>/images/common/placeholders/banner_blank.png" width="760" height="140" alt="<?=$game->GameTitle;?>" title="<?=$game->GameTitle;?>" />
						<?php
}
    }
    ?>
				</div>

				<div style="clear: both;"></div>

			</div>

			<div id="platforms">
				<div style="margin: auto; width: 500px; box-shadow: 0px 0px 22px #000; border-radius: 16px; background-color: #1e1e1e; text-align: center; margin-top: 20px;">
					<div style="padding: 20px;">
						<h3 style="color: #fff;">Other Platforms with this Game</h3>
						<?php
$similarResult = $database->query(" SELECT g.id, g.platform, g.GameTitle, p.name, p.icon FROM games as g, platforms as p WHERE g.GameTitle = \"$game->GameTitle\" AND g.Platform <> '$game->Platform' AND g.Platform = p.id ORDER BY p.name");
    $similarRowCount = count($similarResult);
    if ($similarRowCount > 0) {
        ?>
								<p>This game exists on <?=$similarRowCount?> other platforms.</p>
						<?php
while ($similarRow = $similarResult->fetch(PDO::FETCH_ASSOC)) {
            ?>
										<div style="margin-top: 10px; font-size: 16px;">
											<img src="<?=$baseurl?>/images/common/consoles/png32/<?=$similarRow['icon']?>" alt="<?=$similarRow['name']?>" style="vertical-align: -8px;" />&nbsp;&nbsp;
											<a href="<?=$baseurl?>?tab=game&id=<?=$similarRow['id']?>"><?=$similarRow['name']?> - <?=$similarRow['GameTitle']?></a>
										</div>
						<?php
}
        ?>
								<p>If you know this game exists on another platform, why not <a href="<?=$baseurl?>?tab=addgame&passTitle=<?=urlencode($game->GameTitle)?>">add it</a>.</p>
						<?php
} else {
        ?>
								<p>There are currently no other platforms that have this game yet...</p>
								<p>If you know of one, why not <a href="<?=$baseurl?>?tab=addgame&passTitle=<?=urlencode($game->GameTitle)?>">add it</a>.</p>
						<?php
}
    ?>
					</div>
				</div>
				<div style="clear: both;"></div>
			</div>

			<div id="trailer">
				<?php if ($game->Youtube != "") {?>
				<div style="margin: auto; width: 853px; box-shadow: 0px 0px 22px #000;">
					<iframe width="853" height="510" src="http://www.youtube.com/embed/<?=str_replace("&hd=1", "", str_replace("?hd=1", "", "$game->Youtube")) . "?hd=1"?>" frameborder="0" allowfullscreen></iframe>
					<div style="clear: both;"></div>
				</div>
				<?php } else {?>
				<div style="margin: auto; width: 500px; box-shadow: 0px 0px 22px #000; border-radius: 16px; background-color: #1e1e1e;">
					<p style="color: #fff; font-size: 18px; text-shadow: 0px 0px 5px #000; text-align: center; padding: 125px 10px;">This game does not currently have a trailer added.</p>
				</div>
				<?php }?>
			</div>

	</div>

	<div style="clear: both;"></div>

	<div id="gameContentBottom">

		<div style="text-align: center;"><a style="font-size: 18px; color: #fff; text-decoration: none; text-shadow: 0px 0px 10px #000;" href="#gameContentBottom"  onclick="$('#comments').slideToggle();">Comments&nbsp;&nbsp;<img style="vertical-align: middle;" src="<?=$baseurl;?>/images/common/icons/collapse-alt_16.png" alt="Show Comments" title="Show Comments" /></a></div>

		<hr style="margin: 10px 0px 14px 0px;" />

		<div id="comments">
				<?php
// SHOW ALL CURRENT COMMENTS
    $commentsQuery = $database->query(" SELECT c.*, u.username, u.emailaddress FROM comments AS c , users AS u WHERE c.gameid='$game->id' AND c.userid = u.id ORDER BY c.timestamp ASC");
    if (count($commentsQuery)) {
        while ($comments = $commentsQuery->fetch(PDO::FETCH_OBJ)) {
            ?>
							<div class="comment">
							<?php
$filename = glob("banners/users/" . $comments->userid . "-*.jpg");
            if (file_exists($filename[0])) {
                ?>
								<div style="float: left; width: 64px; height: 64px; padding: 0px 15px 15px 0px; text-align: center;"><img src="<?=$baseurl;?>/<?=$filename[0];?>" alt="<?=$comments->username;?>" title="<?=$comments->username;?>" /></div>
							<?php
$filename = null;
            } else {
                $gravatarID = md5(strtolower(trim($comments->emailaddress)));
                $defaultBanner = urlencode($baseurl . "/images/common/icons/user-black_64.png");
                ?>
								<img style="float: left; padding: 0px 15px 5px 0px;" src="http://www.gravatar.com/avatar/<?=$gravatarID?>?s=64&r=pg&d=<?=$defaultBanner?>" alt="<?=$comments->username;?>" title="<?=$comments->username;?>" />
							<?php
}
            ?>
								<span style="float: right;"><?=date("l, jS F Y - g:i A (T)", strtotime($comments->timestamp))?></span>
								<h2><?=$comments->username;?> says...</h2>
								<p><?=$comments->comment;?></p>
								<?php
if ($comments->userid == $user->id || $adminuserlevel == 'ADMINISTRATOR') {
                ?>
										<p style="text-align: right;"><a href="<?=$baseurl;?>/game/<?=$game->id;?>/?function=Delete+Game+Comment&commentid=<?=$comments->id;?>">Delete Comment</a></p>
								<?php
}
            ?>
								<div style="clear: both;"></div>
							</div>
				<?php
}
    }
    if ($loggedin == 1) {
        // LEAVE COMMENT LOGGED IN
        ?>
						<div class="comment">
							<?php
$filename = glob("banners/users/" . $user->id . "-*.jpg");
        if (file_exists($filename[0])) {
            ?>
								<div style="float: left; width: 64px; height: 64px; padding: 0px 15px 15px 0px; text-align: center;"><img src="<?=$baseurl;?>/<?=$filename[0];?>" alt="<?=$user->username;?>" title="<?=$user->username;?>" /></div>
							<?php
$filename = null;
        } else {
            $gravatarID = md5(strtolower(trim($user->emailaddress)));
            $defaultBanner = urlencode($baseurl . "/images/common/icons/user-black_64.png");
            ?>
								<img style="float: left; padding: 0px 15px 5px 0px;" src="http://www.gravatar.com/avatar/<?=$gravatarID?>?s=64&r=pg&d=<?=$defaultBanner?>" alt="<?=$user->username;?>" title="<?=$user->username;?>" />
							<?php
}
        ?>
							<?php
if (!count($commentsQuery)) {
            ?>
								<h2>No one has left a comment yet...</h2>
								<p>Be the first to leave a comment!</p>
								<?php
} else {
            ?>
								<h2>Leave a comment...</h2>
							<?php
}
        ?>
							<p>Comments are plain-text only: bb-code, html and so forth are not allowed.</p>
							<form method="post" action="<?=$baseurl;?>/game/<?=$game->id;?>/">
								<textarea name="comment" style="width: 100%; height: 60px;"></textarea>
								<input type="hidden" name="userid" value="<?=$user->id;?>" />
								<input type="hidden" name="gameid" value="<?=$game->id;?>" />
								<input type="hidden" name="function" value="Add Game Comment" />
								<p style="text-align: right;"><input class="greyButton" type="submit" name="button" value="Leave Comment..." /></p>
							</form>
							<div style="clear: both;"></div>
						</div>
				<?php
} else {
        // LEAVE COMMENT NOT LOGGED IN
        ?>
						<div class="comment">
							<img style="float: left; padding: 0px 15px 5px 0px;" src="<?=$baseurl;?>/images/common/icons/user-black_64.png" />
							<?php
if (!count($commentsQuery)) {
            ?>
								<h2>No one has left a comment yet...</h2>
								<p>Be the first to leave a comment!</p>
								<?php
} else {
            ?>
								<h2>Leave a comment...</h2>
							<?php
}
        ?>
							<p>Comments are plain-text only: bb-code, html and so forth are not allowed.</p>
							<div style="clear: both;"></div>
							<p style="font-size: 14px; text-align: center"><em>You must be logged in to leave a comment,<br />click <a href="<?=$baseurl?>/login/?redirect=<?=urlencode($_SERVER["REQUEST_URI"])?>">here</a> to log in...</em></p>
							<div style="clear: both;"></div>
						</div>
				<?php
}
    ?>
			<div style="width: 96%; margin: auto; background: #333; box-shadow: 0px 0px 22px #000; border-radius: 16px; text-align: center;">
			</div>
			<div style="clear: both;"></div>
		</div>

	</div>

	<!--
	<div id="gameFooter">

	</div>
	-->

</div>

<!-- Start #panelNav Scripts -->
<script type="text/javascript">
	function contentShow(id)
	{
		switch (id)
		{
			case "fanartScreens":
				contentHide();
				$("#nav_fanartScreens").addClass("active");
				$("#fanartScreens").slideDown("400");
				$("#fanartRibbon").slideDown("400");
				$("#screensRibbon").slideDown("400");
			break;

			case "banners":
				contentHide();
				$("#nav_banners").addClass("active");
				$("#banners").slideDown("400");
				$("#bannerRibbon").slideDown("400");
			break;

			case "platforms":
				contentHide();
				$("#nav_platforms").addClass("active");
				$("#platforms").slideDown("400");
			break;

			case "trailer":
				contentHide();
				$("#nav_trailer").addClass("active");
				$("#trailer").slideDown("400");
			break;
		}
	}

	function contentHide(id)
	{
		// Remove active class from nav item
		$("#panelNav ul li a").each( function(index) { $(this).removeClass("active"); } );

		// Hide all panels
		$("#fanartScreens").slideUp("400");
		$("#fanartRibbon").slideUp("400");
		$("#screensRibbon").slideUp("400");
		$("#banners").slideUp("400");
		$("#bannerRibbon").slideUp("400");
		$("#platforms").slideUp("400");
		$("#trailer").slideUp("400");
	}
</script>
<!-- End #panelNav Scripts -->

<!-- Start Share Script -->
<script type="text/javascript">

	$('#gameShare').css({display: "none"});

	$(document).ready(function(){
		$('#shareButton').bind("click",function(){
			var elem = $('#gameShare');
			elem.slideToggle();
		});

	});
</script>
<!-- End Share Script -->


<!-- Start Boxart Flip Script -->
<script type="text/javascript">
	$('#frontCover').css({display: "none"});

	$(document).ready(function(){
		$('#frontCover').fadeIn(2000);
	});
</script>

<?php
if (!empty($back)) {
        ?>
<script type="text/javascript">
	$('#gameCovers').css({cursor : "pointer"});

	$(document).ready(function(){
		$('.gameCoversFlip').bind("click",function(){
			var elem = $('#gameCovers');

			if(elem.data('flipped'))
			{
				elem.revertFlip();
				elem.data('flipped',false)
			}
			else
			{
				var frontWidth = $("#frontCover").attr("width");
				var frontHeight = $("#frontCover").attr("height");
				elem.flip({
				direction:'rl',
				speed: 350,
				color: "#ff9000",
				content: "<img class=\"imgShadow\" src=\"" + $("#backCover").attr("src") + "\" width=\"" + frontWidth + "\" height=\"" + frontHeight + "\" />"
				});
				elem.data('flipped',true);
			}
		});
		$('#gameCovers').bind("click",function(){
			var elem = $(this);

			if(elem.data('flipped'))
			{
				elem.revertFlip();
				elem.data('flipped',false)
			}
			else
			{
				var frontWidth = $("#frontCover").attr("width");
				var frontHeight = $("#frontCover").attr("height");
				elem.flip({
				direction:'rl',
				speed: 350,
				color: "#ff9000",
				content: "<img class=\"imgShadow\" src=\"" + $("#backCover").attr("src") + "\" width=\"" + frontWidth + "\" height=\"" + frontHeight + "\" />"
				});
				elem.data('flipped',true);
			}
		});
	});
</script>
<!-- End Boxart Flip Script -->
<?php
}
    ?>

<!-- Start Fanart nivoSlider -->
<script type="text/javascript">
    $(window).load(function() {
        $('#fanartSlider').nivoSlider({animSpeed: 220, effect: 'fade'});
        $('#screenSlider').nivoSlider({animSpeed: 220, effect: 'fade'});
        $('#bannerSlider').nivoSlider({animSpeed: 220, effect: 'fade'});
    });
</script>
<!-- End Fanart nivoSlider -->

<!-- Start jQuery Smooth Vertical Page Scrolling -->
<script type="text/javascript">
    $(document).ready(function() {  function filterPath(string) {  return string    .replace(/^\//,'')    .replace(/(index|default).[a-zA-Z]{3,4}$/,'')    .replace(/\/$/,'');  }  var locationPath = filterPath(location.pathname);  var scrollElem = scrollableElement('html', 'body');  $('a[href*=#]').each(function() {    var thisPath = filterPath(this.pathname) || locationPath;    if (  locationPath == thisPath    && (location.hostname == this.hostname || !this.hostname)    && this.hash.replace(/#/,'') ) {      var $target = $(this.hash), target = this.hash;      if (target) {        var targetOffset = $target.offset().top;        $(this).click(function(event) {          event.preventDefault();          $(scrollElem).animate({scrollTop: targetOffset}, 400, function() {            location.hash = target;          });        });      }    }  });
	// use the first element that is "scrollable"
	function scrollableElement(els) {    for (var i = 0, argLength = arguments.length; i <argLength; i++) {      var el = arguments[i],          $scrollElement = $(el);      if ($scrollElement.scrollTop()> 0) {        return el;      } else {        $scrollElement.scrollTop(1);        var isScrollable = $scrollElement.scrollTop()> 0;        $scrollElement.scrollTop(0);        if (isScrollable) {          return el;        }      }    }    return [];  }});
</script>
<!-- End jQuery Smooth Vertical Page Scrolling -->

<script type="text/javascript">
	function faceboxReport(reportid, reporttype)
	{
		if(!reporttype) { reporttype = "image"; }
		jQuery.facebox({ ajax: '<?=$baseurl?>/scripts/reportqueue_submit.php?reporttype=' + reporttype + '&reportid=' + reportid });
	}
</script>

<?php
}
?>
