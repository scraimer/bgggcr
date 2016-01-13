<?php

$user = db_get_user_by_cookie( $_COOKIE['bggcookie'] );

$may_award = award_can_user_give( $user['username'] );
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

<p>You last awarded a star in the month of <u>August 2016</u>.
	<?php
}
?>
</p>

</body>
</html>

