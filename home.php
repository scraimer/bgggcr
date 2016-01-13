<?php

$user = db_get_user_by_cookie( $_COOKIE['bggcookie'] );

?>
<html>
	<head>
		<title>Home | Geek Citizenship Recognition</title>
	</head>
<body>

<p>Welcome, <?=$user['username']?>!</p>

</body>
</html>



