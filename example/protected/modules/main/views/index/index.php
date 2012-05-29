<!-- Begin: content -->
<p>Hello World!</p>

<p><strong>Name:</strong> <?php echo $this->name; ?>
<!-- End: content -->

<pre>
<?php
#$_SESSION['name'] = $this->name;
$app = \Eve::app();
$name = $app->session->name;
$encrypted = $app->session->encrypted;

echo 'name: ' . $name . "\n";
echo 'encrypted: ' . $encrypted . "\n";

if ($encrypted) {
	echo 'decrypted: ' . \Eve::app()->crypter->decrypt($encrypted) . "\n\n";
}

echo '$_SESSION:' . "\n";
print_r($_SESSION);
#print_r(\Eve::app());
?>
</pre>
