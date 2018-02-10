<?php
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

	<div id="gameHead">

	<?php if ($errormessage): ?>
	<div class="error"><?=$errormessage?></div>
	<?php endif;?>
	<?php if ($message): ?>
	<div class="message"><?=$message?></div>
	<?php endif;?>


	<h1>All Platforms</h1>

	<?php
$recentResult = $database->query(" SELECT p.* FROM platforms AS p ORDER BY p. name ASC ");
$count = 1;
## Platform Items Display
while ($recent = $recentResult->fetch(PDO::FETCH_OBJ)) {
    ?>
		<div style=" width: 90%; padding: 16px; margin: 10px auto 20px auto; border-radius: 4px; border: 1px solid #4f4f4f; background-color: #333;">

			<?php
if ($boxartResult = $database->query(" SELECT b.filename FROM banners as b WHERE b.keyvalue = '$recent->id' AND b.keytype = 'platform-boxart' LIMIT 1 ")) {
        $boxart = $boxartResult->fetch(PDO::FETCH_OBJ);
    }
    ?>

			<div style="height: 200px; float: left; padding-right: 12px; width: 202px; text-align: center;">
			<?php
if ($boxart->filename != "") {
        ?>
				<img <?=imageResize("$baseurl/banners/$boxart->filename", "banners/_allplatformscache/$boxart->filename", 200)?> alt="<?=$game->GameTitle?> Boxart" style="border: 1px solid #666;"/>
			<?php
} else {
        ?>
				<img src="<?=$baseurl?>/images/common/placeholders/boxart_blank.png" alt="<?=$game->GameTitle?> Boxart"  style="width:140px; height: 200px; border: 1px solid #666;"/>
			<?php
}
    ?>
			</div>

			<span style="float: right; background-color: #333; padding: 6px; border-radius: 6px;">
			<?php
$ratingquery = "SELECT AVG(rating) AS average, count(*) AS count FROM ratings WHERE itemtype='platform' AND itemid=$recent->id";
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
$gameCountQuery = $database->query(" SELECT count(*) AS gamecount FROM games WHERE games.platform = $recent->id ");
    $gameCountResult = $gameCountQuery->fetch(PDO::FETCH_OBJ);
    ?>
			<h2><?=$count?>:&nbsp;<img src="<?=$baseurl?>/images/common/consoles/png24/<?=$recent->icon?>" alt="<?=$recent->name?>" style="vertical-align: -6px;" />&nbsp;<a style="color: orange; text-decoration: underline;" href="<?=$baseurl;?>/platform/<?php if (!empty($recent->alias)) {echo $recent->alias;} else {echo $recent->id;}?>/"><?=$recent->name?></a>&nbsp;(<?=$gameCountResult->gamecount?> games)</h2>

			<p style="text-align: justify;"><?php if ($recent->overview != "") {echo substr($recent->overview, 0, 410) . "...";} else {echo "<br />No Overview Available...<br /><br />";}?></p>

			<hr />

			<div>
				<p style="text-align: center;"><a href="<?=$baseurl;?>/platform/<?php if (!empty($recent->alias)) {echo $recent->alias;} else {echo $recent->id;}?>/" style="color: orange;">View platform page</a>&nbsp;|&nbsp;<a href="<?=$baseurl?>/browse/<?=$recent->id?>/" style="color: orange;">View all games for <?=$recent->name?></a></p>

				<hr />

				<p style="text-align: center;">
				<?php
$boxartQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$recent->id' AND banners.keytype = 'platform-boxart' LIMIT 1");
    $boxartResult = count($boxartQuery);

    $fanartQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$recent->id' AND keytype = 'platform-fanart' LIMIT 1");
    $fanartResult = count($fanartQuery);

    $bannerQuery = $database->query("SELECT keyvalue FROM banners WHERE banners.keyvalue = '$recent->id' AND keytype = 'platform-banner' LIMIT 1");
    $bannerResult = count($bannerQuery);
    ?>

				<?php
if ($boxartResult != 0) {?>Boxart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" style="vertical-align: -3px;" /> | <?php } else {?>Boxart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" style="vertical-align: -3px;" /> | <?php }
    if ($fanartResult != 0) {?>Fanart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" style="vertical-align: -3px;" /> | <?php } else {?>Fanart:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" style="vertical-align: -3px;" /> | <?php }
    if ($bannerResult != 0) {?>Banner:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" style="vertical-align: -3px;" /> | <?php } else {?>Banner:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" style="vertical-align: -3px;" /> | <?php }
    if ($recent->console != "") {?>Console Art:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" style="vertical-align: -3px;" /> | <?php } else {?>Console Art:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" style="vertical-align: -3px;" /> | <?php }
    if ($recent->controller != "") {?>Controller Art:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" style="vertical-align: -3px;" /> | <?php } else {?>Controller Art:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" style="vertical-align: -3px;" /> | <?php }
    if ($recent->youtube != "") {?>Trailer:&nbsp;<img src="<?=$baseurl?>/images/common/icons/tick_16.png" alt="Yes" style="vertical-align: -3px;" /><?php } else {?>Trailer:&nbsp;<img src="<?=$baseurl?>/images/common/icons/cross_16.png" alt="No" style="vertical-align: -3px;" /><?php }
    ?>
				</p>
			</div>
			<div style="clear: both;"></div>
		</div>
	<?php
$count++;
}
?>

		<div style="clear: both;"></div>

	</div>