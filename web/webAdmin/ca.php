<?php
namespace webAdmin;

class ca
{
	private $config;
	
	private $cert_file_name;
	private $priv_key_name;

	public function __construct($config, $cert_file_name, $priv_key_name)
	{
		$this->config = $config;
		$this->cert_file_name = $cert_file_name;
		$this->priv_key_name = $priv_key_name;
		
		if ($this->config["ca_enabled"] == 1)
		{
			if (!file_exists($this->config["temp_ca_folder"] . $this->cert_file_name) ||
				!file_exists($this->config["temp_ca_folder"] . $this->priv_key_name)
				)
			{	//create temp data if missing
				$this->create_ca_cert();
			}
		}
		
		if (!file_exists($this->config["actual_ca_folder"] . $this->cert_file_name) ||
			!file_exists($this->config["actual_ca_folder"] . $this->priv_key_name)
			)
		{	//cannot continue if either is missing
			throw new \Exception("Some key data is missing");
		}
	}
	
	public function show_ca_cert()
	{
		$ca = "file://" . $this->config["actual_ca_folder"] . $this->cert_file_name;
		$CAcert = openssl_x509_read($ca);
		openssl_x509_export($CAcert, $ca_display, FALSE);
		echo "<pre>" . $ca_display . "</pre><br />\n";
	}
	
	public function download_ca_cert()
	{
		$ca = "file://" . $this->config["actual_ca_folder"] . $this->cert_file_name;
		$CAcert = openssl_x509_read($ca);
		openssl_x509_export($CAcert, $ca_display);
		$length = strlen($ca_display);
		header('Last-Modified: '.date('r+b'));
		header('Accept-Ranges: bytes');
		header('Content-Length: '.$length);
		header('Content-Type: application/x-x509-ca-cert');
		header('Content-Disposition: attachment; filename="root_ca.crt"');
		echo $ca_display;
	}
	
	public function create_user_cert()
	{
		$key_config = array(
				"digest_alg" => "sha512",
				"private_key_bits" => 2048,
				"private_key_type" => OPENSSL_KEYTYPE_RSA,
			);
		$user_res = openssl_pkey_new($key_config);
		
		$ca = "file://" . $this->config["actual_ca_folder"] . $this->cert_file_name;
		$CAcert = openssl_x509_read($ca);
		$privKey = openssl_pkey_get_private('file://' . $this->config["actual_ca_folder"] . $this->priv_key_name);
		$csr = openssl_csr_new(array('commonName'=>$_POST['username']), $user_res, array('digest_alg'=>'sha512'));
		$cert = openssl_csr_sign($csr, $ca, $privKey, 30, array('digest_alg'=>'sha512'));
		$stuff = array(
			'extracerts' => $CAcert,
			'friendly_name' => 'My signed cert by CA certificate'
			);
		openssl_pkcs12_export($cert, $display, $user_res, "asdfghjkl");
		
		openssl_pkey_export($user_res, $pkey_export);
		//And send it back to the user
		$length = strlen($display);
		header('Last-Modified: '.date('r+b'));
		header('Accept-Ranges: bytes');
		header('Content-Length: '.$length);
		header('Content-Type: application/x-pkcs12');
		header('Content-Disposition: attachment; filename="user.p12"');
		echo $display;
	}
	
	public function create_user_cert2()
	{
		$spkac = $_POST["pubkey"];
	
		$caPrivateKey = new \Crypt_RSA();
		$pkey_data = file_get_contents($this->config["actual_ca_folder"] . $this->priv_key_name);
        $caPrivateKey->loadKey($pkey_data);
		
		$cert_data = file_get_contents($this->config["actual_ca_folder"] . $this->cert_file_name);
		$issuer = new \File_X509();
        $issuer->loadX509($cert_data);
        $issuer->setPrivateKey($caPrivateKey);
		
		$subject = new \File_X509();
		$subject->loadCA($this->config["actual_ca_folder"] . $this->cert_file_name);
		$subject->loadSPKAC($spkac);
		$subject->setDNProp('CN', $_POST['username']);

		$x509 = new \File_X509();
        $x509->setSerialNumber($serialNumber = bin2hex(openssl_random_pseudo_bytes(8)), 16);
		$x509->setEndDate('+1 year');
        $result = $x509->sign($issuer, $subject, 'sha512WithRSAEncryption');
        $format = FILE_X509_FORMAT_PEM;//X509::FORMAT_DER;
		$cert = $x509->saveX509($result);

		//And send it back to the user
		$length = strlen($cert);
		header('Last-Modified: '.date('r+b'));
		header('Accept-Ranges: bytes');
		header('Content-Length: '.$length);
		header('Content-Type: application/x-x509-user-cert');
		header('Content-Disposition: attachment; filename="user.crt"');
		echo $cert;		
	}
	
	private function create_ca_cert()
	{
		echo "Creating CA certificate<br />\n";
		
		// create private key / x.509 cert for stunnel / website
		$privKey = new \Crypt_RSA();
		$privKey->setHash('sha384');
		extract($privKey->createKey(2048));
		$privKey->loadKey($privatekey);
		
		$pubKey = new \Crypt_RSA();
		$pubKey->setHash('sha384');
		$pubKey->loadKey($publickey);
		$pubKey->setPublicKey();
		
		// create a self-signed cert that'll serve as the CA
		$subject = new \File_X509();
		$subject->setPublicKey($pubKey);
		$subject->setPrivateKey($privKey);
		$subject->setDNProp('id-at-organizationName', $_SERVER["SERVER_NAME"] . " Identify Verification");
		$subject->setDNProp('id-at-commonName', $_SERVER["SERVER_NAME"] . " Identify Verification");

		$x509 = new \File_X509();
		$x509->setEndDate('+10 year');
		$x509->setSerialNumber(chr(1));
		$x509->makeCA();

		$result = $x509->sign($subject, $subject, 'sha512WithRSAEncryption');
		$key_ca = $x509->saveX509($result);
		echo "the CA cert to be imported into the browser is as follows:<br />\n<pre>";
		echo $key_ca;
		echo "</pre><br />\n";
		
		file_put_contents($this->config["temp_ca_folder"] . $this->cert_file_name, $key_ca);
		file_put_contents($this->config["temp_ca_folder"] . $this->priv_key_name, $privatekey);
	}

}

?>