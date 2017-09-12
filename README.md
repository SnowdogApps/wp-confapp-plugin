# Wordpres Confapp Plugin
Wordpress plugin which integrate your page with [ConfApp API](http://confapp.co/) to render selected event agenda

## How to use it?
1. Contact with us to get API URL and private key
2. Go to admin area and open "ConfApp" tab in sidebar
3. Fill "Base url api" and "Api key" inputs then press "Save changes"
4. Select event from list and press "Save changes"
5. Press "Manual synchronize" to get all conference data
6. Available languages codes will be displayed in this view, you can use it in shortcodes
7. Add `[conffapp_agenda lang="en"]` shortcode to your page or post to render agenda
8. If you're using wp cache modules, please remember to clear it. It's necessary because cached pages are stored on server as html and PHP files.


## Translations
By default everything is in English and we are shipping Polish tranlsations files.
You can use [Loco Translate](https://pl.wordpress.org/plugins/loco-translate/) plugin, to create own translations for more languages, fell free to send them as a pull request.
