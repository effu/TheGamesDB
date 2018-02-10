<?php

#####################################################
## MAIN MENU FUNCTIONS
#####################################################

// Function to auto-redirect to game page if only one result is found
if (isset($function) && $function == "Search") {
    $string = filter_var($string, FILTER_SANITIZE_STRING);

    $searchQuery = $database->query("SELECT g.id FROM games as g WHERE g.GameTitle = '$string'");
    if (count($searchQuery) == 1) {
        $searchResult = $searchQuery->fetch(PDO::FETCH_OBJ);
        $tab = "game";
        $id = $searchResult->id;
    } else {
        $searchQuery = "SELECT g.id FROM games as g WHERE MATCH(g.GameTitle) AGAINST ('$string')";
        $arr = array();
        preg_match('/[0-9]+/', $string, $arr);
        foreach ($arr as $numeric) {
            $searchQuery .= " AND g.GameTitle LIKE '%$numeric%'";
        }

        $searchQuery = $database->query($searchQuery);

        if (count($searchQuery) == 1) {
            $searchResult = $searchQuery->fetch(PDO::FETCH_OBJ);
            $tab = "game";
            $id = $searchResult->id;
        }
    }
}

// Function to update last search/favorites view type in users db table
if (isset($updateview) && $updateview == "yes") {
    if ($loggedin == 1) {
        if (!empty($searchview)) {
            $mode = $searchview;
        } elseif (!empty($favoritesview)) {
            $mode = $favoritesview;
        }
        $database->query(" UPDATE users SET favorites_displaymode = '$mode' WHERE id = '$user->id' ");
        $user->favorites_displaymode = $mode;
    }
}

// Function to share page via email
if (isset($function) && $function == "Share via Email") {
    // Check that captcha is completed and matches
    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['captcha']) && $_POST['captcha'] == $_SESSION['captcha']) {
        ##Make userinput safe
        $fromname = mysql_real_escape_string($fromname);
        $fromaddress = mysql_real_escape_string($fromaddress);
        $toaddress = mysql_real_escape_string($toaddress);
        $url = mysql_real_escape_string($url);

        if ($messagecontent != false) {
            $quote = "<h3>Message From Your Friend:</h3><p><i>$messagecontent</i></p>";
        }

        ## Email it to the user

        $from = "$fromname <$fromaddress>";

        $host = $mail_server;

        $to = "'$toaddress <$toaddress>";

        $subject = "[TheGamesDB.net] $fromname has shared a link with you";

        $emailmessage = "
			<html>
				<head>
					<title>TheGamesDB.net</title>
				</head>
				<body>
					<table width=\"802\" align=\"center\" border=\"0\">
						<img src=\"http://thegamesdb.net/email/email-header.jpg\" />
						<div style=\"background-color: #333333; color: #ffffff; padding: 15px; border: 1px solid #000;\">
							<h2>TheGamesDB.net Shared Link.</h2>
							<p>$fromname visited <a href=\"http://thegamesdb.net\" style=\"color: orange;\">TheGamesDB.net</a> and wanted to share a link with you.</p>
							$quote
							<h3>Your link details:</h3>
							<p><a href=\"$url\" style=\"color: orange;\">$url</a></p>
							<hr />
							<p>We hope you enjoy your visit with us.</p>
							<p><i>TheGamesDB.net Admins</i></p>
						</div>
					</table>
				</body>
			</html>
			";

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        $headers .= 'From:' . $from . "\r\n";

        mail($to, $subject, wordwrap($emailmessage, 70), $headers);

        // Display success message and finish up session
        $message = "Message Sent to $toaddress!";

        unset($_SESSION['captcha']); /* this line makes session free, we recommend you to keep it */
    } elseif ($_SERVER['REQUEST_METHOD'] == "POST" && !isset($_POST['captcha'])) {
        $errormessage = "Message was not sent, captcha didn't pass...<br />Please try again and remember to complete the captcha!";
    }
}

if (isset($function) && $function == 'Send PM') {
    $toQuery = $database->query(" SELECT id FROM users WHERE username = '$pmto' LIMIT 1");
    $to = $toQuery->fetch(PDO::FETCH_OBJ);

    $pmmessage = htmlspecialchars($pmmessage, ENT_QUOTES);

    if ($database->query(" INSERT INTO messages (`from`, `to`, `subject`, `message`, `status`, `timestamp`) VALUES ('$user->id', '$to->id', '$pmsubject', '$pmmessage', 'new', FROM_UNIXTIME($time)); ") or die(mysql_error())) {
        $message = "PM Sent to \"$pmto\" Successfully";
    } else {
        $errormessage = "Oops! There was a problem sending your message,<br />Please try again...";
    }
}

if (isset($function) && $function == 'Delete PM') {
    if ($database->query(" DELETE FROM messages WHERE messages.id = $pmid AND messages.to = '$user->id' ")) {
        $message = "Your message was deleted.";
    } else {
        $errormessage = "There was a problem deleting your message,<br />Please try again...";
    }
}

if (isset($function) && $function == "Generate Platform Alias's") {
    if ($aliasResult = $database->query(" SELECT p.id, p.name, p.alias FROM platforms AS p WHERE p.alias IS NULL OR p.alias = '' ")) {
        $successflag = true;
        while ($alias = $aliasResult->fetch(PDO::FETCH_OBJ)) {
            $platformName = trim($alias->name);
            $platformName = strtolower($platformName);
            $platformName = str_ireplace(" ", "-", $platformName);
            $platformAlias = preg_replace("/[^a-z0-9\-]/", "", $platformName);
            if (!$database->query(" UPDATE platforms SET alias = '$platformAlias' WHERE id = '$alias->id' ")) {
                $successflag = false;
            }
        }

        if ($successflag == true) {
            $message = "Missing Platform Alias's Generated Successfully";
        } else {
            $errormessage = "There was a problem generating the Platform Alias's,<br />please carefully check the list and try again.";
        }
    } else {
        $errormessage = "There was a problem generating the Platform Alias's,<br />please carefully check the list and try again.";
    }
}
