<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require_once("vendor/autoload.php");
    
    if(file_exists("smtp_credentials.php")) {
        include_once("smtp_credentials.php");
    }

    (PHP_SAPI !== "cli" || isset($_SERVER["HTTP_USER_AGENT"])) && die("Must be run via CLI");

    umask(0);
    $passed = [];

    if(!empty($argc)) {
        for($i = 1; $i < $argc; $i++) {
            if(strpos($argv[$i], "=") !== false) {
                $split = explode("=", $argv[$i]);
                $key = $split[0];
                $value = $split[1];

                $passed[$key] = $value;
            }
        }
    }
    else {
        die("\e[31margc / argv is disabled\n");
    }

    //CLI text colour codes
    $colours = [
        "default" => "\e[39m",
        "black" => "\e[30m",
        "red" => "\e[31m",
        "green" => "\e[32m",
        "yellow" => "\e[33m",
        "blue" => "\e[34m",
        "magenta" => "\e[35m",
        "cyan" => "\e[36m",
        "white" => "\e[97m"
    ];

    //Die if missing required parameters
    if(empty($passed["to"])) {
        die($colours["red"] . "A to address is required\n" . $colours["default"]);
    }
    elseif(empty($passed["subject"])) {
        die($colours["red"] . "A subject is required\n" . $colours["default"]);
    }
    elseif(empty($passed["content"])) {
        die($colours["red"] . "An html content file is required\n" . $colours["default"]);
    }
    elseif(!file_exists(__DIR__ . "/content/" . $passed["content"]) || pathinfo(__DIR__ . "/content/" . $passed["content"])["extension"] !== "html") {
        die($colours["red"] . "The html content file does not exist, or you have passed an invalid filetype\n" . $colours["default"]);
    }

    //Set basic details
    $mail = new PHPMailer();
    $mail->isHTML(true);
    $mail->Subject = $passed["subject"];
    $mail->Body = file_get_contents(__DIR__ . "/content/" . $passed["content"]);

    //Store debug information
    $debugOutput = "test";
    $debugDirectory = "logs";
    $debugFilename = date("YmdHis") . ".txt";

    $mail->SMTPDebug = 3;
    $mail->Debugoutput = function($output, $level) {
        global $debugOutput;
        $debugOutput .= $level . ": " . $output;
    };

    //Add attachments
    if(!empty($passed["attachments"])) {
        $attachments = explode(",", rtrim($passed["attachments"], ","));

        foreach($attachments as $attachment) {
            $attachmentPath = __DIR__ . "/attachments/" . $attachment;

            if(file_exists($attachmentPath)) {
                $mail->addAttachment($attachmentPath);
            }
            else {
                echo $colours["yellow"] . "Skipping attachment " . $attachment . ", file does not exist\n" . $colours["default"];
            }
        }
    }

    //Use SMTP details if supplied
    if(!empty($passed["host"]) && !empty($passed["username"]) && !empty($passed["password"]) && !empty($passed["port"])) {
        $useSmtp = true;

        $smtpHost = $passed["host"];
        $smtpUsername = $passed["username"];
        $smtpPassword = $passed["password"];
        $smtpPort = $passed["port"];
    }
    elseif(!empty($smtpCredentials["host"]) && !empty($smtpCredentials["username"]) && !empty($smtpCredentials["password"]) && !empty($smtpCredentials["port"])) {
        $useSmtp = true;

        $smtpHost = $smtpCredentials["host"];
        $smtpUsername = $smtpCredentials["username"];
        $smtpPassword = $smtpCredentials["password"];
        $smtpPort = $smtpCredentials["port"];
    }
    
    if(isset($useSmtp) && $useSmtp === true) {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        $mail->Host = $smtpHost;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->Port = $smtpPort;

        echo $colours["cyan"] . "Sending via SMTP\n\n" . $colours["default"];
    }
    else {
        echo $colours["cyan"] . "Sending via local server\n\n" . $colours["default"];
    }

    //Set from address
    $fromAddress = (!empty($passed["from_address"]) ? $passed["from_address"] : (!empty($addresses["from_address"]) ? $addresses["from_address"]: ""));
    $fromFriendly = (!empty($passed["from_friendly"]) ? $passed["from_friendly"] : (!empty($addresses["from_friendly"]) ? $addresses["from_friendly"]: ""));

    if(!empty($formAddress) && !empty($fromFriendly)) {
        $mail->setFrom($fromAddress, $fromFriendly);
    }
    elseif(!empty($fromAddress)) {
        $mail->setFrom($fromAddress);
    }
    elseif(!empty($fromFriendly)) {
        $mail->setFrom("noreply@" . php_uname("n"), $fromFriendly);
    }
    else {
        $mail->setFrom("noreply@" . php_uname("n"));
    }

    //Set reply to address
    $replyAddress = (!empty($passed["reply_address"]) ? $passed["reply_address"] : (!empty($addresses["reply_address"]) ? $addresses["reply_address"]: ""));
    $replyFriendly = (!empty($passed["reply_friendly"]) ? $passed["reply_friendly"] : (!empty($addresses["reply_friendly"]) ? $addresses["reply_friendly"]: ""));

    if(!empty($replyAddress) && !empty($replyFriendly)) {
        $mail->addReplyTo($replyAddress, $replyFriendly);
    }
    elseif(!empty($replyAddress)) {
        $mail->addReplyTo($replyAddress);
    }

    //Add CCs
    if(!empty($passed["cc"])) {
        $ccs = explode(",", rtrim($passed["cc"], ","));
    }

    //Add BCCs
    if(!empty($passed["bcc"])) {
        $bccs = explode(",", rtrim($passed["bcc"], ","));
    }

    //Send the mail
    if(strpos($passed["to"], ".json") !== false) {
        $toFile = __DIR__ . "/" . $passed["to"];
        
        if(file_exists($toFile)) {
            $tos = [];
            $fc = file_get_contents($toFile);
            $json = json_decode($fc, true);

            if(!empty($json)) {
                foreach($json as $to) {
                    array_push($tos, $to);
                }
            }
        }
        else {
            die($colours["red"] . "Supplied json file for to addresses does not exist\n" . $colours["default"]);
        }
    }
    elseif(strpos($passed["to"], ",") !== false) {
        $tos = explode(",", rtrim($passed["to"], ","));
    }
    else {
        $tos = [$passed["to"]];
    }

    foreach($tos as $to) {
        $mail->clearAllRecipients();
        $mail->addAddress($to);

        if(!empty($ccs)) {
            foreach($ccs as $cc) {
                $mail->addCC($cc);
            }
        }

        if(!empty($bccs)) {
            foreach($bccs as $bcc) {
                $mail->addBCC($bcc);
            }
        }

        if($mail->send()) {
            echo $colours["green"] . "Successfully sent to: " . $to . "\n" . $colours["default"];
        }
        else {
            echo $colours["yellow"] . "Failed sending to: " . $to . "\n" . $colours["default"];
            echo $mail->ErrorInfo . "\n\n";
        }
    }

    //Log the PHPMailer debug output
    if(isset($passed["log"]) && $passed["log"] === "false") {
        echo $colours["cyan"] . "Skipping log\n" . $colours["default"];
    }
    else {
        if(!is_dir($debugDirectory)) {
            mkdir($debugDirectory, 755, true);
        }
        
        $fp = fopen(__DIR__ . "/" . $debugDirectory . "/" . $debugFilename, "w");

        if($fp) {
            fwrite($fp, $debugOutput);
            fclose($fp);

            echo "\n" . $colours["cyan"] . "Logging as filename: " . $debugDirectory . "/" . $debugFilename . "\n" . $colours["default"];
        }
        else {
            echo "\n" . $colours["red"] . "Failed to create log file, check permissions\n" . $colours["default"];
        }
    }