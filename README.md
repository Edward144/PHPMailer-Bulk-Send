# PHPMAILER-Bulk-Send

This php script is designed to be run from the cli for the purpose of sending out mass emails using SMTP.

## Parameters

When running the script you can pass various parameters by adding each one seperated by a space after the script name. Each passed parameter must consist of an index and a value split up with an equals sign. For example:

`php sendmail.php key1=value1 key2=value2 key3=value3`

The list of accepted parameters is:
* to - A single email address, comma separated list of addresses, or json file containing an array of email addresses. **This is a required parameter.**
* subject - The subject to be used in the email. **This is a required parameter.**
* content - A html file containing the body of the email, stored within the content directory. **This is a required parameter and must be a html file, others will not be accepted.**
* cc - A single email address or comma separated list of addresses.
* bcc - A single email address or comma separated list of addresses.
* attachments - A comma separated list of attachments to be included with the email. 
* reply_address - The reply to email address which will appear on the email.
* reply_friendly - The real name of the reply to address.
* from_address - The email address which the mail will be sent from.
* from_friendly - The real name of the from address.
* log - Set this to false to skip logging the debug output from PHP Mailer.

## SMTP credentials

Store your SMTP credentials within the **smtp_credentials.php** file. If all required SMTP parameters are provided then the system will send via SMTP. Otherwise send via the local environment.

* host
* username
* password
* port

You can also supply these details via the commandline parameters. If both the **smtp_credentials.php** file and the inline CLI parameters are supplied, then the CLI parameters will override the file.

`php sendmail.php to=johnsmith@example.com subject="This is an email" content=myemail.html host=smtp.provider.com username=myapi_username password=myapi_password port=465`

## Reply and From addresses

The reply_address, reply_friendly, from_address and from_friendly parameters can all be set within the **smtp_credentials.php** file. As these will generally be used alongside an SMTP service and you may not wish to have to set them every time. 

If both the **smtp_credentials.php** file and the inline CLI parameters are supplied, then the CLI parameters will override the file.

## Supplying multiple email addresses

Make sure that you leave no spaces in between each email address, surrounding the commas. The format should follow

> johnsmith<span>@</span>example.com,janedoe<span>@</span>gmail.com

You can leave a trailing comma at the end of the list as this will be removed automatically.

When providing multiple email addresses a separate email is sent out to each individual email address. Each recipient will not see the others. 

If supplying a json file provide the full path to the file from the installation root directory. For example:

> myfile.json
> mydirectory/myfile.json

The json file should contain a simple array of emails:

`["email1@domain.com", "email2@domain.com", "email3@gmail.com"]`

## BCC and CC email addresses

The same above also applies to BCCs and CCs. However BCC and CC addresses are copied in on each sent copy of an email. 

So if multiple to addresses are provided, then any BCC or CCed address will be copied in on multiple emails. It may be best to only use BCC or CC when sending to a single to address.

## Subject

Surround the subject with double quotes if it contains spaces or alternatively escape these with a backslash.

## Attachments

Attachment files should be stored within the attachments directory.

Make sure to escape any spaces with a backslash or ideally use filenames without spaces.

## Log files

If logging has not been disabled, the PHP mailer debug output will be logged at the end of the process and stored within the **logs/** directory, if this directory does not exist then it will be created. 

Ensure that the user running the script has write permissions for the log directory.