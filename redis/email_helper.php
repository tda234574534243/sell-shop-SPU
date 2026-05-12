<?php
// Email helper with optional SMTP using credentials from redis/email_smtp_config.php
class EmailHelper {
    private $fromEmail;
    private $fromName;
    private $config;

    public function __construct($fromEmail = null, $fromName = null) {
        $cfgFile = __DIR__ . '/email_smtp_config.php';
        $this->config = file_exists($cfgFile) ? include $cfgFile : [];
        $this->fromEmail = $fromEmail ?: ($this->config['from_email'] ?? 'no-reply@example.com');
        $this->fromName = $fromName ?: ($this->config['from_name'] ?? 'Sell Shop');
    }

    public function send($to, $subject, $htmlBody) {
        if (!empty($this->config['use_smtp'])) {
            return $this->sendSmtp($to, $subject, $htmlBody);
        }
        // fallback to PHP mail()
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $this->fromName . ' <' . $this->fromEmail . '>' . "\r\n";
        return @mail($to, $subject, $htmlBody, $headers);
    }

    private function sendSmtp($to, $subject, $htmlBody) {
        $host = $this->config['host'] ?? 'localhost';
        $port = $this->config['port'] ?? 25;
        $username = $this->config['username'] ?? '';
        $password = $this->config['password'] ?? '';
        $encryption = $this->config['encryption'] ?? '';

        $timeout = 30;
        $errno = 0; $errstr = '';
        $remote = $host . ':' . $port;
        $socket = stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!$socket) return false;

        stream_set_timeout($socket, $timeout);
        $res = $this->getSmtpResponse($socket);
        if (strpos($res, '220') !== 0) { fclose($socket); return false; }

        $this->smtpCommand($socket, "EHLO localhost");
        $res = $this->getSmtpResponse($socket);

        if (strtolower($encryption) === 'tls') {
            $this->smtpCommand($socket, "STARTTLS");
            $res = $this->getSmtpResponse($socket);
            if (strpos($res, '220') === 0) {
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($socket); return false; }
                $this->smtpCommand($socket, "EHLO localhost");
                $res = $this->getSmtpResponse($socket);
            }
        }

        if ($username !== '') {
            $this->smtpCommand($socket, "AUTH LOGIN");
            $this->getSmtpResponse($socket);
            $this->smtpCommand($socket, base64_encode($username));
            $this->getSmtpResponse($socket);
            $this->smtpCommand($socket, base64_encode($password));
            $authRes = $this->getSmtpResponse($socket);
            if (strpos($authRes, '235') !== 0) { fclose($socket); return false; }
        }

        $this->smtpCommand($socket, 'MAIL FROM: <' . $this->fromEmail . '>');
        $this->getSmtpResponse($socket);
        $this->smtpCommand($socket, 'RCPT TO: <' . $to . '>');
        $this->getSmtpResponse($socket);
        $this->smtpCommand($socket, 'DATA');
        $this->getSmtpResponse($socket);

        $headers  = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "To: {$to}\r\n";

        $data = $headers . "\r\n" . $htmlBody . "\r\n.";
        $this->smtpCommand($socket, $data);
        $this->getSmtpResponse($socket);

        $this->smtpCommand($socket, 'QUIT');
        fclose($socket);
        return true;
    }

    private function smtpCommand($socket, $cmd) {
        $line = $cmd . "\r\n";
        fwrite($socket, $line);
    }

    private function getSmtpResponse($socket) {
        $data = '';
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            // multi-line response ends when 4th char is space
            if (isset($str[3]) && $str[3] === ' ') break;
        }
        return $data;
    }
}

?>
