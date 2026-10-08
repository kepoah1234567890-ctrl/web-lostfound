<?php
/** Minimal SMTP sender with STARTTLS, suitable for Gmail App Passwords. */
function smtpSend(array $mail, string $to, string $subject, string $html): bool {
    if (empty($mail['host']) || empty($mail['username']) || empty($mail['password']) || empty($mail['from_email'])) {
        return false;
    }
    $host = $mail['host']; $port = (int)$mail['port'];
    $remote = 'tcp://' . $host . ':' . $port;
    $errno = 0; $errstr = '';
    $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if (!$fp) return false;
    stream_set_timeout($fp, 15);
    $read = function() use ($fp) {
        $data=''; while (($line=fgets($fp, 515)) !== false) { $data.=$line; if (strlen($line)>=4 && $line[3]===' ') break; }
        return $data;
    };
    $write = function(string $cmd) use ($fp) { fwrite($fp, $cmd."\r\n"); };
    $code = (int)substr($read(),0,3); if ($code !== 220) { fclose($fp); return false; }
    $write('EHLO localhost'); if ((int)substr($read(),0,3) !== 250) { fclose($fp); return false; }
    if (strtolower($mail['encryption'] ?? 'tls') === 'tls') {
        $write('STARTTLS'); if ((int)substr($read(),0,3) !== 220) { fclose($fp); return false; }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; }
        $write('EHLO localhost'); if ((int)substr($read(),0,3) !== 250) { fclose($fp); return false; }
    }
    $write('AUTH LOGIN'); if ((int)substr($read(),0,3) !== 334) { fclose($fp); return false; }
    $write(base64_encode($mail['username'])); if ((int)substr($read(),0,3) !== 334) { fclose($fp); return false; }
    $write(base64_encode($mail['password'])); if ((int)substr($read(),0,3) !== 235) { fclose($fp); return false; }
    $write('MAIL FROM:<' . $mail['from_email'] . '>'); if ((int)substr($read(),0,3) !== 250) { fclose($fp); return false; }
    $write('RCPT TO:<' . $to . '>'); if ((int)substr($read(),0,3) !== 250) { fclose($fp); return false; }
    $write('DATA'); if ((int)substr($read(),0,3) !== 354) { fclose($fp); return false; }
    $headers = 'From: ' . $mail['from_name'] . ' <' . $mail['from_email'] . ">\r\n";
    $headers .= 'To: <' . $to . ">\r\n";
    $headers .= 'Subject: ' . $subject . "\r\n";
    $headers .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
    $body = preg_replace('/\r?\n/', "\r\n", $headers . $html);
    $body = preg_replace('/(^|\r\n)\./', '$1..', $body);
    fwrite($fp, $body . "\r\n.\r\n"); if ((int)substr($read(),0,3) !== 250) { fclose($fp); return false; }
    $write('QUIT'); $read(); fclose($fp); return true;
}
