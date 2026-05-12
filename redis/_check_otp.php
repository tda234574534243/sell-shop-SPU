<?php
$email='projectmap1234@gmail.com';
$k='otp:'.md5(strtolower($email));
$file=sys_get_temp_dir().DIRECTORY_SEPARATOR.'redis_fallback_'.md5($k);
echo "FILE:".$file.PHP_EOL;
if (file_exists($file)) {
    echo "FOUND\n";
    echo file_get_contents($file);
} else {
    echo "NOTFOUND\n";
}
?>