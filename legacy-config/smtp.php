<?php

function smtpSend(
    array $mail,
    string $to,
    string $subject,
    string $html
): bool {

    if (
        empty($mail['host']) ||
        empty($mail['username']) ||
        empty($mail['password']) ||
        empty($mail['from_email'])
    ) {
        return false;
    }

    $host =
        $mail['host'];

    $port =
        (int)$mail['port'];

    $remote =
        'tcp://'
        . $host
        . ':'
        . $port;

    $errno = 0;
    $errstr = '';

    $fp =
        @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT
        );

    if (!$fp) {
        error_log(
            'SMTP connection failed: '
            . $errstr
        );

        return false;
    }

    stream_set_timeout(
        $fp,
        15
    );

    $read = function () use ($fp): string {

        $data = '';

        while (
            ($line = fgets($fp, 515))
            !== false
        ) {

            $data .= $line;

            if (
                strlen($line) >= 4
                && $line[3] === ' '
            ) {
                break;
            }
        }

        return $data;
    };

    $write =
        function (string $command)
        use ($fp): void {

            fwrite(
                $fp,
                $command . "\r\n"
            );
        };

    /*
    |--------------------------------------------------------------------------
    | SMTP GREETING
    |--------------------------------------------------------------------------
    */

    $code =
        (int)substr(
            $read(),
            0,
            3
        );

    if ($code !== 220) {
        fclose($fp);
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | EHLO
    |--------------------------------------------------------------------------
    */

    $write(
        'EHLO localhost'
    );

    if (
        (int)substr(
            $read(),
            0,
            3
        ) !== 250
    ) {
        fclose($fp);
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | STARTTLS
    |--------------------------------------------------------------------------
    */

    if (
        strtolower(
            $mail['encryption'] ?? 'tls'
        ) === 'tls'
    ) {

        $write(
            'STARTTLS'
        );

        if (
            (int)substr(
                $read(),
                0,
                3
            ) !== 220
        ) {
            fclose($fp);
            return false;
        }

        $crypto =
            stream_socket_enable_crypto(
                $fp,
                true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT
            );

        if (!$crypto) {
            fclose($fp);
            return false;
        }

        $write(
            'EHLO localhost'
        );

        if (
            (int)substr(
                $read(),
                0,
                3
            ) !== 250
        ) {
            fclose($fp);
            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    $write(
        'AUTH LOGIN'
    );

    if (
        (int)substr(
            $read(),
            0,
            3
        ) !== 334
    ) {
        fclose($fp);
        return false;
    }

    $write(
        base64_encode(
            $mail['username']
        )
    );

    if (
        (int)substr(
            $read(),
            0,
            3
        ) !== 334
    ) {
        fclose($fp);
        return false;
    }

    $write(
        base64_encode(
            $mail['password']
        )
    );

    if (
        (int)substr(
            $read(),
            0,
            3
        ) !== 235
    ) {
        fclose($fp);
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | MAIL FROM
    |--------------------------------------------------------------------------
    */

    $write(
        'MAIL FROM:<'
        . $mail['from_email']
        . '>'
    );

    if (
        (int)substr(
            $read(),
            0,
            3
        ) !== 250
    ) {
        fclose($fp);
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | RECIPIENT
    |--------------------------------------------------------------------------
    */

    $write(
        'RCPT TO:<'
        . $to
        . '>'
    );

    if (
        (int)substr(
            $read(),
            0,
            3
        ) !== 250
    ) {
        fclose($fp);
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    $write('DATA');

    if (
        (int)substr(
            $read(),
            0,
            3
        ) !== 354
    ) {
        fclose($fp);
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | HEADERS
    |--------------------------------------------------------------------------
    */

    $headers =
        'From: '
        . $mail['from_name']
        . ' <'
        . $mail['from_email']
        . ">\r\n";

    $headers .=
        'To: <'
        . $to
        . ">\r\n";

    $headers .=
        'Subject: '
        . $subject
        . "\r\n";

    $headers .=
        "MIME-Version: 1.0\r\n";

    $headers .=
        "Content-Type: text/html; charset=UTF-8\r\n";

    $headers .=
        "Content-Transfer-Encoding: 8bit\r\n\r\n";


    $body =
        $headers
        . $html;

    $body =
        preg_replace(
            '/\r?\n/',
            "\r\n",
            $body
        );

    $body =
        preg_replace(
            '/(^|\r\n)\./',
            '$1..',
            $body
        );


    fwrite(
        $fp,
        $body
        . "\r\n.\r\n"
    );


    if (
        (int)substr(
            $read(),
            0,
            3
        ) !== 250
    ) {
        fclose($fp);
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | QUIT
    |--------------------------------------------------------------------------
    */

    $write('QUIT');

    $read();

    fclose($fp);

    return true;
}