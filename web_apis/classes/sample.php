<?php

include 'ElggApiClient.php';

$siteUrl = "http://localhost/~xingyu/elgg/";
$apiKey = '4de60a98f0b578c5f357e5a59d20b725e18e10a3';
$username = 'elggadmin';
$password = 'elggadmin';

$myClient = new ElggApiClient($siteUrl, $apiKey);

$params = array(
	'username' => $username,
	'password' => $password,
);
$token = $myClient->post('auth.gettoken', $params);

//$method = 'blog.get_latest_posts';
//$params = ['username'=>'test01',
//           'limit'=>0,
//           'offset'=>0
//];

$method = 'blog.save_post';
$params = ['username'=>'elggadmin',
           'title'=>'api test blog',
           'text'=>'this is api test blog post 2',
           'excerpt'=>'',
           'tags'=>NULL,
           'access'=>2
];

//$method = 'wire.save_post';
//$params = ['username'=>'elggadmin',
//           'text'=>'this is api test wire post',
//           'access'=>2
//];

//$method = 'user.friend.add';
//$params = ['username'=>'elggadmin',
//           'friend'=>'test01',
//];

//$method = 'wire.delete_posts';
//$params = ['username'=>'elggadmin',
//           'wireid'=>65,
//];

//$method = 'user.register';
//$params = ['name'=>'apitest2',
//           'email'=>'apitest2@apitest2.com',
//           'username'=>'apitest2',
//           'password'=>'apitest2',
//];

//$method = 'user.friend.remove';
//$params = ['username'=>'elggadmin',
//           'friend'=>'test01',
//];

$result = $myClient->post($method,$params);
var_dump($result);
echo $myClient->getLastErrorMessage();

?>
