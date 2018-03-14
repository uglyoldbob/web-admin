<?php
//Should not happen since this should be in a directory that does not ask for client certificates

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) 
		{
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
			$file = str_replace('_', DIRECTORY_SEPARATOR, $file);
            if (file_exists($file)) 
			{
                require $file;
                return true;
            }
			else
			{
				echo "Cannot find " . $file . " for class " . $class . "<br />\n";
			}
            return false;
        });
    }
}
Autoloader::register();

require_once("webAdmin/exceptions.php");
require_once("webAdmin/global.php");

try
{
	$config = parse_ini_file("config.ini");
	\webAdmin\test_config($config);
	$my_ca = new \webAdmin\ca($config, 'cert.pem', 'private.key');
}
catch (Exception $e)
{
	echo "Failed to start CA<br />\n";
}

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (array_key_exists("DOWNLOAD_CA", $_POST))
	{
		$my_ca->download_ca_cert();
		exit;
	}
	else if (array_key_exists("SHOW_CA", $_POST))
	{
		$my_ca->show_ca_cert();
		exit;
	}
	else if (array_key_exists("username", $_POST))
	{
		if (array_key_exists("pubkey", $_POST))
		{
			$my_ca->create_user_cert2();
			exit;
		}
		else
		{
			$my_ca->create_user_cert();
			exit;
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
 <title>Keygen page</title>
</head>
<body>
<?php 
if (array_key_exists("username", $_POST) && !array_key_exists("pubkey", $_POST))
{
	?><script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script type="text/javascript" src="explorer-keygen.js"></script>
	<?php
}
?>

<h1>Let's generate you a cert so you don't have to use a password!</h1>
 Hit the Generate button and then install the certificate it gives you in your browser.
 All modern browsers (except for Internet Explorer) should be compatible.
 <form method="post">
   <keygen name="pubkey" keytype="rsa" challenge="randomchars">
   The username I want: <input type="text" name="username" value="Alice">
   <input type="submit" name="createcert" value="Generate">
 </form>
 <form method="post">
   <input type="hidden" name="DOWNLOAD_CA" value="yes">
   <input type="submit" name="createcert" value="Get CA certificate">
 </form>
 <form method="post">
   <input type="hidden" name="SHOW_CA" value="yes">
   <input type="submit" name="createcert" value="Show CA certificate">
 </form>
 
 <strong>Wait a minute, then refresh this page over HTTPS to see your new cert in action!</strong>
</body>
</html>