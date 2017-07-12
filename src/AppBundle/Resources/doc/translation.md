Translating
===========

This application is currently only translated into german. To translate it into different languages, we need **your** help.


# Creating a translation

All translations lie under `src/AppBundle/Resouced/translations/`.
There might already be a translation-file for your language. All translations require the following naming-schema: `messages.LANGUAGE_CODE.xlf`.
If you had to create a new file, you can copy the contents of the `messages.de.xlf`-file and change the texts accordingly.
At the begin of the xlf-file, there is a field, which implies the language, that this file translates to:
```xml
<?xml version="1.0"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" target-language="YOUR_LANGUAGE_CODE" datatype="plaintext" original="file.ext">
        <body>
        ...
```
When you had to create a new file, you will need to clear the application-cache. So that symfony can detect the new resource. `php app/console cache:cl -e=prod && php app/console cache:cl`


# Contributing

To share the translations you created, you can either create a fork on github, create an issue, or mail me the contents of the file, and it's language.
My mail-address is located at the bottom of the [README.md](/README.md) file.
