<?php

require_once("award.inc.php");

$user = db_get_user_by_cookie( $_COOKIE['bggcookie'] );

$can_award = award_can_user_give( $user['username'] );
$last_award = award_get_last( $user['username'] );

?>
<html>
	<head>
		<title>Home | Geek Citizenship Recognition</title>
	</head>
<body>

<p>Welcome, <?=$user['username']?>!</p>

<?php
if( $last_award )
{
?>
	<p>You last awarded a star in the month of
		<?=$last_award['year']?>-<?=$last_award['month']?></p>.
<?php
}

if( $can_award )
{
?>
	<form method="POST" action="award.php">
		Who do you want to award a star to? Enter their BGG username here:
		<input type="text" name="recipient" />
		<input type="submit" value="Give Star" />
	</form>
<?php
}
else
{
?>
	<p>You may not award a star this month. The next month will be here soon, so 
		be patient!</p>
<?php
}
?>

</body>
</html>

